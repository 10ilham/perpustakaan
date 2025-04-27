<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BukuModel;
use App\Models\KategoriModel;

class BukuController extends Controller
{
    //Menampilkan data buku
    public function index(Request $request)
    {
        // Filter berdasarkan kategori jika ada
        $kategoriId = $request->get('kategori');
        $status = $request->get('status');
        $search = $request->get('search');

        $query = BukuModel::with('kategori');

        // Filter berdasarkan kategori
        if ($kategoriId) {
            // Gunakan whereHas untuk filter many-to-many relationship
            $query->whereHas('kategori', function ($q) use ($kategoriId) {
                $q->where('kategori.id', $kategoriId);
            });
        }

        // Filter berdasarkan status
        if ($status) {
            $query->where('status', $status);
        }

        // Pencarian berdasarkan judul
        if ($search) {
            $query->where('judul', 'like', '%' . $search . '%');
        }

        // Ambil data buku
        $buku = $query->get();

        // Salin query untuk menghitung total buku, supaya tidak tertimpa pagnation (jika tidak diclone, maka total buku akan muncul 0 saat berpindah halaman)
        $totalBukuQuery = clone $query;

        // Pagination data buku 8 item perhalaman
        $buku = $query->paginate(8)->appends($request->query());

        // Ambil semua kategori
        $kategori = KategoriModel::all();

        // Hitung jumlah total buku berdasarkan filter
        $totalBuku = $totalBukuQuery->count();

        // Hitung jumlah buku tersedia berdasarkan filter
        $tersedia = (clone $query)
            ->where('status', 'Tersedia')
            ->when($status, function ($query) use ($status) {
                if ($status !== 'Tersedia') {
                    $query->whereRaw('1 = 0'); // Pastikan hasilnya 0 jika status bukan "Tersedia"
                }
            })
            ->count();

        // Hitung jumlah buku habis berdasarkan filter
        $habis = (clone $query)
            ->where('status', 'Habis')
            ->when($status, function ($query) use ($status) {
                if ($status !== 'Habis') {
                    $query->whereRaw('1 = 0'); // Pastikan hasilnya 0 jika status bukan "Habis"
                }
            })
            ->count();

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
            'kode_buku.unique' => 'Kode buku sudah ada.',

            'judul.required' => 'Judul buku harus diisi.',
            'judul.string' => 'Judul buku harus berupa string.',
            'judul.max' => 'Judul buku tidak boleh lebih dari 50 karakter.',

            'pengarang.required' => 'Pengarang buku harus diisi.',
            'pengarang.string' => 'Pengarang buku harus berupa string.',
            'pengarang.max' => 'Pengarang buku tidak boleh lebih dari 30 karakter.',

            'penerbit.required' => 'Penerbit buku harus diisi.',
            'penerbit.string' => 'Penerbit buku harus berupa string.',
            'penerbit.max' => 'Penerbit buku tidak boleh lebih dari 30 karakter.',

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
            'kode_buku' => 'required|unique:buku,kode_buku',
            'judul' => 'required|string|max:50',
            'pengarang' => 'required|string|max:30',
            'penerbit' => 'required|string|max:30',
            'tahun_terbit' => 'required|numeric|digits:4',
            'deskripsi' => 'required|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3048',
            'total_buku' => 'required|integer|min:0',
            'kategori_id' => 'required|min:1',
        ], $messages);

        $buku = new BukuModel();
        $buku->kode_buku = $request->kode_buku;
        $buku->judul = $request->judul;
        $buku->pengarang = $request->pengarang;
        $buku->penerbit = $request->penerbit;
        $buku->tahun_terbit = $request->tahun_terbit;
        $buku->deskripsi = $request->deskripsi;
        $buku->total_buku = $request->total_buku;
        $buku->stok_buku = $request->total_buku; // Stok awal sama dengan total buku

        // Set status berdasarkan stok, jika <= 0, set status "Habis" jika >=0, set status "Tersedia"
        if ($buku->stok_buku <= 0) {
            $buku->status = 'Habis';
        } else {
            $buku->status = 'Tersedia';
        }

        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $nama_file = time() . '_' . $foto->getClientOriginalName();
            $foto->move(public_path('assets/img/buku/'), $nama_file);
            $buku->foto = $nama_file;
        }

        $buku->save();

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

        return view('buku.detail', compact('buku', 'totalStokBuku'));
    }

    // Menampilkan form edit buku
    public function edit($id)
    {
        $buku = BukuModel::findOrFail($id);
        $kategori = KategoriModel::all();
        return view('buku.edit', compact('buku', 'kategori'));
    }

    // Mengupdate data buku
    public function update(Request $request, $id)
    {
        $messages = [
            'kode_buku.required' => 'Kode buku harus diisi.',
            'kode_buku.unique' => 'Kode buku sudah ada.',

            'judul.required' => 'Judul buku harus diisi.',
            'judul.string' => 'Judul buku harus berupa string.',
            'judul.max' => 'Judul buku tidak boleh lebih dari 50 karakter.',

            'pengarang.required' => 'Pengarang buku harus diisi.',
            'pengarang.string' => 'Pengarang buku harus berupa string.',
            'pengarang.max' => 'Pengarang buku tidak boleh lebih dari 30 karakter.',

            'penerbit.required' => 'Penerbit buku harus diisi.',
            'penerbit.string' => 'Penerbit buku harus berupa string.',
            'penerbit.max' => 'Penerbit buku tidak boleh lebih dari 30 karakter.',

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
            'kode_buku' => 'required|unique:buku,kode_buku,' . $id,
            'judul' => 'required|string|max:50',
            'pengarang' => 'required|string|max:30',
            'penerbit' => 'required|string|max:30',
            'tahun_terbit' => 'required|numeric|digits:4',
            'deskripsi' => 'required|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3048',
            'total_buku' => 'required|integer|min:0',
            'kategori_id' => 'required|min:1',
        ], $messages);

        $buku = BukuModel::findOrFail($id);
        $oldStok = $buku->stok_buku; // Simpan stok lama
        $oldTotal = $buku->total_buku ?? $oldStok; // Simpan total lama (jika ada)

        // Hitung selisih antara stok saat ini dengan total (untuk melihat berapa buku yang sedang dipinjam)
        $bukuDipinjam = $oldTotal - $oldStok;

        $buku->kode_buku = $request->kode_buku;
        $buku->judul = $request->judul;
        $buku->pengarang = $request->pengarang;
        $buku->penerbit = $request->penerbit;
        $buku->tahun_terbit = $request->tahun_terbit;
        $buku->deskripsi = $request->deskripsi;
        $buku->total_buku = $request->total_buku;

        // Perbarui stok berdasarkan total buku baru dikurangi buku yang sedang dipinjam
        $newStok = $request->total_buku - $bukuDipinjam;
        $buku->stok_buku = max(0, $newStok); // Pastikan stok tidak negatif
        // Pastikan stok tidak melebihi total buku
        $buku->stok_buku = min($buku->stok_buku, $request->total_buku);

        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($buku->foto && file_exists(public_path('assets/img/buku/' . $buku->foto))) {
                // Hapus foto lama
                unlink(public_path('assets/img/buku/' . $buku->foto));
            }
            // Upload foto baru
            $foto = $request->file('foto');
            $nama_file = time() . '_' . $foto->getClientOriginalName();
            $foto->move(public_path('assets/img/buku/'), $nama_file);
            $buku->foto = $nama_file;
        }

        // Set status berdasarkan stok, jika <= 0, set status "Habis" jika >=0, set status "Tersedia"
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

        return redirect()->route('buku.index')->with('success', 'Buku berhasil diperbarui.');
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
}
