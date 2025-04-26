<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AdminModel;
use App\Models\SiswaModel;
use App\Models\GuruModel;
use App\Models\StaffModel;

class AnggotaController extends Controller
{
    //
    public function index(Request $request)
    {
        // Filter level
        $level = $request->query('level', 'all');

        // Mengambil data user dengan filter level jika diperlukan
        $query = User::query();

        if ($level !== 'all') {
            $query->where('level', $level);
        }

        $users = $query->get();

        // Data untuk dropdown filter
        $levels = ['all' => 'Semua Level', 'admin' => 'Admin', 'siswa' => 'Siswa', 'guru' => 'Guru', 'staff' => 'Staff'];

        return view('anggota.index', compact('users', 'level', 'levels'));
    }

    // Menampilkan detail anggota
    public function detail($id)
    {
        $user = User::findOrFail($id);
        $profileData = null;

        // Ambil data profil sesuai level
        if ($user->level === 'admin') {
            $profileData = AdminModel::where('user_id', $user->id)->first();
        } elseif ($user->level === 'siswa') {
            $profileData = SiswaModel::where('user_id', $user->id)->first();
        } elseif ($user->level === 'guru') {
            $profileData = GuruModel::where('user_id', $user->id)->first();
        } elseif ($user->level === 'staff') {
            $profileData = StaffModel::where('user_id', $user->id)->first();
        }

        return view('anggota.detail', compact('user', 'profileData'));
    }

    // Tambah anggota
    public function tambah()
    {
        return view('anggota.tambah');
    }

    // Simpan anggota
    public function simpan(Request $request)
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

            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal :min karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',

            'level.required' => 'Level wajib dipilih',
            'level.in' => 'Level tidak valid',

            'foto.image' => 'File harus berupa gambar',
            'foto.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif',
            'foto.max' => 'Ukuran gambar tidak boleh lebih dari 3MB',

            'nip.required_if' => 'NIP wajib diisi',
            'nip.numeric' => 'NIP harus berupa angka',
            'nip.digits_between' => 'NIP harus terdiri dari 10 hingga 20 digit',
            'nip.unique' => 'NIP sudah digunakan',

            'nis.required_if' => 'NIS wajib diisi',
            'nis.numeric' => 'NIS harus berupa angka',
            'nis.digits_between' => 'NIS harus terdiri dari 10 hingga 20 digit',
            'nis.unique' => 'NIS sudah digunakan',

            'kelas.required_if' => 'Kelas wajib diisi',
            'kelas.string' => 'Kelas harus berupa teks',
            'kelas.max' => 'Kelas tidak boleh lebih dari :max karakter',

            'mata_pelajaran.required_if' => 'Mata pelajaran wajib diisi',
            'mata_pelajaran.string' => 'Mata pelajaran harus berupa teks',
            'mata_pelajaran.max' => 'Mata pelajaran tidak boleh lebih dari :max karakter',

            'bagian.required_if' => 'Bagian wajib diisi',
            'bagian.string' => 'Bagian harus berupa teks',
            'bagian.max' => 'Bagian tidak boleh lebih dari :max karakter',

            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid',

            'alamat.required' => 'Alamat wajib diisi',
            'alamat.string' => 'Alamat harus berupa teks',
            'alamat.max' => 'Alamat tidak boleh lebih dari :max karakter',

            'no_telepon.required' => 'Nomor telepon wajib diisi',
            'no_telepon.numeric' => 'Nomor telepon hanya boleh berisi angka',
            'no_telepon.digits_between' => 'Nomor telepon harus terdiri dari 10 hingga 15 digit',
            'no_telepon.unique' => 'Nomor telepon sudah digunakan',
        ];

        // Validasi input
        $request->validate([
            'nama' => 'required|regex:/^[a-zA-Z\s]+$/|max:50',
            'email' => 'required|email|max:50|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'level' => 'required|in:admin,siswa,guru,staff',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3048',
            // Validasi sesuai level
            'nip' => 'required_if:level,admin,guru,staff|numeric|digits_between:10,20|unique:admin,nip|unique:guru,nip|unique:staff,nip',
            'nis' => 'required_if:level,siswa|numeric|digits_between:10,20|unique:siswa,nis',
            'kelas' => 'required_if:level,siswa|string|max:10',
            'mata_pelajaran' => 'required_if:level,guru|string|max:50',
            'bagian' => 'required_if:level,staff|string|max:50',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'required|string|max:255',
            'no_telepon' => 'required|numeric|digits_between:10,15|unique:admin,no_telepon|unique:siswa,no_telepon|unique:guru,no_telepon|unique:staff,no_telepon',
        ], $messages);

        $user = User::create($request->only('nama', 'email', 'level', 'password'));
        $user->password = bcrypt($request->password);
        $user->save();
        // Simpan data anggota sesuai level
        if ($user->level === 'admin') {
            $profileData = new AdminModel($request->only('nip', 'tanggal_lahir', 'alamat', 'no_telepon'));
            $folder = 'admin_foto';
        } elseif ($user->level === 'siswa') {
            $profileData = new SiswaModel($request->only('nis', 'kelas', 'tanggal_lahir', 'alamat', 'no_telepon'));
            $folder = 'siswa_foto';
        } elseif ($user->level === 'guru') {
            $profileData = new GuruModel($request->only('nip', 'mata_pelajaran', 'tanggal_lahir', 'alamat', 'no_telepon'));
            $folder = 'guru_foto';
        } elseif ($user->level === 'staff') {
            $profileData = new StaffModel($request->only('nip', 'bagian', 'tanggal_lahir', 'alamat', 'no_telepon'));
            $folder = 'staff_foto';
        }
        $profileData->user_id = $user->id;
        $profileData->save();
        // Handle foto upload
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $nama_file = time() . '_' . $foto->getClientOriginalName(); // Menambahkan timestamp untuk menghindari duplikasi
            $foto->move(public_path('assets/img/' . $folder), $nama_file);

            $profileData->foto = $nama_file;
            $profileData->save();
        }
        return redirect()->route('anggota.index')->with('success', 'Anggota baru berhasil ditambahkan.');
    }

    // Edit anggota
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $profileData = null;

        // Ambil data profil sesuai level
        if ($user->level === 'admin') {
            $profileData = AdminModel::where('user_id', $user->id)->first();
        } elseif ($user->level === 'siswa') {
            $profileData = SiswaModel::where('user_id', $user->id)->first();
        } elseif ($user->level === 'guru') {
            $profileData = GuruModel::where('user_id', $user->id)->first();
        } elseif ($user->level === 'staff') {
            $profileData = StaffModel::where('user_id', $user->id)->first();
        }

        return view('anggota.edit', compact('user', 'profileData'));
    }

    // Update anggota
    public function update(Request $request, $id)
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

            'password.min' => 'Password minimal :min karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',

            'foto.image' => 'File harus berupa gambar',
            'foto.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif',
            'foto.max' => 'Ukuran gambar tidak boleh lebih dari 3MB',

            'nip.required_if' => 'NIP wajib diisi',
            'nip.numeric' => 'NIP harus berupa angka',
            'nip.digits_between' => 'NIP harus terdiri dari 10 hingga 20 digit',
            'nip.unique' => 'NIP sudah digunakan',

            'nis.required_if' => 'NIS wajib diisi',
            'nis.numeric' => 'NIS harus berupa angka',
            'nis.digits_between' => 'NIS harus terdiri dari 10 hingga 20 digit',
            'nis.unique' => 'NIS sudah digunakan',

            'kelas.required_if' => 'Kelas wajib diisi',
            'kelas.string' => 'Kelas harus berupa teks',
            'kelas.max' => 'Kelas tidak boleh lebih dari :max karakter',

            'mata_pelajaran.required_if' => 'Mata pelajaran wajib diisi',
            'mata_pelajaran.string' => 'Mata pelajaran harus berupa teks',
            'mata_pelajaran.max' => 'Mata pelajaran tidak boleh lebih dari :max karakter',

            'bagian.required_if' => 'Bagian wajib diisi',
            'bagian.string' => 'Bagian harus berupa teks',
            'bagian.max' => 'Bagian tidak boleh lebih dari :max karakter',

            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid',

            'alamat.required' => 'Alamat wajib diisi',
            'alamat.string' => 'Alamat harus berupa teks',
            'alamat.max' => 'Alamat tidak boleh lebih dari :max karakter',

            'no_telepon.required' => 'Nomor telepon wajib diisi',
            'no_telepon.numeric' => 'Nomor telepon hanya boleh berisi angka',
            'no_telepon.digits_between' => 'Nomor telepon harus terdiri dari 10 hingga 15 digit',
            'no_telepon.unique' => 'Nomor telepon sudah digunakan',
        ];
        // Validasi input
        $request->validate([
            'nama' => 'required|regex:/^[a-zA-Z\s]+$/|max:50',
            'email' => 'required|email|max:50|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6|confirmed',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3048',
            // Validasi sesuai level
            'nip' => 'required_if:level,admin,guru,staff|numeric|digits_between:10,20|unique:admin,nip,' . $id . ',user_id|unique:guru,nip,' . $id . ',user_id|unique:staff,nip,' . $id . ',user_id',
            'nis' => 'required_if:level,siswa|numeric|digits_between:10,20|unique:siswa,nis,' . $id . ',user_id',
            'kelas' => 'required_if:level,siswa|string|max:10',
            'mata_pelajaran' => 'required_if:level,guru|string|max:50',
            'bagian' => 'required_if:level,staff|string|max:50',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'required|string|max:255',
            'no_telepon' => 'required|numeric|digits_between:10,15|unique:admin,no_telepon,' . $id . ',user_id|unique:siswa,no_telepon,' . $id . ',user_id|unique:guru,no_telepon,' . $id . ',user_id|unique:staff,no_telepon,' . $id . ',user_id',
        ], $messages);
        $user = User::findOrFail($id);
        $user->update($request->only('nama', 'email'));

        // Update password jika diisi
        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
            $user->save();
        }

        // Update data anggota sesuai level
        if ($user->level === 'admin') {
            $profileData = AdminModel::where('user_id', $user->id)->first();
            $profileData->update($request->only('nip', 'tanggal_lahir', 'alamat', 'no_telepon'));
            $folder = 'admin_foto';
        } elseif ($user->level === 'siswa') {
            $profileData = SiswaModel::where('user_id', $user->id)->first();
            $profileData->update($request->only('nis', 'kelas', 'tanggal_lahir', 'alamat', 'no_telepon'));
            $folder = 'siswa_foto';
        } elseif ($user->level === 'guru') {
            $profileData = GuruModel::where('user_id', $user->id)->first();
            $profileData->update($request->only('nip', 'mata_pelajaran', 'tanggal_lahir', 'alamat', 'no_telepon'));
            $folder = 'guru_foto';
        } elseif ($user->level === 'staff') {
            $profileData = StaffModel::where('user_id', $user->id)->first();
            $profileData->update($request->only('nip', 'bagian', 'tanggal_lahir', 'alamat', 'no_telepon'));
            $folder = 'staff_foto';
        }

        // Handle foto upload
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($profileData->foto && file_exists(public_path('assets/img/' . $folder . '/' . $profileData->foto))) {
                unlink(public_path('assets/img/' . $folder . '/' . $profileData->foto));
            }

            $foto = $request->file('foto');
            $nama_file = time() . '_' . $foto->getClientOriginalName(); // Menambahkan timestamp untuk menghindari duplikasi
            $foto->move(public_path('assets/img/' . $folder), $nama_file);

            $profileData->foto = $nama_file;
            $profileData->save();
        }

        return redirect()->route('anggota.index')->with('success', 'Anggota berhasil diperbarui.');
    }

    // Hapus anggota
    public function hapus($id)
    {
        $user = User::findOrFail($id);

        // Hapus data profil sesuai level
        if ($user->level === 'admin') {
            $profile = AdminModel::where('user_id', $user->id)->first();
            if ($profile) {
                // Hapus foto jika ada
                if ($profile->foto && file_exists(public_path('assets/img/admin_foto/' . $profile->foto))) {
                    unlink(public_path('assets/img/admin_foto/' . $profile->foto));
                }
                $profile->delete();
            }
        } elseif ($user->level === 'siswa') {
            $profile = SiswaModel::where('user_id', $user->id)->first();
            if ($profile) {
                // Hapus foto jika ada
                if ($profile->foto && file_exists(public_path('assets/img/siswa_foto/' . $profile->foto))) {
                    unlink(public_path('assets/img/siswa_foto/' . $profile->foto));
                }
                $profile->delete();
            }
        } elseif ($user->level === 'guru') {
            $profile = GuruModel::where('user_id', $user->id)->first();
            if ($profile) {
                // Hapus foto jika ada
                if ($profile->foto && file_exists(public_path('assets/img/guru_foto/' . $profile->foto))) {
                    unlink(public_path('assets/img/guru_foto/' . $profile->foto));
                }
                $profile->delete();
            }
        } elseif ($user->level === 'staff') {
            $profile = StaffModel::where('user_id', $user->id)->first();
            if ($profile) {
                // Hapus foto jika ada
                if ($profile->foto && file_exists(public_path('assets/img/staff_foto/' . $profile->foto))) {
                    unlink(public_path('assets/img/staff_foto/' . $profile->foto));
                }
                $profile->delete();
            }
        }

        // Hapus user
        $user->delete();
        return redirect()->route('anggota.index')->with('success', 'Anggota berhasil dihapus');
    }
}
