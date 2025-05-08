<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BukuModel;
use App\Models\User;

class PeminjamanModel extends Model
{
    use HasFactory;

    protected $table = 'peminjaman';

    protected $fillable = [
        'user_id',
        'buku_id',
        'no_peminjaman',
        'tanggal_pinjam',
        'tanggal_kembali',
        'tanggal_pengembalian',
        'status',
        'catatan',
        'is_terlambat',
        'jumlah_hari_terlambat',
    ];

    // Relasi dengan user (peminjam)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi dengan buku
    public function buku()
    {
        return $this->belongsTo(BukuModel::class, 'buku_id');
    }
}
