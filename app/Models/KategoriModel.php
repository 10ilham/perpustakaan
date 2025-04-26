<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriModel extends Model
{
    use HasFactory;

    // Nama tabel yang digunakan
    protected $table = 'kategori';

    // Atribut yang dapat diisi
    protected $fillable = [
        'nama',
        'deskripsi',
    ];

    // Relasi ke tabel buku
    public function buku()
    {
        return $this->hasMany(BukuModel::class, 'kategori_id');
    }
}
