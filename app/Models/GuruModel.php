<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GuruModel extends Model
{
    use HasFactory;

    // Nama tabel yang digunakan
    protected $table = 'guru';

    protected $fillable = [
        'user_id',
        'nip',
        'mata_pelajaran',
        'tanggal_lahir',
        'alamat',
        'no_telepon',
        'foto',
    ];

    // Relasi ke tabel users
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
