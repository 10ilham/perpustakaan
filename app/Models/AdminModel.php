<?php

namespace App\Models;

// Menggunakan traits dan kelas dari framework - Konsep OOP: Reusability (menggunakan kembali kode yang sudah ada di laravel)
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Kelas AdminModel - Representasi data admin perpustakaan
 * Konsep OOP: Inheritance (Pewarisan) - Kelas ini mewarisi kelas Model dari Laravel
 */
class AdminModel extends Model // Konsep OOP: Inheritance - mewarisi sifat dan metode dari kelas Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan dalam database
     * Konsep OOP: Encapsulation - Menggunakan modifier protected untuk membatasi akses
     */
    protected $table = 'admin'; // protected - hanya dapat diakses oleh kelas ini dan turunannya

    /**
     * Atribut
     * Konsep OOP: Encapsulation - Melindungi atribut dari modifikasi yang tidak diinginkan
     */
    protected $fillable = [
        'user_id',     // Kunci asing ke tabel users
        'nip',         // Nomor Induk Pegawai admin
        'tanggal_lahir', // Tanggal lahir admin
        'alamat',      // Alamat admin
        'no_telepon',  // Nomor telepon admin
        'foto',        // Path ke foto admin
    ];

    /**
     * Relasi ke tabel users - Menghubungkan AdminModel dengan User
     * Konsep OOP: Association (Asosiasi) - Menunjukkan hubungan antara kelas AdminModel dan User
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() // public - dapat diakses dari mana saja
    {
        // Implementasi relasi one-to-one (inverse) - Admin milik satu User (satu akun milik satu orang)
        return $this->belongsTo(User::class, 'user_id');
    }
}
