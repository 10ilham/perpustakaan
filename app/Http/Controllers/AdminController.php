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
}
