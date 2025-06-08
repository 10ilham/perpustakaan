<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PeminjamanModel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LaporanController extends Controller
{
    public function index()
    {
        // Statistik umum
        $totalPeminjaman = PeminjamanModel::count();
        $belumKembali = PeminjamanModel::whereIn('status', ['Dipinjam', 'Terlambat'])->count();
        $sudahKembali = PeminjamanModel::where('status', 'Dikembalikan')->count();

        // Hitung terlambat: yang masih dipinjam melewati tanggal kembali + yang dikembalikan terlambat
        $terlambatMasihPinjam = PeminjamanModel::whereIn('status', ['Dipinjam', 'Terlambat'])
            ->where('tanggal_kembali', '<', now()->toDateString())
            ->count();

        $terlambatSudahKembali = PeminjamanModel::where('status', 'Dikembalikan')
            ->whereRaw('DATE(tanggal_pengembalian) > DATE(tanggal_kembali)')
            ->count();

        $terlambat = $terlambatMasihPinjam + $terlambatSudahKembali;

        // Statistik per level user
        $statistikLevel = User::select('level', DB::raw('count(*) as total_user'))
            ->groupBy('level')
            ->get()
            ->map(function ($user) {
                $user->total_peminjaman = PeminjamanModel::whereHas('user', function ($q) use ($user) {
                    $q->where('level', $user->level);
                })->count();

                $user->sedang_pinjam = PeminjamanModel::whereHas('user', function ($q) use ($user) {
                    $q->where('level', $user->level);
                })->whereIn('status', ['Dipinjam', 'Terlambat'])->count();

                return $user;
            });

        return view('laporan.index', compact(
            'totalPeminjaman',
            'belumKembali',
            'sudahKembali',
            'terlambat',
            'statistikLevel'
        ));
    }

    public function belumKembali()
    {
        $query = PeminjamanModel::with(['user', 'buku'])
            ->whereIn('status', ['Dipinjam', 'Terlambat']);

        // Filter data untuk non-admin: hanya tampilkan data peminjaman mereka sendiri
        if (Auth::user()->level !== 'admin') {
            $query->where('user_id', Auth::id());
        }

        $peminjamanBelumKembali = $query->orderBy('tanggal_kembali', 'asc')
            ->get()
            ->map(function ($peminjaman) {
                $tanggalKembali = \Carbon\Carbon::parse($peminjaman->tanggal_kembali);
                $hariIni = \Carbon\Carbon::now();

                $peminjaman->hari_terlambat = 0;
                $peminjaman->status_keterlambatan = 'normal';

                if ($tanggalKembali->isPast()) {
                    $peminjaman->hari_terlambat = $hariIni->diffInDays($tanggalKembali);
                    $peminjaman->status_keterlambatan = 'terlambat';
                } elseif ($tanggalKembali->isToday()) {
                    $peminjaman->status_keterlambatan = 'hari_ini';
                } elseif ($tanggalKembali->isTomorrow()) {
                    $peminjaman->status_keterlambatan = 'besok';
                }

                return $peminjaman;
            });

        return view('laporan.belum_kembali', compact('peminjamanBelumKembali'));
    }

    public function sudahKembali(Request $request)
    {
        $query = PeminjamanModel::with(['user', 'buku'])
            ->where('status', 'Dikembalikan');

        // Filter data untuk non-admin: hanya tampilkan data peminjaman mereka sendiri
        if (Auth::user()->level !== 'admin') {
            $query->where('user_id', Auth::id());
        }

        // Filter berdasarkan tanggal jika ada
        if ($request->has('tanggal_mulai') && $request->tanggal_mulai) {
            $query->whereDate('tanggal_pengembalian', '>=', $request->tanggal_mulai);
        }

        if ($request->has('tanggal_selesai') && $request->tanggal_selesai) {
            $query->whereDate('tanggal_pengembalian', '<=', $request->tanggal_selesai);
        }

        // Filter berdasarkan level user jika ada (hanya untuk admin)
        if (Auth::user()->level === 'admin' && $request->has('level') && $request->level) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('level', $request->level);
            });
        }

        $peminjamanSudahKembali = $query->orderBy('tanggal_pengembalian', 'desc')->get();

        // Data untuk filter (hanya untuk admin)
        $levels = ['siswa', 'guru', 'staff'];

        return view('laporan.sudah_kembali', compact('peminjamanSudahKembali', 'levels'));
    }

    public function getChartData(Request $request)
    {
        $period = $request->get('period', '6months');
        $data = [];

        // Array untuk nama bulan dan hari dalam bahasa Indonesia
        $bulanIndonesia = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des'
        ];

        $hariIndonesia = [
            'Sunday' => 'Min',
            'Monday' => 'Sen',
            'Tuesday' => 'Sel',
            'Wednesday' => 'Rab',
            'Thursday' => 'Kam',
            'Friday' => 'Jum',
            'Saturday' => 'Sab'
        ];

        switch ($period) {
            case 'day':
                // Last 24 hours (hourly data)
                for ($i = 23; $i >= 0; $i--) {
                    $hour = now()->subHours($i);
                    $data[] = [
                        'label' => $hour->format('H:i'),
                        'dipinjam' => PeminjamanModel::where(function ($query) use ($hour) {
                            $query->whereDate('tanggal_pinjam', $hour->toDateString())
                                ->whereTime('tanggal_pinjam', '>=', $hour->format('H:00:00'))
                                ->whereTime('tanggal_pinjam', '<', $hour->copy()->addHour()->format('H:00:00'));
                        })->count(),
                        'dikembalikan' => PeminjamanModel::where('status', 'Dikembalikan')
                            ->where(function ($query) use ($hour) {
                                $query->whereDate('tanggal_pengembalian', $hour->toDateString())
                                    ->whereTime('tanggal_pengembalian', '>=', $hour->format('H:00:00'))
                                    ->whereTime('tanggal_pengembalian', '<', $hour->copy()->addHour()->format('H:00:00'));
                            })->count()
                    ];
                }
                break;

            case 'week':
                // 1 minggu sekarang (dari hari senin sampai minggu)
                // $startOfWeek = now()->startOfWeek();
                // $endOfWeek = now()->endOfWeek();
                // $daysInWeek = 7;
                // for ($day = 0; $day < $daysInWeek; $day++) {
                //     $date = $startOfWeek->copy()->addDays($day);
                //     $dayName = $hariIndonesia[$date->format('l')];
                //     $data[] = [
                //         'label' => $dayName . ', ' . $date->format('j') . ' ' . $bulanIndonesia[$date->month],
                //         'dipinjam' => PeminjamanModel::whereDate('tanggal_pinjam', $date->toDateString())->count(),
                //         'dikembalikan' => PeminjamanModel::whereDate('tanggal_pengembalian', $date->toDateString())
                //             ->where('status', 'Dikembalikan')
                //             ->count()
                //     ];
                // }
                // break;

                // Last 7 days (daily data)
                for ($i = 6; $i >= 0; $i--) {
                    $day = now()->subDays($i);
                    $dayName = $hariIndonesia[$day->format('l')];
                    $data[] = [
                        'label' => $dayName . ', ' . $day->format('j') . ' ' . $bulanIndonesia[$day->month],
                        'dipinjam' => PeminjamanModel::whereDate('tanggal_pinjam', $day->toDateString())->count(),
                        'dikembalikan' => PeminjamanModel::whereDate('tanggal_pengembalian', $day->toDateString())
                            ->where('status', 'Dikembalikan')
                            ->count()
                    ];
                }
                break;

            case 'month':
                // Bulan sekarang (dari tanggal 1 sampai akhir bulan)
                $currentMonth = now();
                $startOfMonth = $currentMonth->copy()->startOfMonth();
                $endOfMonth = $currentMonth->copy()->endOfMonth();
                $daysInMonth = $endOfMonth->day;

                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $date = $currentMonth->copy()->day($day);
                    $data[] = [
                        'label' => $day . ' ' . $bulanIndonesia[$date->month],
                        'dipinjam' => PeminjamanModel::whereDate('tanggal_pinjam', $date->toDateString())->count(),
                        'dikembalikan' => PeminjamanModel::whereDate('tanggal_pengembalian', $date->toDateString())
                            ->where('status', 'Dikembalikan')
                            ->count()
                    ];
                }
                break;

            // Last 30 days (daily data)
            // for ($i = 29; $i >= 0; $i--) {
            //     $day = now()->subDays($i);
            //     $data[] = [
            //         'label' => $day->format('j') . ' ' . $bulanIndonesia[$day->month],
            //         'dipinjam' => PeminjamanModel::whereDate('tanggal_pinjam', $day->toDateString())->count(),
            //         'dikembalikan' => PeminjamanModel::whereDate('tanggal_pengembalian', $day->toDateString())
            //             ->where('status', 'Dikembalikan')
            //             ->count()
            //     ];
            // }
            // break;

            case '6months':
                // Last 6 months (monthly data)
                for ($i = 5; $i >= 0; $i--) {
                    $month = now()->subMonths($i);
                    $data[] = [
                        'label' => $bulanIndonesia[$month->month] . ' ' . $month->year,
                        'dipinjam' => PeminjamanModel::whereYear('tanggal_pinjam', $month->year)
                            ->whereMonth('tanggal_pinjam', $month->month)
                            ->count(),
                        'dikembalikan' => PeminjamanModel::whereYear('tanggal_pengembalian', $month->year)
                            ->whereMonth('tanggal_pengembalian', $month->month)
                            ->where('status', 'Dikembalikan')
                            ->count()
                    ];
                }
                break;

            case 'year':
                // Current year (12 months from January to December)
                $currentYear = now()->year;
                for ($month = 1; $month <= 12; $month++) {
                    $data[] = [
                        'label' => $bulanIndonesia[$month] . ' ' . $currentYear,
                        'dipinjam' => PeminjamanModel::whereYear('tanggal_pinjam', $currentYear)
                            ->whereMonth('tanggal_pinjam', $month)
                            ->count(),
                        'dikembalikan' => PeminjamanModel::whereYear('tanggal_pengembalian', $currentYear)
                            ->whereMonth('tanggal_pengembalian', $month)
                            ->where('status', 'Dikembalikan')
                            ->count()
                    ];
                }
                break;
        }

        return response()->json($data);
    }

    public function getPieChartData(Request $request)
    {
        $period = $request->get('period', '6months');

        // Get date range based on period
        $dateRange = $this->getDateRange($period);

        // Get level data based on period (exclude admin level since admins don't borrow books)
        $levelData = User::select('level', DB::raw('count(*) as total_user'))
            ->where('level', '!=', 'admin')
            ->groupBy('level')
            ->get()
            ->map(function ($user) use ($dateRange, $period) {
                // For consistency between both charts, we need to handle different periods differently
                if ($period === 'day') {
                    // For daily view: show current status (books currently borrowed)
                    // This makes both charts consistent for daily monitoring
                    $user->total_peminjaman = PeminjamanModel::whereHas('user', function ($q) use ($user) {
                        $q->where('level', $user->level);
                    })->whereIn('status', ['Dipinjam', 'Terlambat'])->count();
                } else {
                    // For other periods: show borrowings made in the period
                    $query = PeminjamanModel::whereHas('user', function ($q) use ($user) {
                        $q->where('level', $user->level);
                    });

                    if ($dateRange) {
                        $query->whereBetween('tanggal_pinjam', $dateRange);
                    }

                    $user->total_peminjaman = $query->count();
                }

                // Count sedang pinjam in current time (for status chart)
                $user->sedang_pinjam = PeminjamanModel::whereHas('user', function ($q) use ($user) {
                    $q->where('level', $user->level);
                })->whereIn('status', ['Dipinjam', 'Terlambat'])->count();

                return $user;
            });

        // Calculate totals for status chart (exclude admin-related borrowings)
        $totalSedangPinjam = $levelData->sum('sedang_pinjam');
        $totalPeminjamanPeriod = $levelData->sum('total_peminjaman');

        // For status chart, calculate returned books based on period logic
        $totalDikembalikanPeriod = 0;
        if ($period === 'day') {
            // For daily view: show books returned today
            if ($dateRange) {
                $totalDikembalikanPeriod = PeminjamanModel::where('status', 'Dikembalikan')
                    ->whereHas('user', function ($q) {
                        $q->where('level', '!=', 'admin');
                    })
                    ->whereBetween('tanggal_pengembalian', $dateRange)
                    ->count();
            }
        } else {
            // For other periods: show books returned in the period
            if ($dateRange) {
                $totalDikembalikanPeriod = PeminjamanModel::where('status', 'Dikembalikan')
                    ->whereHas('user', function ($q) {
                        $q->where('level', '!=', 'admin');
                    })
                    ->whereBetween('tanggal_pengembalian', $dateRange)
                    ->count();
            } else {
                $totalDikembalikanPeriod = PeminjamanModel::where('status', 'Dikembalikan')
                    ->whereHas('user', function ($q) {
                        $q->where('level', '!=', 'admin');
                    })
                    ->count();
            }
        }

        return response()->json([
            'levelData' => $levelData,
            'statusData' => [
                'sedang_pinjam' => $totalSedangPinjam,
                'sudah_kembali' => $totalDikembalikanPeriod
            ]
        ]);
    }

    private function getDateRange($period)
    {
        switch ($period) {
            case 'day':
                return [now()->startOfDay(), now()->endOfDay()];
            case 'week':
                return [now()->subDays(6)->startOfDay(), now()->endOfDay()];
            case 'month':
                return [now()->startOfMonth(), now()->endOfMonth()];
            case '6months':
                return [now()->subMonths(5)->startOfMonth(), now()->endOfMonth()];
            case 'year':
                return [now()->startOfYear(), now()->endOfYear()];
            default:
                return null; // No filter for all time
        }
    }
}
