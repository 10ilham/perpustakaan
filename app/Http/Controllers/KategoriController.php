<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KategoriModel;

class KategoriController extends Controller
{
    public function index()
    {
        $kategori = KategoriModel::all();
        return view('kategori.index', compact('kategori'));
    }

    public function detail($id)
    {
        // Ambil detail kategori dan buku yang terkait
        $kategori = KategoriModel::with('buku')->findOrFail($id);
        return view('kategori.detail', compact('kategori'));
    }

    public function tambah()
    {
        // Tampilkan form tambah kategori
        return view('kategori.tambah');
    }

    public function simpan(Request $request)
    {
        $messages = [
            'nama.required' => 'Nama kategori harus diisi',
            'nama.string' => 'Nama kategori harus berupa string',
            'nama.max' => 'Nama kategori tidak boleh lebih dari 255 karakter',
            'nama.unique' => 'Nama kategori sudah ada',

            'deskripsi.string' => 'Deskripsi kategori harus berupa string',
        ];

        // Validasi input
        $request->validate([
            'nama' => 'required|string|max:255|unique:kategori,nama',
            'deskripsi' => 'nullable|string',
        ], $messages);

        // Simpan kategori baru
        KategoriModel::create([
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
        ]);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil ditambahkan');
    }

    public function edit($id)
    {
        // Ambil data kategori berdasarkan ID
        $kategori = KategoriModel::findOrFail($id);
        return view('kategori.edit', compact('kategori'));
    }

    public function update(Request $request, $id)
    {
        $messages = [
            'nama.required' => 'Nama kategori harus diisi',
            'nama.string' => 'Nama kategori harus berupa string',
            'nama.max' => 'Nama kategori tidak boleh lebih dari 255 karakter',
            'nama.unique' => 'Nama kategori sudah ada',

            'deskripsi.string' => 'Deskripsi kategori harus berupa string',
        ];

        // Validasi input
        $request->validate([
            'nama' => 'required|string|max:255|unique:kategori,nama,' . $id,
            'deskripsi' => 'nullable|string',
        ], $messages);

        // Update kategori
        $kategori = KategoriModel::findOrFail($id);
        $kategori->update([
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
        ]);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diperbarui');
    }

    public function hapus($id)
    {
        // Hapus kategori berdasarkan ID
        $kategori = KategoriModel::findOrFail($id);

        // Cek apakah kategori masih digunakan oleh buku
        if ($kategori->buku->count() > 0) {
            return redirect()->route('kategori.index')->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh buku.');
        }

        $kategori->delete();

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dihapus');
    }
}
