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
            $query->where('kategori_id', $kategoriId);
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

        // Hitung jumlah buku dipinjam berdasarkan filter
        $dipinjam = (clone $query)
            ->where('status', 'Dipinjam')
            ->when($status, function ($query) use ($status) {
                if ($status !== 'Dipinjam') {
                    $query->whereRaw('1 = 0'); // Pastikan hasilnya 0 jika status bukan "Dipinjam"
                }
            })
            ->count();

        return view('buku.index', compact('buku', 'kategori', 'totalBuku', 'tersedia', 'dipinjam'));
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

            'kategori_id.required' => 'Kategori buku harus dipilih.',
            'kategori_id.exists' => 'Kategori buku tidak valid.',
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
            'kategori_id' => 'required|exists:kategori,id'
        ], $messages);

        $buku = new BukuModel();
        $buku->kode_buku = $request->kode_buku;
        $buku->judul = $request->judul;
        $buku->pengarang = $request->pengarang;
        $buku->penerbit = $request->penerbit;
        $buku->tahun_terbit = $request->tahun_terbit;
        $buku->deskripsi = $request->deskripsi;

        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $nama_file = time() . '_' . $foto->getClientOriginalName();
            $foto->move(public_path('assets/img/buku/'), $nama_file);
            $buku->foto = $nama_file;
        }

        // Set status default
        $buku->status = 'Tersedia';
        $buku->kategori_id = $request->kategori_id;
        $buku->save();

        return redirect()->route('buku.index')->with('success', 'Buku berhasil ditambahkan.');
    }

    // Menampilkan detail buku
    public function detail($id)
    {
        $buku = BukuModel::with('kategori')->findOrFail($id);
        return view('buku.detail', compact('buku'));
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

            'status.required' => 'Status buku harus dipilih.',
            'status.in' => 'Status buku tidak valid.',

            'kategori_id.required' => 'Kategori buku harus dipilih.',
            'kategori_id.exists' => 'Kategori buku sudah ada.',

            // Validasi untuk foto
            // Jika foto baru diunggah, maka validasi
            // Jika tidak ada foto baru, maka lewati validasi
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
            'status' => 'required|in:Tersedia,Dipinjam',
            'kategori_id' => 'required|exists:kategori,id'
        ], $messages);

        $buku = BukuModel::findOrFail($id);
        $buku->kode_buku = $request->kode_buku;
        $buku->judul = $request->judul;
        $buku->pengarang = $request->pengarang;
        $buku->penerbit = $request->penerbit;
        $buku->tahun_terbit = $request->tahun_terbit;
        $buku->deskripsi = $request->deskripsi;
        $buku->kategori_id = $request->kategori_id;
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

        // Update status jika ada perubahan
        // Jika status tidak diubah, maka tetap gunakan status lama
        if ($request->has('status')) {
            $buku->status = $request->status;
        } else {

            $buku->status = $buku->getOriginal('status');
        }
        $buku->save();
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
}
