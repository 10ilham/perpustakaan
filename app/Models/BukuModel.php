<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BukuModel extends Model
{
    use HasFactory;

    // Nama tabel yang digunakan
    protected $table = 'buku';

    // Atribut yang dapat diisi
    protected $fillable = [
        'kode_buku',
        'judul',
        'pengarang',
        'penerbit',
        'tahun_terbit',
        'deskripsi',
        'foto',
        'stok_buku',
        'total_buku',
        'status',
        'kategori_id'
    ];

    // Relasi ke tabel kategori
    public function kategori()
    {
        return $this->belongsToMany(KategoriModel::class, 'kategori_buku', 'buku_id', 'kategori_id');
    }

    // Relasi ke tabel peminjaman
    // public function peminjaman()
    // {
    //     return $this->hasMany(PeminjamanModel::class, 'buku_id');
    // }
}
