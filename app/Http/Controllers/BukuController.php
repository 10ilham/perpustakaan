<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BukuModel;
use App\Models\KategoriModel;
use App\Models\AdminModel;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BukuController extends Controller
{
    //Menampilkan data buku
    public function index(Request $request)
    {
        // Ambil parameter filter dan pencarian
        $kategoriId = $request->get('kategori');
        $status = $request->get('status');
        $search = $request->get('search');

        // Base query untuk ngeloading data kategori
        $query = BukuModel::with('kategori');

        // Terapkan filter kategori jika ada
        if ($kategoriId) {
            $query->whereHas('kategori', function ($q) use ($kategoriId) {
                $q->where('kategori.id', $kategoriId);
            });
        }

        // Terapkan filter status jika ada
        if ($status) {
            $query->where('status', $status);
        }

        // Terapkan pencarian judul jika ada
        if ($search) {
            $query->where('judul', 'like', '%' . $search . '%');
        }

        // Clone query untuk perhitungan statistik, total buku, buku tersedia, dan buku habis
        $totalQuery = clone $query;

        // Dapatkan hasil buku dengan pagination, maksimal menampilkan 8 buku per halaman
        $buku = $query->paginate(8)->appends($request->all());

        // Ambil semua kategori untuk dropdown filter
        $kategori = KategoriModel::all();

        // Hitung jumlah total buku dengan filter yang sama
        $totalBuku = $totalQuery->count();

        // Hitung jumlah buku tersedia dengan filter yang sama
        $tersediaQuery = clone $totalQuery;
        $tersedia = $tersediaQuery->where('status', 'Tersedia')->count();

        // Hitung jumlah buku habis dengan filter yang sama
        $habisQuery = clone $totalQuery;
        $habis = $habisQuery->where('status', 'Habis')->count();

        return view('buku.index', compact('buku', 'kategori', 'totalBuku', 'tersedia', 'habis'));
    }

    //Menampilkan form tambah buku
    public function tambah()
    {
        $kategori = KategoriModel::all();
        return view('buku.tambah', compact('kategori'));
    }

    //Menyimpan data buku
    public function simpan(Request $request)
    {
        $messages = [
            'kode_buku.required' => 'Kode buku harus diisi.',
            'kode_buku.max' => 'Kode buku tidak boleh lebih dari :max karakter.',
            'kode_buku.unique' => 'Kode buku sudah ada.',

            'judul.required' => 'Judul buku harus diisi.',
            'judul.string' => 'Judul buku harus berupa string.',
            'judul.max' => 'Judul buku tidak boleh lebih dari :max karakter.',

            'pengarang.required' => 'Pengarang buku harus diisi.',
            'pengarang.string' => 'Pengarang buku harus berupa string.',
            'pengarang.max' => 'Pengarang buku tidak boleh lebih dari :max karakter.',

            'penerbit.required' => 'Penerbit buku harus diisi.',
            'penerbit.string' => 'Penerbit buku harus berupa string.',
            'penerbit.max' => 'Penerbit buku tidak boleh lebih dari :max karakter.',

            'tahun_terbit.required' => 'Tahun terbit buku harus diisi.',
            'tahun_terbit.numeric' => 'Tahun terbit buku harus berupa angka.',
            'tahun_terbit.digits' => 'Tahun terbit buku harus terdiri dari 4 digit.',

            'deskripsi.required' => 'Deskripsi buku harus diisi.',
            'deskripsi.string' => 'Deskripsi buku harus berupa string.',

            'foto.image' => 'File yang diunggah harus berupa gambar.',
            'foto.mimes' => 'File yang diunggah harus berupa jpeg, png, jpg, atau gif.',
            'foto.max' => 'Ukuran file tidak boleh lebih dari 3MB.',

            'total_buku.required' => 'Stok buku harus diisi.',
            'total_buku.integer' => 'Stok buku harus berupa angka.',
            'total_buku.min' => 'Stok buku tidak boleh kurang dari 0.',

            'kategori_id.required' => 'Kategori buku harus diisi minimal 1.',
            'kategori_id.min' => 'Kategori minimal 1 kategori harus dipilih.',
        ];

        // Validasi input
        $request->validate([
            'kode_buku' => 'required|max:22|unique:buku,kode_buku',
            'judul' => 'required|string|max:60',
            'pengarang' => 'required|string|max:50',
            'penerbit' => 'required|string|max:50',
            'tahun_terbit' => 'required|numeric|digits:4',
            'deskripsi' => 'required|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3048',
            'total_buku' => 'required|integer|min:0',
            'kategori_id' => 'required|min:1',
        ], $messages);

        // Ambil data admin yang sedang login, untuk mengisi id_admin
        $adminModel = AdminModel::where('user_id', Auth::id())->first();

        $buku = new BukuModel();
        $buku->kode_buku = $request->kode_buku;
        $buku->judul = $request->judul;
        $buku->pengarang = $request->pengarang;
        $buku->penerbit = $request->penerbit;
        $buku->tahun_terbit = $request->tahun_terbit;
        $buku->deskripsi = $request->deskripsi;
        $buku->total_buku = $request->total_buku;
        $buku->stok_buku = $request->total_buku; // Stok awal sama dengan total buku

        // Set id_admin berdasarkan admin yang sedang login
        if ($adminModel) {
            $buku->id_admin = $adminModel->id;
        }

        // Set status berdasarkan stok, jika <= 0, set status "Habis" jika >=0, set status "Tersedia"
        if ($buku->stok_buku <= 0) {
            $buku->status = 'Habis';
        } else {
            $buku->status = 'Tersedia';
        }

        // Simpan buku terlebih dahulu untuk mendapatkan ID
        $buku->save();

        // Setelah save, baru upload foto (sehingga ID buku tersedia)
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $nama_file = $buku->id . '_' . $foto->getClientOriginalName(); // Sekarang ID buku sudah tersedia
            $foto->move(public_path('assets/img/buku/'), $nama_file);
            $buku->foto = $nama_file;
            // Update kembali data buku untuk menyimpan nama file foto
            $buku->save();
        }

        if ($request->has('kategori_id')) {
            $buku->kategori()->attach($request->kategori_id);
        }

        return redirect()->route('buku.index')->with('success', 'Buku berhasil ditambahkan.');
    }

    // Menampilkan detail buku
    public function detail($id)
    {
        $buku = BukuModel::with('kategori')->findOrFail($id);

        // Total stok buku sebelum dikurangi peminjaman (menggunakan kolom total_buku)
        $totalStokBuku = $buku->total_buku;

        // Ambil referensi dari mana pengguna berasal
        $ref = request('ref');
        $kategori_id = request('kategori_id'); // id referensi untuk kembali ke halaman kategori
        $dashboard = request('dashboard'); // id referensi untuk kembali ke halaman dashboard

        // Referensi untuk kembali ke page sebelumnya
        $page = request('page');
        $search = request('search');
        $kategoriFilter = request('kategori');
        $status = request('status');

        return view('buku.detail', compact('buku', 'totalStokBuku', 'ref', 'kategori_id', 'dashboard', 'page', 'search', 'kategoriFilter', 'status'));
    }

    // Menampilkan form edit buku
    public function edit($id)
    {
        $buku = BukuModel::findOrFail($id);
        $kategori = KategoriModel::all();

        // Ambil referensi dari mana pengguna berasal
        $ref = request('ref');
        $kategori_id = request('kategori_id');

        // Referensi untuk kembali ke page sebelumnya
        $page = request('page');
        $search = request('search');
        $kategoriFilter = request('kategori');
        $status = request('status');

        return view('buku.edit', compact('buku', 'kategori', 'ref', 'kategori_id', 'page', 'search', 'kategoriFilter', 'status'));
    }

    // Mengupdate data buku
    public function update(Request $request, $id)
    {
        $messages = [
            'kode_buku.required' => 'Kode buku harus diisi.',
            'kode_buku.max' => 'Kode buku tidak boleh lebih dari :max karakter.',
            'kode_buku.unique' => 'Kode buku sudah ada.',

            'judul.required' => 'Judul buku harus diisi.',
            'judul.string' => 'Judul buku harus berupa string.',
            'judul.max' => 'Judul buku tidak boleh lebih dari :max karakter.',

            'pengarang.required' => 'Pengarang buku harus diisi.',
            'pengarang.string' => 'Pengarang buku harus berupa string.',
            'pengarang.max' => 'Pengarang buku tidak boleh lebih dari :max karakter.',

            'penerbit.required' => 'Penerbit buku harus diisi.',
            'penerbit.string' => 'Penerbit buku harus berupa string.',
            'penerbit.max' => 'Penerbit buku tidak boleh lebih dari :max karakter.',

            'tahun_terbit.required' => 'Tahun terbit buku harus diisi.',
            'tahun_terbit.numeric' => 'Tahun terbit buku harus berupa angka.',
            'tahun_terbit.digits' => 'Tahun terbit buku harus terdiri dari 4 digit.',

            'deskripsi.required' => 'Deskripsi buku harus diisi.',
            'deskripsi.string' => 'Deskripsi buku harus berupa string.',

            'foto.image' => 'File yang diunggah harus berupa gambar.',
            'foto.mimes' => 'File yang diunggah harus berupa jpeg, png, jpg, atau gif.',
            'foto.max' => 'Ukuran file tidak boleh lebih dari 3MB.',

            'total_buku.required' => 'Stok buku harus diisi.',
            'total_buku.integer' => 'Stok buku harus berupa angka.',
            'total_buku.min' => 'Stok buku tidak boleh kurang dari :min.',

            'kategori_id.required' => 'Kategori buku harus diisi minimal 1.',
            'kategori_id.min' => 'Kategori minimal 1 kategori harus dipilih.',

        ];

        // Ambil data buku terlebih dahulu
        $buku = BukuModel::findOrFail($id);

        // PENTING: Hitung jumlah buku yang sedang dipinjam dan diproses dari database
        // Ini lebih akurat daripada menghitung dari selisih total dan stok
        $bukuDipinjam = \App\Models\PeminjamanModel::where('buku_id', $id)
            ->whereIn('status', ['Dipinjam', 'Diproses', 'Terlambat'])
            ->count();

        // Tambahkan debug output untuk memverifikasi hasil perhitungan peminjaman aktif
        // dd("Buku dengan ID $id: Total Dipinjam: $bukuDipinjam");

        // Jika ada buku yang sedang dipinjam, kita perlu memastikan total buku tidak kurang dari itu
        if ($bukuDipinjam > 0) {
            // Update pesan error untuk validasi total_buku
            $messages['total_buku.min'] = "Total buku minimal harus minimal $bukuDipinjam. Karena saat ini ada $bukuDipinjam buku sedang dipinjam.";
        }

        // Validasi input dengan tambahan validasi untuk total_buku
        // total_buku minimal harus sama dengan jumlah buku yang sedang dipinjam
        $request->validate([
            'kode_buku' => 'required|max:22|unique:buku,kode_buku,' . $id,
            'judul' => 'required|string|max:60',
            'pengarang' => 'required|string|max:50',
            'penerbit' => 'required|string|max:50',
            'tahun_terbit' => 'required|numeric|digits:4',
            'deskripsi' => 'required|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3048',
            'total_buku' => 'required|integer|min:' . $bukuDipinjam, // Kunci perbaikan ada di sini
            'kategori_id' => 'required|min:1',
        ], $messages);

        // Ambil data admin yang sedang login dan update id_admin pada tabel buku
        $adminModel = AdminModel::where('user_id', Auth::id())->first();

        $buku->kode_buku = $request->kode_buku;
        $buku->judul = $request->judul;
        $buku->pengarang = $request->pengarang;
        $buku->penerbit = $request->penerbit;
        $buku->tahun_terbit = $request->tahun_terbit;
        $buku->deskripsi = $request->deskripsi;

        // PERBAIKAN: Dapatkan total buku baru dari request dan pastikan dikonversi ke integer
        // untuk menghindari masalah tipe data
        $newTotal = (int)$request->total_buku;

        // Verifikasi bahwa nilai total valid - total tidak boleh kurang dari jumlah buku yang dipinjam
        if ($newTotal < $bukuDipinjam) {
            $newTotal = $bukuDipinjam;
        }

        // Update total_buku dengan nilai input yang baru
        $buku->total_buku = $newTotal;

        // Update id_admin berdasarkan admin yang sedang melakukan edit
        if ($adminModel) {
            $buku->id_admin = $adminModel->id;
        }

        // RUMUS UTAMA: stok_buku = total_buku - bukuDipinjam
        // Hitung stok buku yang tersedia (total buku dikurangi jumlah yang sedang dipinjam)
        $buku->stok_buku = max(0, $newTotal - $bukuDipinjam);

        // Buku sudah disiapkan dengan total_buku dan stok_buku yang benar

        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($buku->foto && file_exists(public_path('assets/img/buku/' . $buku->foto))) {
                // Hapus foto lama
                unlink(public_path('assets/img/buku/' . $buku->foto));
            }
            // Upload foto baru
            $foto = $request->file('foto');
            $nama_file = $buku->id . '_' . $foto->getClientOriginalName(); // Menggunakan ID buku untuk menghindari duplikasi
            $foto->move(public_path('assets/img/buku/'), $nama_file);
            $buku->foto = $nama_file;
        }

        // Final check: pastikan stok dihitung dengan benar
        $buku->stok_buku = max(0, $buku->total_buku - $bukuDipinjam);

        // Set status berdasarkan stok, jika <= 0, set status "Habis" jika >0, set status "Tersedia"
        if ($buku->stok_buku <= 0) {
            $buku->status = 'Habis';
        } else {
            $buku->status = 'Tersedia';
        }

        // Simpan perubahan
        $buku->save();

        // Tambahkan baris ini untuk update kategori
        if ($request->has('kategori_id')) {
            $buku->kategori()->sync($request->kategori_id);
        }

        // Cek apakah ada referensi ke halaman kategori
        if ($request->has('ref') && $request->ref == 'kategori' && $request->has('kategori_id')) {
            // Referensi untuk kembali ke halaman kategori
            $page = $request->input('page');
            $search = $request->input('search');
            $kategori_id = $request->input('kategori_id');
            // Handle if kategori_id agar dibaca berupa array
            if (is_array($kategori_id)) {
                $kategori_id = reset($kategori_id); // Ambil kategori pertama dari array
            }

            // Pesan sukses dengan detail stok yang lebih jelas
            $successMessage = 'Buku berhasil diperbarui. Total buku: ' . $buku->total_buku .
                ', Stok tersedia: ' . $buku->stok_buku .
                ($bukuDipinjam > 0 ? ', Dipinjam: ' . $bukuDipinjam : '') .
                ' (Input total buku: ' . $request->total_buku . ')';

            return redirect()->route('kategori.detail', [
                'id' => $kategori_id,
                'page' => $page ?? '',
                'search' => $search ?? '',
            ])->with('success', $successMessage);
        } else {

            // Referensi untuk kembali ke page sebelumnya - menggunakan parameter yang sama dengan tombol "Kembali"
            $page = $request->input('page');
            $search = $request->input('search');
            $kategoriFilter = $request->input('kategori');
            $status = $request->input('status');

            // Pesan sukses dengan detail stok yang lebih jelas
            $successMessage = 'Buku berhasil diperbarui. Total buku: ' . $buku->total_buku .
                ', Stok tersedia: ' . $buku->stok_buku .
                ($bukuDipinjam > 0 ? ', Dipinjam: ' . $bukuDipinjam : '') .
                ' (Input total buku: ' . $request->total_buku . ')';

            // Gunakan parameter yang sama persis dengan tombol "Kembali" di view
            return redirect()->route('buku.index', [
                'page' => $page ?? '',
                'search' => $search ?? '',
                'kategori' => $kategoriFilter ?? '',
                'status' => $status ?? ''
            ])->with('success', $successMessage);
        }
    }

    // Menghapus data buku
    public function hapus($id)
    {
        $buku = BukuModel::findOrFail($id);
        // Hapus foto jika ada
        if ($buku->foto && file_exists(public_path('assets/img/buku/' . $buku->foto))) {
            unlink(public_path('assets/img/buku/' . $buku->foto));
        }
        $buku->delete();
        return redirect()->route('buku.index')->with('success', 'Buku berhasil dihapus.');
    }

    // Menggunakan library simple-qrcode dan extension imagick (untuk membaca qr code)
    // Download QR Code
    public function downloadQrCode($id)
    {
        $buku = BukuModel::findOrFail($id);
        $filename = 'qrcode-' . $buku->kode_buku . '.png';

        // Path ke logo yang akan ditempatkan di tengah QR code
        $logoPath = public_path('assets/img/logo_mts.png');

        // Generate QR code dalam format png dengan logo di tengah
        $qrcode = QrCode::format('png')
            ->size(300)
            ->margin(1)
            ->errorCorrection('H') // Error correction level tinggi untuk memastikan QR code masih bisa terbaca meski ada logo
            ->merge($logoPath, 0.3, true) // Menambahkan logo dengan ukuran 30% dari QR code
            ->generate(route('peminjaman.form', $buku->id));

        // Return file sebagai download
        return response($qrcode)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    // Logika  untuk mengurangi stok buku saat buku dipinjam
    public function pinjamBuku($id)
    {
        $buku = BukuModel::findOrFail($id);

        if ($buku->stok_buku > 0) {
            // Kurangi stok buku
            $buku->stok_buku -= 1;

            // Set status berdasarkan stok, jika <= 0, set status "Habis" jika >=0, set status "Tersedia"
            if ($buku->stok_buku <= 0) {
                $buku->status = 'Habis';
            } else {
                $buku->status = 'Tersedia';
            }

            $buku->save();

            return redirect()->route('buku.index')->with('success', 'Buku berhasil dipinjam.');
        }
        return redirect()->route('buku.index')->with('error', 'Stok buku habis.');
    }

    // Method untuk mengambil semua buku untuk ekspor
    public function getAllBooksForExport(Request $request)
    {
        // Ambil parameter filter dan pencarian
        $kategoriId = $request->get('kategori');
        $status = $request->get('status');
        $search = $request->get('search');

        // Base query untuk ngeloading data buku dengan relasinya
        $query = BukuModel::with('kategori');

        // Terapkan filter kategori jika ada
        if ($kategoriId) {
            $query->whereHas('kategori', function ($q) use ($kategoriId) {
                $q->where('kategori.id', $kategoriId);
            });
        }

        // Terapkan filter status jika ada
        if ($status) {
            $query->where('status', $status);
        }

        // Terapkan pencarian judul jika ada
        if ($search) {
            $query->where('judul', 'like', '%' . $search . '%');
        }

        // Ambil semua buku yang sesuai dengan filter (tanpa pagination)
        $buku = $query->get();

        // Format data untuk respons
        $formattedBooks = $buku->map(function ($book) {
            return [
                'kode_buku' => $book->kode_buku,
                'judul' => $book->judul,
                'pengarang' => $book->pengarang,
                'penerbit' => $book->penerbit,
                'tahun_terbit' => $book->tahun_terbit,
                'kategori' => $book->kategori->pluck('nama_kategori')->implode(', '),
                'total_buku' => $book->total_buku,
                'stok_buku' => $book->stok_buku,
                'status' => $book->status,
                'deskripsi' => $book->deskripsi,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedBooks
        ]);
    }
}
