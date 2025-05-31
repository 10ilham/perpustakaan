<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PeminjamanModel;
use App\Models\BukuModel;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Notifications\PeminjamanBukuAdminNotification;

class PeminjamanController extends Controller
{
    // Menampilkan daftar peminjaman
    public function index(Request $request)
    {
        // Perbarui status terlambat terlebih dahulu
        $this->updateLateStatus();

        // Ambil role user
        $userLevel = Auth::user()->level;

        // Filter berdasarkan user_type jika ada
        $userType = $request->input('user_type');

        // Filter berdasarkan rentang tanggal jika ada
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Base query untuk statistik dan daftar peminjaman
        $baseQuery = PeminjamanModel::with(['user', 'buku']);

        if ($userLevel == 'admin') {
            if ($userType) {
                // Jika ada filter, tambahkan where clause
                $peminjaman = $baseQuery->whereHas('user', function ($query) use ($userType) {
                    $query->where('level', $userType);
                });

                // Tambahkan filter rentang tanggal jika ada
                if ($startDate && $endDate) {
                    $peminjaman = $peminjaman->whereBetween('tanggal_pinjam', [$startDate, $endDate]);
                }

                $peminjaman = $peminjaman->orderBy('created_at', 'desc')->get();

                // Query untuk statistik berdasarkan filter
                $totalQuery = PeminjamanModel::whereHas('user', function ($query) use ($userType) {
                    $query->where('level', $userType);
                });

                $dipinjamQuery = PeminjamanModel::whereHas('user', function ($query) use ($userType) {
                    $query->where('level', $userType);
                })->where(function ($query) {
                    $query->where('status', 'Dipinjam')
                        ->orWhere('status', 'Terlambat');
                });

                $dikembalikanQuery = PeminjamanModel::whereHas('user', function ($query) use ($userType) {
                    $query->where('level', $userType);
                })->where('status', 'Dikembalikan');

                $terlambatQuery = PeminjamanModel::whereHas('user', function ($query) use ($userType) {
                    $query->where('level', $userType);
                })->where(function ($query) {
                    $query->where('status', 'Terlambat')
                        ->orWhere('is_terlambat', true);
                });

                // Tambahkan filter rentang tanggal jika ada
                if ($startDate && $endDate) {
                    $totalQuery = $totalQuery->whereBetween('tanggal_pinjam', [$startDate, $endDate]);
                    $dipinjamQuery = $dipinjamQuery->whereBetween('tanggal_pinjam', [$startDate, $endDate]);
                    $dikembalikanQuery = $dikembalikanQuery->whereBetween('tanggal_pinjam', [$startDate, $endDate]);
                    $terlambatQuery = $terlambatQuery->whereBetween('tanggal_pinjam', [$startDate, $endDate]);
                }

                $totalPeminjaman = $totalQuery->count();
                $dipinjam = $dipinjamQuery->count();
                $dikembalikan = $dikembalikanQuery->count();
                $terlambat = $terlambatQuery->count();
            } else {
                // Admin dapat melihat semua peminjaman jika tidak ada filter user type
                $peminjaman = $baseQuery;

                // Tambahkan filter rentang tanggal jika ada
                if ($startDate && $endDate) {
                    $peminjaman = $peminjaman->whereBetween('tanggal_pinjam', [$startDate, $endDate]);
                }

                $peminjaman = $peminjaman->orderBy('created_at', 'desc')->get();

                // Statistik untuk semua peminjaman
                $totalQuery = PeminjamanModel::query();

                $dipinjamQuery = PeminjamanModel::where(function ($query) {
                    $query->where('status', 'Dipinjam')
                        ->orWhere('status', 'Terlambat');
                });

                $dikembalikanQuery = PeminjamanModel::where('status', 'Dikembalikan');

                $terlambatQuery = PeminjamanModel::where(function ($query) {
                    $query->where('status', 'Terlambat')
                        ->orWhere('is_terlambat', true);
                });

                // Tambahkan filter rentang tanggal jika ada
                if ($startDate && $endDate) {
                    $totalQuery = $totalQuery->whereBetween('tanggal_pinjam', [$startDate, $endDate]);
                    $dipinjamQuery = $dipinjamQuery->whereBetween('tanggal_pinjam', [$startDate, $endDate]);
                    $dikembalikanQuery = $dikembalikanQuery->whereBetween('tanggal_pinjam', [$startDate, $endDate]);
                    $terlambatQuery = $terlambatQuery->whereBetween('tanggal_pinjam', [$startDate, $endDate]);
                }

                $totalPeminjaman = $totalQuery->count();
                $dipinjam = $dipinjamQuery->count();
                $dikembalikan = $dikembalikanQuery->count();
                $terlambat = $terlambatQuery->count();
            }
        } else {
            // Siswa, staff dan guru hanya melihat peminjaman mereka sendiri
            $peminjaman = $baseQuery->where('user_id', Auth::id());

            // Tambahkan filter rentang tanggal jika ada
            if ($startDate && $endDate) {
                $peminjaman = $peminjaman->whereBetween('tanggal_pinjam', [$startDate, $endDate]);
            }

            $peminjaman = $peminjaman->orderBy('created_at', 'desc')->get();

            // Statistik untuk peminjaman pengguna sendiri
            $totalQuery = PeminjamanModel::where('user_id', Auth::id());

            $dipinjamQuery = PeminjamanModel::where('user_id', Auth::id())->where(function ($query) {
                $query->where('status', 'Dipinjam')
                    ->orWhere('status', 'Terlambat');
            });

            $dikembalikanQuery = PeminjamanModel::where('user_id', Auth::id())->where('status', 'Dikembalikan');

            $terlambatQuery = PeminjamanModel::where('user_id', Auth::id())->where(function ($query) {
                $query->where('status', 'Terlambat')
                    ->orWhere('is_terlambat', true);
            });

            // Tambahkan filter rentang tanggal jika ada
            if ($startDate && $endDate) {
                $totalQuery = $totalQuery->whereBetween('tanggal_pinjam', [$startDate, $endDate]);
                $dipinjamQuery = $dipinjamQuery->whereBetween('tanggal_pinjam', [$startDate, $endDate]);
                $dikembalikanQuery = $dikembalikanQuery->whereBetween('tanggal_pinjam', [$startDate, $endDate]);
                $terlambatQuery = $terlambatQuery->whereBetween('tanggal_pinjam', [$startDate, $endDate]);
            }

            $totalPeminjaman = $totalQuery->count();
            $dipinjam = $dipinjamQuery->count();
            $dikembalikan = $dikembalikanQuery->count();
            $terlambat = $terlambatQuery->count();
        }

        return view('peminjaman.index', compact('peminjaman', 'totalPeminjaman', 'dipinjam', 'dikembalikan', 'terlambat', 'startDate', 'endDate'));
    }

    // Method untuk memperbarui status peminjaman yang terlambat
    private function updateLateStatus()
    {
        // Ambil semua peminjaman dengan status 'Dipinjam' yang sudah melewati tanggal kembali
        $latePeminjaman = PeminjamanModel::where('status', 'Dipinjam')
            ->where('tanggal_kembali', '<', Carbon::now()->format('Y-m-d'))
            ->get();

        // Ubah status menjadi 'Terlambat'
        foreach ($latePeminjaman as $peminjaman) {
            $peminjaman->status = 'Terlambat';
            $peminjaman->save();
        }
    }

    // Menampilkan form peminjaman buku
    public function formPinjam($id)
    {
        $buku = BukuModel::findOrFail($id);

        // Cek apakah stok buku tersedia
        if ($buku->stok_buku <= 0) {
            return redirect()->back()->with('error', 'Stok buku tidak tersedia untuk dipinjam.');
        }

        // Cek apakah user sudah meminjam buku yang sama dan belum dikembalikan
        // Perubahan: Mencakup semua status yang belum dikembalikan (Dipinjam DAN Terlambat)
        $sudahPinjam = PeminjamanModel::where('user_id', Auth::id())
            ->where('buku_id', $id)
            ->whereIn('status', ['Dipinjam', 'Terlambat'])
            ->first();

        if ($sudahPinjam) {
            return redirect()->back()->with('error', 'Anda sudah meminjam buku ini dan belum mengembalikannya.');
        }

        // Cek jumlah buku yang sedang dipinjam oleh user
        $jumlahPinjam = PeminjamanModel::where('user_id', Auth::id())
            ->whereIn('status', ['Dipinjam', 'Terlambat'])
            ->count();

        // Maksimal 1 buku yang boleh dipinjam dalam waktu bersamaan
        if ($jumlahPinjam >= 1) {
            return redirect()->back()->with('error', 'Anda sudah meminjam 1 buku. Silakan kembalikan buku tersebut terlebih dahulu untuk meminjam buku lain.');
        }

        return view('peminjaman.form', compact('buku'));
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
            ->whereIn('status', ['Dipinjam', 'Terlambat'])
            ->first();

        if ($sudahPinjam) {
            return redirect()->back()->with('error', 'Anda sudah meminjam buku ini dan belum mengembalikannya.')->withInput();
        }

        // Cek jumlah buku yang sedang dipinjam oleh user
        $jumlahPinjam = PeminjamanModel::where('user_id', Auth::id())
            ->whereIn('status', ['Dipinjam', 'Terlambat'])
            ->count();

        // Maksimal 1 buku yang boleh dipinjam dalam waktu bersamaan
        if ($jumlahPinjam >= 1) {
            return redirect()->back()->with('error', 'Anda sudah meminjam 1 buku. Silakan kembalikan buku tersebut terlebih dahulu untuk meminjam buku lain.')->withInput();
        }

        // Generate nomor peminjaman
        $no_peminjaman = 'PJM-' . date('YmdHis') . '-' . Str::random(5);

        // Buat record peminjaman
        $peminjaman = new PeminjamanModel();
        $peminjaman->user_id = Auth::id();
        $peminjaman->buku_id = $request->buku_id;
        $peminjaman->no_peminjaman = $no_peminjaman;
        $peminjaman->tanggal_pinjam = $request->tanggal_pinjam;
        $peminjaman->tanggal_kembali = $request->tanggal_kembali;
        $peminjaman->status = 'Dipinjam';
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
        // Ambil tanggal kembali dan jadikan jam 23:59:59 (akhir hari)
        $tanggalKembaliAkhirHari = \Carbon\Carbon::parse($peminjaman->tanggal_kembali)->endOfDay();

        // Hanya terlambat jika sudah melewati akhir hari tanggal kembali
        if ($peminjaman->status == 'Dipinjam' && now()->greaterThan($tanggalKembaliAkhirHari)) {
            return true;
        }
        return false;
    }

    // Menghitung jumlah hari keterlambatan
    public function hitungHariTerlambat($peminjaman)
    {
        // Ambil tanggal kembali
        $tanggalKembali = \Carbon\Carbon::parse($peminjaman->tanggal_kembali);
        $sekarang = \Carbon\Carbon::now();

        // Pastikan tanggal kembali hanya sampai tanggal saja (tanpa waktu)
        $tanggalKembali->startOfDay();
        $sekarang->startOfDay();

        // Hitung jumlah hari terlambat
        $diffInDays = $sekarang->diffInDays($tanggalKembali);

        // Kembalikan dalam format hari
        return $diffInDays . ' hari';
    }

    // Proses pengembalian buku (untuk Admin)
    public function kembalikanBuku($id)
    {
        $peminjaman = PeminjamanModel::findOrFail($id);

        // Hanya admin yang dapat mengembalikan buku
        if (Auth::user()->level != 'admin') {
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
        if (Auth::user()->level != 'admin') {
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
}
