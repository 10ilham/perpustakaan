<?php

namespace App\Models;

// Menggunakan traits dan kelas dari framework - Konsep OOP: Reusability (menggunakan kembali kode yang sudah ada di laravel)
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Kelas BukuModel - Representasi data buku di perpustakaan
 * Konsep OOP: Inheritance (Pewarisan) - Kelas ini mewarisi kelas Model dari Laravel
 */
class BukuModel extends Model // Konsep OOP: Inheritance - mewarisi sifat dan metode dari kelas Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan dalam database
     * Konsep OOP: Encapsulation - Menggunakan modifier protected untuk membatasi akses
     */
    protected $table = 'buku'; // protected - hanya dapat diakses oleh kelas ini dan turunannya

    /**
     * Atribut yang dapat diisi secara massal (mass assignment)
     * Konsep OOP: Encapsulation - Melindungi atribut dari modifikasi yang tidak diinginkan
     */
    protected $fillable = [
        'kode_buku',    // Kode unik untuk buku
        'judul',        // Judul buku
        'pengarang',    // Nama pengarang buku
        'penerbit',     // Nama penerbit buku
        'tahun_terbit', // Tahun buku diterbitkan
        'deskripsi',    // Deskripsi atau sinopsis buku
        'foto',         // Path ke foto cover buku
        'total_buku',   // Total jumlah buku yang dimiliki perpustakaan
        'stok_buku',    // Jumlah buku yang tersedia untuk dipinjam
        'status',       // Status ketersediaan buku
    ];

    /**
     * Relasi ke tabel kategori - Menghubungkan BukuModel dengan KategoriModel
     * Konsep OOP: Association (Asosiasi) - Menunjukkan hubungan many-to-many antara Buku dan Kategori
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function kategori() // public - dapat diakses dari mana saja
    {
        // Implementasi relasi many-to-many - Satu buku bisa memiliki banyak kategori,
        // dan satu kategori bisa dimiliki oleh banyak buku
        // Menggunakan tabel pivot (tabel perantara) 'kategori_buku' untuk menyimpan relasi antara buku dan kategori
        return $this->belongsToMany(KategoriModel::class, 'kategori_buku', 'buku_id', 'kategori_id');
    }
}
