<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PeminjamanModel;
use App\Models\BukuModel;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Notifications\PeminjamanBukuAdminNotification;
use App\Notifications\PeminjamanManualNotification;

class PeminjamanController extends Controller
{
    // Menampilkan daftar peminjaman
    /**
     * Helper method untuk menerapkan filter tanggal pada query peminjaman
     */
    private function applyDateFilter($query, $startDate, $endDate, $status = null)
    {
        // Fungsi ini hanya melihat tanggal_pinjam, terlepas dari status
        if ($startDate && $endDate) {
            // Gunakan Carbon untuk parsing tanggal
            $startDateFormatted = Carbon::parse($startDate)->startOfDay()->format('Y-m-d');
            $endDateFormatted = Carbon::parse($endDate)->endOfDay()->format('Y-m-d');

            // Filter tanggal_pinjam antara start dan end date
            return $query->whereDate('tanggal_pinjam', '>=', $startDateFormatted)
                ->whereDate('tanggal_pinjam', '<=', $endDateFormatted);
        } elseif ($startDate) {
            // Jika hanya ada startDate (endDate telah dihapus)
            $startDateFormatted = Carbon::parse($startDate)->startOfDay()->format('Y-m-d');
            return $query->whereDate('tanggal_pinjam', '>=', $startDateFormatted);
        } elseif ($endDate) {
            // Jika hanya ada endDate (startDate telah dihapus)
            $endDateFormatted = Carbon::parse($endDate)->endOfDay()->format('Y-m-d');
            return $query->whereDate('tanggal_pinjam', '<=', $endDateFormatted);
        }

        return $query;
    }

    public function index(Request $request)
    {
        // Perbarui status terlambat terlebih dahulu
        $this->updateLateStatus();

        // Ambil role user
        $userLevel = Auth::user()->level;

        // Filter berdasarkan user_type jika ada
        $userType = $request->input('user_type');

        // Filter status peminjaman
        $status = $request->input('status');

        // Filter berdasarkan rentang tanggal
        $startDate = $request->filled('start_date') ? $request->input('start_date') : null;
        $endDate = $request->filled('end_date') ? $request->input('end_date') : null;

        // Validasi format tanggal
        if ($startDate && !$this->validateDate($startDate)) {
            $startDate = null;
        }

        if ($endDate && !$this->validateDate($endDate)) {
            $endDate = null;
        }

        // Base query untuk statistik dan daftar peminjaman
        $baseQuery = PeminjamanModel::with(['user', 'buku']);

        if ($userLevel == 'admin') {
            if ($userType) {
                // Jika ada filter, tambahkan where clause
                $peminjaman = $baseQuery->whereHas('user', function ($query) use ($userType) {
                    $query->where('level', $userType);
                });

                // Tambahkan filter status jika ada
                if ($status) {
                    if ($status == 'Dipinjam') {
                        $peminjaman = $peminjaman->whereIn('status', ['Dipinjam', 'Terlambat']);
                    } elseif ($status == 'Terlambat') {
                        $peminjaman = $peminjaman->where(function ($query) {
                            $query->where('status', 'Terlambat')
                                ->orWhere(function ($q) {
                                    $q->where('status', 'Dikembalikan')
                                        ->where('is_terlambat', true);
                                });
                        });
                    } elseif ($status == 'Diproses') {
                        // Untuk status Diproses, khusus menghitung peminjaman dengan status Diproses
                        $peminjaman = PeminjamanModel::whereHas('user', function ($query) use ($userType) {
                            $query->where('level', $userType);
                        })->where('status', 'Diproses');
                    } elseif ($status == 'Dibatalkan') {
                        // Untuk status Dibatalkan, khusus menghitung peminjaman yang dibatalkan
                        $peminjaman = PeminjamanModel::whereHas('user', function ($query) use ($userType) {
                            $query->where('level', $userType);
                        })->where('status', 'Dibatalkan');
                    } else {
                        // Untuk status Dikembalikan
                        $peminjaman = $peminjaman->where('status', $status);
                    }
                }

                // Terapkan filter tanggal
                $peminjaman = $this->applyDateFilter($peminjaman, $startDate, $endDate, $status);
                $peminjaman = $peminjaman->orderBy('created_at', 'desc')->get();

                // Query untuk statistik berdasarkan filter
                $totalQuery = PeminjamanModel::whereHas('user', function ($query) use ($userType) {
                    $query->where('level', $userType);
                });

                // Exclude Diproses dan Dibatalkan dari total kecuali jika filter status adalah salah satu dari keduanya
                if ($status != 'Diproses' && $status != 'Dibatalkan') {
                    $totalQuery = $totalQuery->whereNotIn('status', ['Diproses', 'Dibatalkan']);
                }

                $dipinjamQuery = PeminjamanModel::whereHas('user', function ($query) use ($userType) {
                    $query->where('level', $userType);
                })->whereIn('status', ['Dipinjam', 'Terlambat']);

                $dikembalikanQuery = PeminjamanModel::whereHas('user', function ($query) use ($userType) {
                    $query->where('level', $userType);
                })->where('status', 'Dikembalikan');

                $terlambatQuery = PeminjamanModel::whereHas('user', function ($query) use ($userType) {
                    $query->where('level', $userType);
                })->where(function ($query) {
                    $query->where('status', 'Terlambat')
                        ->orWhere(function ($q) {
                            $q->where('status', 'Dikembalikan')
                                ->where('is_terlambat', true);
                        });
                });

                // Tambahkan filter status untuk statistik jika ada
                if ($status) {
                    if ($status == 'Dipinjam') {
                        $totalQuery = $totalQuery->whereIn('status', ['Dipinjam', 'Terlambat']);
                    } elseif ($status == 'Terlambat') {
                        $totalQuery = $totalQuery->where(function ($query) {
                            $query->where('status', 'Terlambat')
                                ->orWhere(function ($q) {
                                    $q->where('status', 'Dikembalikan')
                                        ->where('is_terlambat', true);
                                });
                        });
                    } elseif ($status == 'Diproses') {
                        // Untuk status Diproses, khusus menghitung peminjaman dengan status Diproses
                        $totalQuery = PeminjamanModel::where('user_id', Auth::id())->where('status', 'Diproses');
                    } elseif ($status == 'Dibatalkan') {
                        // Untuk status Dibatalkan, khusus menghitung peminjaman yang dibatalkan
                        $totalQuery = PeminjamanModel::where('user_id', Auth::id())->where('status', 'Dibatalkan');
                    } else {
                        // Untuk status Dikembalikan
                        $totalQuery = $totalQuery->where('status', $status);
                    }
                }

                // Terapkan filter tanggal di semua query statistik
                $totalQuery = $this->applyDateFilter($totalQuery, $startDate, $endDate, $status);
                $dipinjamQuery = $this->applyDateFilter($dipinjamQuery, $startDate, $endDate, 'Dipinjam');
                $dikembalikanQuery = $this->applyDateFilter($dikembalikanQuery, $startDate, $endDate, 'Dikembalikan');
                $terlambatQuery = $this->applyDateFilter($terlambatQuery, $startDate, $endDate, 'Terlambat');

                $totalPeminjaman = $totalQuery->count();
                $dipinjam = $dipinjamQuery->count();
                $dikembalikan = $dikembalikanQuery->count();
                $terlambat = $terlambatQuery->count();
            } else {
                // Admin dapat melihat semua peminjaman jika tidak ada filter user type
                $peminjaman = $baseQuery;

                // Tambahkan filter status jika ada
                if ($status) {
                    if ($status == 'Dipinjam') {
                        $peminjaman = $peminjaman->whereIn('status', ['Dipinjam', 'Terlambat']);
                    } elseif ($status == 'Terlambat') {
                        $peminjaman = $peminjaman->where(function ($query) {
                            $query->where('status', 'Terlambat')
                                ->orWhere(function ($q) {
                                    $q->where('status', 'Dikembalikan')
                                        ->where('is_terlambat', true);
                                });
                        });
                    } else {
                        // Untuk status Dikembalikan
                        $peminjaman = $peminjaman->where('status', $status);
                    }
                }

                // Terapkan filter tanggal
                $peminjaman = $this->applyDateFilter($peminjaman, $startDate, $endDate, $status);
                $peminjaman = $peminjaman->orderBy('created_at', 'desc')->get();

                // Statistik untuk semua peminjaman
                $totalQuery = PeminjamanModel::query();

                // Exclude Diproses dari total kecuali jika filter status adalah Diproses
                if ($status != 'Diproses' && $status != 'Dibatalkan') {
                    $totalQuery = $totalQuery->whereNotIn('status', ['Diproses', 'Dibatalkan']);
                }
                $dipinjamQuery = PeminjamanModel::whereIn('status', ['Dipinjam', 'Terlambat']);
                $dikembalikanQuery = PeminjamanModel::where('status', 'Dikembalikan');
                $terlambatQuery = PeminjamanModel::where(function ($query) {
                    $query->where('status', 'Terlambat')
                        ->orWhere(function ($q) {
                            $q->where('status', 'Dikembalikan')
                                ->where('is_terlambat', true);
                        });
                });

                // Tambahkan filter status untuk statistik jika ada
                if ($status) {
                    if ($status == 'Dipinjam') {
                        $totalQuery = $totalQuery->whereIn('status', ['Dipinjam', 'Terlambat']);
                    } elseif ($status == 'Terlambat') {
                        $totalQuery = $totalQuery->where(function ($query) {
                            $query->where('status', 'Terlambat')
                                ->orWhere(function ($q) {
                                    $q->where('status', 'Dikembalikan')
                                        ->where('is_terlambat', true);
                                });
                        });
                    } elseif ($status == 'Diproses') {
                        // Untuk status Diproses, khusus menghitung peminjaman dengan status Diproses
                        $totalQuery = PeminjamanModel::where('status', 'Diproses');
                    } elseif ($status == 'Dibatalkan') {
                        // Untuk status Dibatalkan, khusus menghitung peminjaman yang dibatalkan
                        $totalQuery = PeminjamanModel::where('status', 'Dibatalkan');
                    } else {
                        // Untuk status Dikembalikan
                        $totalQuery = $totalQuery->where('status', $status);
                    }
                }

                // Terapkan filter tanggal di semua query statistik
                $totalQuery = $this->applyDateFilter($totalQuery, $startDate, $endDate, $status);
                $dipinjamQuery = $this->applyDateFilter($dipinjamQuery, $startDate, $endDate, 'Dipinjam');
                $dikembalikanQuery = $this->applyDateFilter($dikembalikanQuery, $startDate, $endDate, 'Dikembalikan');
                $terlambatQuery = $this->applyDateFilter($terlambatQuery, $startDate, $endDate, 'Terlambat');

                $totalPeminjaman = $totalQuery->count();
                $dipinjam = $dipinjamQuery->count();
                $dikembalikan = $dikembalikanQuery->count();
                $terlambat = $terlambatQuery->count();
            }
        } else {
            // Siswa, staff dan guru hanya melihat peminjaman mereka sendiri
            $peminjaman = $baseQuery->where('user_id', Auth::id());

            // Tambahkan filter status jika ada
            if ($status) {
                if ($status == 'Dipinjam') {
                    $peminjaman = $peminjaman->whereIn('status', ['Dipinjam', 'Terlambat']);
                } elseif ($status == 'Terlambat') {
                    $peminjaman = $peminjaman->where(function ($query) {
                        $query->where('status', 'Terlambat')
                            ->orWhere(function ($q) {
                                $q->where('status', 'Dikembalikan')
                                    ->where('is_terlambat', true);
                            });
                    });
                } else {
                    // Untuk status Dikembalikan
                    $peminjaman = $peminjaman->where('status', $status);
                }
            }

            // Terapkan filter tanggal
            $peminjaman = $this->applyDateFilter($peminjaman, $startDate, $endDate, $status);
            $peminjaman = $peminjaman->orderBy('created_at', 'desc')->get();

            // Statistik untuk peminjaman pengguna sendiri
            $totalQuery = PeminjamanModel::where('user_id', Auth::id());

            // Exclude Diproses dari total kecuali jika filter status adalah Diproses
            if ($status != 'Diproses' && $status != 'Dibatalkan') {
                $totalQuery = $totalQuery->whereNotIn('status', ['Diproses', 'Dibatalkan']);
            }
            $dipinjamQuery = PeminjamanModel::where('user_id', Auth::id())->whereIn('status', ['Dipinjam', 'Terlambat']);
            $dikembalikanQuery = PeminjamanModel::where('user_id', Auth::id())->where('status', 'Dikembalikan');
            $terlambatQuery = PeminjamanModel::where('user_id', Auth::id())->where(function ($query) {
                $query->where('status', 'Terlambat')
                    ->orWhere(function ($q) {
                        $q->where('status', 'Dikembalikan')
                            ->where('is_terlambat', true);
                    });
            });

            // Tambahkan filter status untuk statistik jika ada
            if ($status) {
                if ($status == 'Dipinjam') {
                    $totalQuery = $totalQuery->whereIn('status', ['Dipinjam', 'Terlambat']);
                } elseif ($status == 'Terlambat') {
                    $totalQuery = $totalQuery->where(function ($query) {
                        $query->where('status', 'Terlambat')
                            ->orWhere(function ($q) {
                                $q->where('status', 'Dikembalikan')
                                    ->where('is_terlambat', true);
                            });
                    });
                } else {
                    // Untuk status Dikembalikan
                    $totalQuery = $totalQuery->where('status', $status);
                }
            }

            // Terapkan filter tanggal
            $totalQuery = $this->applyDateFilter($totalQuery, $startDate, $endDate, $status);
            $dipinjamQuery = $this->applyDateFilter($dipinjamQuery, $startDate, $endDate, 'Dipinjam');
            $dikembalikanQuery = $this->applyDateFilter($dikembalikanQuery, $startDate, $endDate, 'Dikembalikan');
            $terlambatQuery = $this->applyDateFilter($terlambatQuery, $startDate, $endDate, 'Terlambat');

            $totalPeminjaman = $totalQuery->count();
            $dipinjam = $dipinjamQuery->count();
            $dikembalikan = $dikembalikanQuery->count();
            $terlambat = $terlambatQuery->count();
        }

        return view('peminjaman.index', compact('peminjaman', 'totalPeminjaman', 'dipinjam', 'dikembalikan', 'terlambat', 'startDate', 'endDate', 'status'));
    }

    // Method untuk memperbarui status peminjaman yang terlambat
    private function updateLateStatus()
    {
        // Update status menjadi 'Terlambat' untuk peminjaman yang melewati batas
        PeminjamanModel::where('status', 'Dipinjam')
            ->where('tanggal_kembali', '<', Carbon::now()->format('Y-m-d'))
            ->update(['status' => 'Terlambat']);

        // Otomatis ubah status menjadi 'Dibatalkan' untuk peminjaman yang belum diambil (status Diproses) saat melewati Batas Waktu Pengembalian
        PeminjamanModel::where('status', 'Diproses')
            ->where('tanggal_kembali', '<', Carbon::now()->format('Y-m-d'))
            ->update([
                'status' => 'Dibatalkan',
            ]);

        // Kembalikan stok buku untuk peminjaman yang dibatalkan
        $dibatalkanBaru = PeminjamanModel::where('status', 'Dibatalkan')
            ->where('is_stok_returned', false)
            ->get();

        foreach ($dibatalkanBaru as $peminjaman) {
            $buku = BukuModel::find($peminjaman->buku_id);
            if ($buku) {
                // Tambah stok buku
                $buku->stok_buku += 1;

                // Update status buku jika stok tersedia
                if ($buku->stok_buku > 0) {
                    $buku->status = 'Tersedia';
                }

                $buku->save();

                // Tandai bahwa stok sudah dikembalikan
                $peminjaman->is_stok_returned = true;
                $peminjaman->save();
            }
        }
    }

    // Menampilkan form peminjaman buku
    public function formPinjam($id)
    {
        $buku = BukuModel::findOrFail($id);

        // Cek apakah stok buku tersedia
        if ($buku->stok_buku <= 0) {
            // Redirect ke halaman buku berdasarkan level user
            return $this->redirectToAppropriateView()->with('error', 'Stok buku tidak tersedia untuk dipinjam.');
        }

        // Cek apakah user sudah meminjam buku yang sama dan belum dikembalikan
        // Perubahan: Mencakup semua status yang belum dikembalikan (Dipinjam DAN Terlambat)
        $sudahPinjam = PeminjamanModel::where('user_id', Auth::id())
            ->where('buku_id', $id)
            ->whereIn('status', ['Dipinjam', 'Terlambat', 'Diproses'])
            ->first();

        if ($sudahPinjam) {
            return $this->redirectToAppropriateView()->with('error', 'Anda sudah meminjam buku ini dan belum mengembalikannya.');
        }

        // Cek jumlah buku yang sedang dipinjam oleh user
        $jumlahPinjam = PeminjamanModel::where('user_id', Auth::id())
            ->whereIn('status', ['Dipinjam', 'Terlambat', 'Diproses'])
            ->count();

        // Maksimal 1 buku yang boleh dipinjam dalam waktu bersamaan
        if ($jumlahPinjam >= 1) {
            return $this->redirectToAppropriateView()->with('error', 'Anda sudah meminjam 1 buku. Silakan kembalikan buku tersebut terlebih dahulu untuk meminjam buku lain.');
        }

        return view('peminjaman.form', compact('buku'));
    }

    // Helper method untuk redirect yang tepat berdasarkan level user untuk anggota ketika peminjaman buku tidak bisa dilakukan
    private function redirectToAppropriateView()
    {
        $userLevel = Auth::user()->level;

        // Redirect ke index buku untuk user selain admin yaitu anggota (siswa, guru, staff)
        if ($userLevel !== 'admin') {
            return redirect()->route('buku.index');
        }
    }

    // Proses peminjaman buku
    public function pinjamBuku(Request $request)
    {
        $request->validate(
            [
                'buku_id' => 'required|exists:buku,id',
                'nama' => 'required|string',
                'tanggal_pinjam' => 'required|date|after_or_equal:today',
                'tanggal_kembali' => 'required|date|after:tanggal_pinjam',
                'catatan' => 'nullable|string'
            ],
            [
                'buku_id.required' => 'ID buku diperlukan.',
                'buku_id.exists' => 'Buku tidak ditemukan.',

                'nama.required' => 'Nama peminjam harus diisi.',
                'nama.string' => 'Format nama peminjam tidak valid.',

                'tanggal_pinjam.required' => 'Tanggal pinjam harus diisi.',
                'tanggal_pinjam.date' => 'Format tanggal pinjam tidak valid.',
                'tanggal_pinjam.after_or_equal' => 'Tanggal pinjam minimal hari ini.',

                'tanggal_kembali.required' => 'Tanggal kembali harus diisi.',
                'tanggal_kembali.date' => 'Format tanggal kembali tidak valid.',
                'tanggal_kembali.after' => 'Tanggal kembali harus setelah tanggal pinjam.'
            ]
        );

        if ($request->nama != Auth::user()->nama) {
            return redirect()->back()->with('error', 'Nama peminjam harus sesuai dengan nama akun yang digunakan.')->withInput();
        }

        $buku = BukuModel::findOrFail($request->buku_id);

        // Cek stok buku lagi (double-check)
        if ($buku->stok_buku <= 0) {
            return redirect()->back()->with('error', 'Stok buku tidak tersedia untuk dipinjam.');
        }

        // Cek apakah user sudah meminjam buku yang sama dan belum dikembalikan (termasuk status Terlambat)
        $sudahPinjam = PeminjamanModel::where('user_id', Auth::id())
            ->where('buku_id', $request->buku_id)
            ->whereIn('status', ['Dipinjam', 'Terlambat', 'Diproses'])
            ->first();

        if ($sudahPinjam) {
            return redirect()->back()->with('error', 'Anda sudah meminjam buku ini dan belum mengembalikanannya.')->withInput();
        }

        // Cek jumlah buku yang sedang dipinjam oleh user
        $jumlahPinjam = PeminjamanModel::where('user_id', Auth::id())
            ->whereIn('status', ['Dipinjam', 'Terlambat', 'Diproses'])
            ->count();

        // Maksimal 1 buku yang boleh dipinjam dalam waktu bersamaan
        if ($jumlahPinjam >= 1) {
            return redirect()->back()->with('error', 'Anda sudah meminjam 1 buku. Silakan kembalikan buku tersebut terlebih dahulu untuk meminjam buku lain.')->withInput();
        }

        // Generate nomor peminjaman
        $no_peminjaman = 'PJM-' . date('YmdHis') . '-' . Str::random(2);

        // Buat record peminjaman
        $peminjaman = new PeminjamanModel();
        $peminjaman->user_id = Auth::id();
        $peminjaman->buku_id = $request->buku_id;
        $peminjaman->no_peminjaman = $no_peminjaman;
        $peminjaman->tanggal_pinjam = $request->tanggal_pinjam;
        $peminjaman->tanggal_kembali = $request->tanggal_kembali;
        $peminjaman->status = 'Diproses';
        $peminjaman->diproses_by = null; // Set diproses_by ke null untuk peminjaman self-service
        $peminjaman->catatan = $request->catatan;
        $peminjaman->save();

        // Kurangi stok buku
        $buku->stok_buku -= 1;

        // Update status buku jika stok habis
        if ($buku->stok_buku <= 0) {
            $buku->status = 'Habis';
        }

        $buku->save();

        // Kirim notifikasi ke admin tentang peminjaman baru
        $this->kirimNotifikasiPeminjamanBaru($peminjaman);

        return redirect()->route('peminjaman.index')->with('success', 'Buku berhasil dipinjam. Silahkan ambil buku di perpustakaan.');
    }

    // Menampilkan detail peminjaman
    public function detail($id)
    {
        // Perbarui status terlambat terlebih dahulu
        $this->updateLateStatus();

        // Ambil parameter referensi jika ada
        $ref = request('ref');
        $anggota_id = request('anggota_id');

        $peminjaman = PeminjamanModel::with(['user', 'buku'])->findOrFail($id);

        // Tambahkan informasi keterlambatan ke data peminjaman
        $isTerlambat = $peminjaman->status === 'Terlambat' ||
            $peminjaman->is_terlambat ||
            $this->cekKeterlambatan($peminjaman);

        $peminjaman->is_late = $isTerlambat;

        // Hitung hari terlambat dengan konsisten
        if ($isTerlambat) {
            // Gunakan jumlah_hari_terlambat jika sudah ada (buku sudah dikembalikan)
            if ($peminjaman->jumlah_hari_terlambat) {
                $peminjaman->late_days = $peminjaman->jumlah_hari_terlambat . ' hari';
            } else {
                // Jika belum dikembalikan, hitung dengan metode yang ada
                $peminjaman->late_days = $this->hitungHariTerlambat($peminjaman);
            }
        } else {
            $peminjaman->late_days = '0 hari';
        }

        // Menentukan apakah menampilkan tombol konfirmasi pengembalian
        $showReturnButton = false;
        if ($peminjaman->status == 'Dipinjam' || $peminjaman->status == 'Terlambat') {
            $showReturnButton = true;
        }

        return view('peminjaman.detail', compact('peminjaman', 'showReturnButton', 'ref', 'anggota_id'));
    }

    // Memeriksa apakah peminjaman terlambat dikembalikan
    public function cekKeterlambatan($peminjaman)
    {
        return $peminjaman->status == 'Dipinjam' && now()->greaterThan(Carbon::parse($peminjaman->tanggal_kembali)->endOfDay());
    }

    // Menghitung jumlah hari keterlambatan
    public function hitungHariTerlambat($peminjaman)
    {
        $tanggalKembali = Carbon::parse($peminjaman->tanggal_kembali)->startOfDay();
        $sekarang = Carbon::now()->startOfDay();

        return $sekarang->diffInDays($tanggalKembali) . ' hari';
    }

    // Proses pengembalian buku (untuk Admin)
    public function kembalikanBuku($id)
    {
        $peminjaman = PeminjamanModel::findOrFail($id);

        // Hanya admin yang dapat mengembalikan buku
        if (Auth::user()->level !== 'admin') {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengembalikan buku.');
        }

        // Ambil tanggal batas kembali dan jadikan jam 23:59:59 (akhir hari)
        $tanggalBatasKembali = Carbon::parse($peminjaman->tanggal_kembali)->endOfDay();
        $tanggalPengembalian = Carbon::now();

        // Periksa status terlambat, hanya terlambat jika pengembalian melewati akhir hari tanggal batas kembali
        $isTerlambat = false;
        if ($peminjaman->status == 'Terlambat' || $tanggalPengembalian->greaterThan($tanggalBatasKembali)) {
            $isTerlambat = true;
        }

        // Update status peminjaman ke 'Dikembalikan'
        // Namun tetap simpan informasi keterlambatan dengan field terpisah
        $peminjaman->is_terlambat = $isTerlambat; // Kolom(Field) untuk melacak keterlambatan
        $peminjaman->status = 'Dikembalikan';
        $peminjaman->tanggal_pengembalian = $tanggalPengembalian;

        // Hitung jumlah hari terlambat jika terlambat
        if ($isTerlambat) {
            // Gunakan startOfDay() untuk kedua tanggal agar perhitungan konsisten
            $hariTerlambat = Carbon::parse($peminjaman->tanggal_kembali)->startOfDay()
                ->diffInDays($tanggalPengembalian->copy()->startOfDay(), false);

            // Jika hasil negatif, ubah menjadi positif
            $hariTerlambat = abs($hariTerlambat);
            $peminjaman->jumlah_hari_terlambat = $hariTerlambat;
        } else {
            // Jika tidak terlambat, pastikan jumlah hari terlambat adalah 0
            $peminjaman->jumlah_hari_terlambat = 0;
        }

        $peminjaman->save();

        // Tambah stok buku
        $buku = BukuModel::findOrFail($peminjaman->buku_id);
        $buku->stok_buku += 1;

        // Update status buku jika stok tersedia
        if ($buku->stok_buku > 0) {
            $buku->status = 'Tersedia';
        }

        $buku->save();

        // Cek parameter referensi dari halaman anggota
        $ref = request('ref');
        $anggota_id = request('anggota_id');

        // Pesan sukses berdasarkan status keterlambatan
        $successMessage = $isTerlambat ?
            'Buku berhasil dikembalikan dengan status terlambat ' . $peminjaman->jumlah_hari_terlambat . ' hari.' :
            'Buku berhasil dikembalikan tepat waktu.';

        // Redirect berdasarkan parameter referensi
        if ($ref == 'anggota' && $anggota_id) {
            return redirect()->route('anggota.detail', $anggota_id)->with('success', $successMessage);
        } else {
            return redirect()->route('peminjaman.index')->with('success', $successMessage);
        }
    }

    // Hapus data peminjaman (khusus Admin)
    public function hapusPeminjaman($id)
    {
        $peminjaman = PeminjamanModel::findOrFail($id);

        // Hanya admin yang dapat menghapus data peminjaman
        if (Auth::user()->level !== 'admin') {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menghapus data peminjaman.');
        }

        // Jika status masih dipinjam, kembalikan stok buku terlebih dahulu
        if ($peminjaman->status == 'Dipinjam') {
            // Tambah stok buku
            $buku = BukuModel::findOrFail($peminjaman->buku_id);
            $buku->stok_buku += 1;

            // Update status buku jika stok tersedia
            if ($buku->stok_buku > 0) {
                $buku->status = 'Tersedia';
            }

            $buku->save();
        }

        // Hapus data peminjaman
        $peminjaman->delete();

        return redirect()->route('peminjaman.index')->with('success', 'Data peminjaman berhasil dihapus.');
    }

    // Mendapatkan buku populer (paling banyak dipinjam) untuk ditampilkan di dashboard
    public static function getBukuPopuler($limit = 10)
    {
        // Mengelompokkan peminjaman berdasarkan buku_id dan menghitung jumlahnya
        $bukuPopuler = PeminjamanModel::select('buku_id')
            ->selectRaw('COUNT(*) as total_peminjaman')
            ->whereNotIn('status', ['Diproses', 'Dibatalkan'])
            ->groupBy('buku_id')
            ->orderByRaw('COUNT(*) DESC')
            ->limit($limit)
            ->with('buku') // Load relasi buku
            ->get();

        return $bukuPopuler;
    }

    /**
     * Mengirim notifikasi ke semua admin tentang peminjaman buku baru
     * Terhubung ke Notifications/PeminjamanBukuAdminNotification
     * @param PeminjamanModel $peminjaman - Model peminjaman yang baru dibuat
     * @return void
     */
    private function kirimNotifikasiPeminjamanBaru(PeminjamanModel $peminjaman)
    {
        // Cari semua user dengan level admin
        $admin = User::whereIn('level', ['admin'])->get();

        foreach ($admin as $user) {
            // Kirim notifikasi ke masing-masing admin
            $user->notify(new PeminjamanBukuAdminNotification($peminjaman));
        }
    }

    /**
     * Mengirim notifikasi ke anggota tentang peminjaman manual yang dilakukan admin
     * Terhubung ke Notifications/PeminjamanManualNotification
     * @param PeminjamanModel $peminjaman - Model peminjaman yang baru dibuat
     * @return void
     */
    private function kirimNotifikasiPeminjamanManual(PeminjamanModel $peminjaman)
    {
        // Ambil user (anggota) yang terkait dengan peminjaman
        $anggota = User::find($peminjaman->user_id);

        if ($anggota) {
            // Kirim notifikasi ke anggota
            $anggota->notify(new PeminjamanManualNotification($peminjaman));
        }
    }

    /**
     * Menampilkan form peminjaman manual untuk admin
     * @return \Illuminate\Http\Response
     */
    public function formManual()
    {
        // Hanya admin yang bisa akses
        if (Auth::user()->level !== 'admin') {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk halaman ini.');
        }

        // Ambil semua buku yang tersedia
        $buku = BukuModel::where('status', 'Tersedia')->where('stok_buku', '>', 0)->get();

        return view('peminjaman.manual', compact('buku'));
    }

    /**
     * Mendapatkan daftar anggota berdasarkan level untuk peminjaman manual
     * Anggota yang sudah memiliki peminjaman aktif (status Dipinjam atau Terlambat) tidak ditampilkan
     */
    public function getAnggotaByLevel($level)
    {
        // Hanya admin yang bisa akses
        if (Auth::user()->level !== 'admin') {
            return response()->json(['error' => 'Dilarang masuk! Selain admin tidak diperbolehkan'], 403);
        }

        // Dapatkan user_id yang memiliki peminjaman aktif (status = 'Diproses' atau 'Dipinjam' atau 'Terlambat')
        $userIdDenganPeminjamanAktif = PeminjamanModel::whereIn('status', ['Diproses', 'Dipinjam', 'Terlambat'])
            ->pluck('user_id')
            ->toArray();

        $anggota = [];

        if ($level === 'siswa') {
            $anggota = User::where('level', 'siswa')
                ->whereNotIn('id', $userIdDenganPeminjamanAktif) // Exclude anggota dengan peminjaman aktif
                ->with('siswa')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'nama' => $user->nama,
                        'info' => $user->siswa ? 'NISN: ' . $user->siswa->nisn . ' - Kelas: ' . $user->siswa->kelas : 'Data tidak lengkap'
                    ];
                });
        } elseif ($level === 'guru') {
            $anggota = User::where('level', 'guru')
                ->whereNotIn('id', $userIdDenganPeminjamanAktif) // Exclude anggota dengan peminjaman aktif
                ->with('guru')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'nama' => $user->nama,
                        'info' => $user->guru ? 'NIP: ' . $user->guru->nip . ' - Mapel: ' . $user->guru->mata_pelajaran : 'Data tidak lengkap'
                    ];
                });
        } elseif ($level === 'staff') {
            $anggota = User::where('level', 'staff')
                ->whereNotIn('id', $userIdDenganPeminjamanAktif) // Exclude anggota dengan peminjaman aktif
                ->with('staff')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'nama' => $user->nama,
                        'info' => $user->staff ? 'NIP: ' . $user->staff->nip . ' - Bagian: ' . $user->staff->bagian : 'Data tidak lengkap'
                    ];
                });
        }

        return response()->json($anggota);
    }

    /**
     * Menyimpan peminjaman manual yang diinput oleh admin
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function simpanManual(Request $request)
    {
        // Hanya admin yang bisa akses
        if (Auth::user()->level !== 'admin') {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk fitur ini.');
        }

        $request->validate(
            [
                'buku_id' => 'required|exists:buku,id',
                'user_level' => 'required|in:siswa,guru,staff',
                'user_id' => 'required|exists:users,id',
                'tanggal_pinjam' => 'required|date|after_or_equal:today',
                'tanggal_kembali' => 'required|date|after:tanggal_pinjam',
                'catatan' => 'nullable|string|max:500'
            ],
            [
                'buku_id.required' => 'Buku harus dipilih.',
                'buku_id.exists' => 'Buku tidak ditemukan.',

                'user_level.required' => 'Level anggota harus dipilih.',
                'user_level.in' => 'Level anggota tidak valid.',

                'user_id.required' => 'Anggota harus dipilih.',
                'user_id.exists' => 'Anggota tidak ditemukan.',

                'tanggal_pinjam.required' => 'Tanggal pinjam harus diisi.',
                'tanggal_pinjam.date' => 'Format tanggal pinjam tidak valid.',
                'tanggal_pinjam.after_or_equal' => 'Tanggal pinjam minimal hari ini.',

                'tanggal_kembali.required' => 'Tanggal kembali harus diisi.',
                'tanggal_kembali.date' => 'Format tanggal kembali tidak valid.',
                'tanggal_kembali.after' => 'Tanggal kembali harus setelah tanggal pinjam.',

                'catatan.max' => 'Catatan tidak boleh lebih dari 500 karakter.'
            ]
        );

        // Validasi level user sesuai dengan user yang dipilih
        $user = User::findOrFail($request->user_id);
        if ($user->level !== $request->user_level) {
            return redirect()->back()->with('error', 'Level anggota tidak sesuai dengan anggota yang dipilih.')->withInput();
        }

        $buku = BukuModel::findOrFail($request->buku_id);

        // Cek stok buku
        if ($buku->stok_buku <= 0) {
            return redirect()->back()->with('error', 'Stok buku tidak tersedia untuk dipinjam.');
        }

        // Cek apakah user sudah meminjam buku yang sama dan belum dikembalikan
        $sudahPinjam = PeminjamanModel::where('user_id', $request->user_id)
            ->where('buku_id', $request->buku_id)
            ->whereIn('status', ['Dipinjam', 'Terlambat', 'Diproses'])
            ->first();

        if ($sudahPinjam) {
            return redirect()->back()->with('error', 'Anggota sudah meminjam buku ini dan belum mengembalikanannya.');
        }

        // Cek jumlah buku yang sedang dipinjam oleh user
        $jumlahPinjam = PeminjamanModel::where('user_id', $request->user_id)
            ->whereIn('status', ['Dipinjam', 'Terlambat', 'Diproses'])
            ->count();

        // Maksimal 1 buku yang boleh dipinjam dalam waktu bersamaan
        if ($jumlahPinjam >= 1) {
            return redirect()->back()->with('error', 'Anggota sudah meminjam 1 buku. Silakan kembalikan buku tersebut terlebih dahulu.');
        }

        // Generate nomor peminjaman
        $no_peminjaman = 'PJM-' . date('YmdHis') . '-' . Str::random(2);

        // Buat record peminjaman
        $peminjaman = new PeminjamanModel();
        $peminjaman->user_id = $request->user_id;
        $peminjaman->buku_id = $request->buku_id;
        $peminjaman->no_peminjaman = $no_peminjaman;
        $peminjaman->tanggal_pinjam = $request->tanggal_pinjam;
        $peminjaman->tanggal_kembali = $request->tanggal_kembali;
        $peminjaman->status = 'Diproses';
        $peminjaman->diproses_by = 'admin'; // Set diproses_by untuk peminjaman manual
        $peminjaman->catatan = $request->catatan;
        $peminjaman->save();

        // Kurangi stok buku
        $buku->stok_buku -= 1;

        // Update status buku jika stok habis
        if ($buku->stok_buku <= 0) {
            $buku->status = 'Habis';
        }

        $buku->save();

        // Kirim notifikasi ke anggota tentang peminjaman manual
        $this->kirimNotifikasiPeminjamanManual($peminjaman);

        return redirect()->route('peminjaman.index')->with('success', 'Peminjaman manual berhasil disimpan untuk anggota: ' . $user->nama);
    }

    // Proses konfirmasi pengambilan buku
    public function konfirmasiPengambilan($id)
    {
        $peminjaman = PeminjamanModel::findOrFail($id);

        // Cek status peminjaman harus dalam status Diproses
        if ($peminjaman->status !== 'Diproses') {
            return redirect()->back()->with('error', 'Status peminjaman tidak valid untuk pengambilan buku.');
        }

        // Update tanggal pinjam menjadi saat ini
        $peminjaman->tanggal_pinjam = now();
        $peminjaman->status = 'Dipinjam';
        $peminjaman->save();

        // Redirect berdasarkan level pengguna
        if (Auth::user()->level === 'admin') {
            return redirect()->route('peminjaman.index')->with('success', 'Konfirmasi pengambilan buku berhasil.');
        } else {
            return redirect()->route('peminjaman.index', $peminjaman->id)->with('success', 'Konfirmasi pengambilan buku berhasil.');
        }
    }

    /**
     * Helper method untuk memvalidasi format tanggal untuk filter data tanggal
     */
    private function validateDate($date)
    {
        return !empty($date) && is_string($date) && strtotime($date) !== false;
    }
}
