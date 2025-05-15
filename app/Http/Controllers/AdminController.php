<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AdminModel;
use App\Models\BukuModel;
use App\Models\KategoriModel;
use App\Models\PeminjamanModel;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function showAdminData()
    {
        // Ambil data admin berdasarkan user yang sedang login
        $admin = AdminModel::where('user_id', Auth::id())->first();

        if (!$admin) {
            return redirect()->back()->with('error', 'Data admin tidak ditemukan.');
        }

        // Data untuk dashboard
        $totalBuku = BukuModel::count();
        $totalKategori = KategoriModel::count();
        $totalPeminjaman = PeminjamanModel::count();

        // Total anggota tidak termasuk admin
        $totalAnggota = User::where('level', '!=', 'admin')->count();

        // Mendapatkan 10 buku terpopuler
        $bukuPopuler = PeminjamanController::getBukuPopuler(10);

        return view('layouts.AdminDashboard', compact('admin', 'totalBuku', 'totalKategori', 'totalPeminjaman', 'totalAnggota', 'bukuPopuler'));
    }

    public function showProfile()
    {
        // Ambil data admin berdasarkan user yang sedang login
        $admin = AdminModel::where('user_id', Auth::id())->first();

        if (!$admin) {
            return redirect()->back()->with('error', 'Data admin tidak ditemukan.');
        }

        return view('admin.profile', compact('admin'));
    }

    // Fungsi edit profile
    public function editProfile()
    {
        // Ambil data admin berdasarkan user yang sedang login
        $admin = AdminModel::where('user_id', Auth::id())->first();

        if (!$admin) {
            return redirect()->back()->with('error', 'Data admin tidak ditemukan.');
        }
        // Kirim data ke view untuk ditampilkan di form edit
        return view('admin.edit', compact('admin'));
    }

    // Fungsi untuk update profile
    public function updateProfile(Request $request)
    {
        // Buat pesan validasi kustom dalam bahasa Indonesia
        $messages = [
            'nama.required' => 'Nama lengkap wajib diisi',
            'nama.regex' => 'Nama hanya boleh berisi huruf dan spasi',
            'nama.max' => 'Nama tidak boleh lebih dari :max karakter',

            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.max' => 'Email tidak boleh lebih dari :max karakter',
            'email.unique' => 'Email sudah digunakan',

            'nip.required' => 'NIP wajib diisi',
            'nip.numeric' => 'NIP harus berupa angka',
            'nip.digits_between' => 'NIP harus terdiri dari 10 hingga 20 digit',
            'nip.unique' => 'NIP sudah digunakan',

            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid',

            'alamat.required' => 'Alamat wajib diisi',
            'alamat.string' => 'Alamat harus berupa teks',
            'alamat.max' => 'Alamat tidak boleh lebih dari :max karakter',

            'no_telepon.required' => 'Nomor telepon wajib diisi',
            'no_telepon.numeric' => 'Nomor telepon hanya boleh berisi angka',
            'no_telepon.digits_between' => 'Nomor telepon harus terdiri dari 10 hingga 15 digit',
            'no_telepon.unique' => 'Nomor telepon sudah digunakan',

            'password.min' => 'Password minimal :min karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',

            'foto.image' => 'File harus berupa gambar',
            'foto.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif',
            'foto.max' => 'Ukuran gambar tidak boleh lebih dari 3MB',
        ];

        // Ambil data admin berdasarkan user yang sedang login
        $admin = AdminModel::where('user_id', Auth::id())->first();

        // Validasi input
        $request->validate([
            'nama' => 'required|regex:/^[a-zA-Z\s]+$/|max:50',
            'email' => 'required|email|max:50|unique:users,email,' . $admin->user->id,
            'nip' => 'required|numeric|digits_between:10,20|unique:admin,nip,' . $admin->id . '|unique:guru,nip|unique:staff,nip',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'required|string|max:255',
            'no_telepon' => 'required|numeric|digits_between:10,15|unique:admin,no_telepon,' . $admin->id . '|unique:siswa,no_telepon|unique:guru,no_telepon|unique:staff,no_telepon',
            'password' => 'nullable|min:6|confirmed',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3048'
        ], $messages);

        if (!$admin) {
            return redirect()->back()->with('error', 'Data admin tidak ditemukan.');
        }

        // Siapkan data untuk update (kecualikan foto untuk mencegah overwrite (fotonya nambah terus)
        $adminData = $request->except('foto');

        // Cek apakah email diubah
        $emailChanged = $admin->user->email != $request->email;
        $oldEmail = $admin->user->email;
        $newEmail = $request->email;

        // Update nama user
        $admin->user->update([
            'nama' => $request->nama,
        ]);

        // Jika email berubah, kirim email verifikasi menggunakan VerificationController
        if ($emailChanged) {
            $verificationController = new VerificationController();
            return $verificationController->sendVerificationEmail($admin->user, $newEmail);
        }

        // update password jika diisi
        if ($request->password) {
            $admin->user->update([
                'password' => bcrypt($request->password),
            ]);
        }

        // Update data di tabel admin
        $admin->update($adminData);

        // Jika ada file foto yang diunggah
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($admin->foto && file_exists(public_path('assets/img/admin_foto/' . $admin->foto))) {
                unlink(public_path('assets/img/admin_foto/' . $admin->foto));
            }

            // Ambil nama file
            $nama_file = time() . '_' . $request->file('foto')->getClientOriginalName();

            // Simpan file ke folder public/assets/img/admin_foto
            $request->file('foto')->move(public_path('assets/img/admin_foto'), $nama_file);

            // Simpan HANYA nama file ke database, terpisah dari update data lainnya
            $admin->foto = $nama_file;
            $admin->save();
        }

        return redirect()->route('admin.profile')->with('success', 'Profile berhasil diperbarui.');
    }

    /**
     * Generate chart data for admin dashboard
     */
    public function getChartData(Request $request)
    {
        $period = $request->query('period', 'day'); // menentukan periode default ke 'day' yang ditampilkan di admin dashboard

        // Tentukan rentang waktu berdasarkan periode
        $startDate = now();
        $endDate = now();

        if ($period == 'day') {
            $startDate = now()->startOfDay();
            $endDate = now()->endOfDay();
            $format = 'H:i';
            $interval = 'hour';
            $intervalValue = 2; // setiap 2 jam
        } elseif ($period == 'week') {
            $startDate = now()->subDays(6)->startOfDay();
            $endDate = now()->endOfDay();
            $format = 'd/m';
            $interval = 'day';
            $intervalValue = 1; // setiap hari
        } elseif ($period == 'month') {
            $startDate = now()->subDays(29)->startOfDay();
            $endDate = now()->endOfDay();
            $format = 'd/m';
            $interval = 'day';
            $intervalValue = 1; // setiap 1 hari
        }

        // Ambil data total peminjaman untuk verifikasi
        $totalPeminjamanDB = PeminjamanModel::count();
        $totalPeminjamanSiswa = PeminjamanModel::whereHas('user', function ($query) {
            $query->where('level', 'siswa');
        })->count();
        $totalPeminjamanGuru = PeminjamanModel::whereHas('user', function ($query) {
            $query->where('level', 'guru');
        })->count();
        $totalPeminjamanStaff = PeminjamanModel::whereHas('user', function ($query) {
            $query->where('level', 'staff');
        })->count();

        // Generate labels untuk chart (tanggal)
        $labels = [];
        $current = clone $startDate;
        while ($current <= $endDate) {
            $labels[] = $current->format($format);
            if ($interval == 'hour') {
                $current->addHours($intervalValue);
            } elseif ($interval == 'day') {
                $current->addDays($intervalValue);
            }
        }

        // Ambil data peminjaman untuk setiap level user
        $siswaData = $this->getPeminjamanByPeriodAndLevel($startDate, $endDate, $interval, $intervalValue, 'siswa');
        $guruData = $this->getPeminjamanByPeriodAndLevel($startDate, $endDate, $interval, $intervalValue, 'guru');
        $staffData = $this->getPeminjamanByPeriodAndLevel($startDate, $endDate, $interval, $intervalValue, 'staff');

        // Hitung total dari semua data grafik untuk verifikasi
        $totalInChart = array_sum($siswaData) + array_sum($guruData) + array_sum($staffData);

        // Get actual loan dates from database for verification
        $actualSiswaLoanDates = PeminjamanModel::whereHas('user', function ($query) {
            $query->where('level', 'siswa');
        })
            ->whereBetween('tanggal_pinjam', [$startDate, $endDate])
            ->select('tanggal_pinjam')
            ->get()
            ->map(function ($item) {
                return \Carbon\Carbon::parse($item->tanggal_pinjam)->format('d/m/Y');
            });

        $actualGuruLoanDates = PeminjamanModel::whereHas('user', function ($query) {
            $query->where('level', 'guru');
        })
            ->whereBetween('tanggal_pinjam', [$startDate, $endDate])
            ->select('tanggal_pinjam')
            ->get()
            ->map(function ($item) {
                return \Carbon\Carbon::parse($item->tanggal_pinjam)->format('d/m/Y');
            });

        $actualStaffLoanDates = PeminjamanModel::whereHas('user', function ($query) {
            $query->where('level', 'staff');
        })
            ->whereBetween('tanggal_pinjam', [$startDate, $endDate])
            ->select('tanggal_pinjam')
            ->get()
            ->map(function ($item) {
                return \Carbon\Carbon::parse($item->tanggal_pinjam)->format('d/m/Y');
            });

        // Kembalikan data dalam format JSON
        return response()->json([
            'labels' => $labels,
            'siswa' => $siswaData,
            'guru' => $guruData,
            'staff' => $staffData,
            'totalPeminjamanDB' => $totalPeminjamanDB,
            'totalSiswa' => $totalPeminjamanSiswa,
            'totalGuru' => $totalPeminjamanGuru,
            'totalStaff' => $totalPeminjamanStaff,
            'totalInChart' => $totalInChart,
            'actualDates' => [
                'siswa' => $actualSiswaLoanDates,
                'guru' => $actualGuruLoanDates,
                'staff' => $actualStaffLoanDates
            ]
        ]);
    }

    /**
     * Helper function to get loan data by period and user level
     */
    private function getPeminjamanByPeriodAndLevel($startDate, $endDate, $interval, $intervalValue, $userLevel)
    {
        $data = [];
        $current = clone $startDate;
        $dateFormat = 'd/m/Y'; // Format for displaying and debugging dates

        // Ambil data peminjaman aktual dari database terlebih dahulu
        $peminjamanData = PeminjamanModel::whereHas('user', function ($query) use ($userLevel) {
            $query->where('level', $userLevel);
        })
            ->whereBetween('tanggal_pinjam', [$startDate, $endDate])
            ->select('tanggal_pinjam')
            ->get();

        $actualDates = $peminjamanData->map(function ($item) use ($dateFormat) {
            return \Carbon\Carbon::parse($item->tanggal_pinjam)->format($dateFormat);
        })->toArray();

        // Initialize empty array with correct date keys based on periods
        $dateMap = [];
        $tempCurrent = clone $startDate;
        while ($tempCurrent <= $endDate) {
            $dateKey = $tempCurrent->format($dateFormat);
            $dateMap[$dateKey] = 0;

            if ($interval == 'hour') {
                $tempCurrent->addHours($intervalValue);
            } elseif ($interval == 'day') {
                $tempCurrent->addDays($intervalValue);
            }
        }

        // Count actual loans for each date
        foreach ($peminjamanData as $peminjaman) {
            $dateKey = \Carbon\Carbon::parse($peminjaman->tanggal_pinjam)->format($dateFormat);
            if (isset($dateMap[$dateKey])) {
                $dateMap[$dateKey]++;
            }
        }

        // Now create data array in the correct sequence
        $current = clone $startDate;
        while ($current <= $endDate) {
            $dateKey = $current->format($dateFormat);
            // Only add count for dates that exist in the dateMap
            $data[] = isset($dateMap[$dateKey]) ? $dateMap[$dateKey] : 0;

            if ($interval == 'hour') {
                $current->addHours($intervalValue);
            } elseif ($interval == 'day') {
                $current->addDays($intervalValue);
            }
        }

        return $data;
    }
}
