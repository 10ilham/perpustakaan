<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminModel extends Model
{
    use HasFactory;

    // Nama tabel yang digunakan
    protected $table = 'admin';

    protected $fillable = [
        'user_id',
        'nip',
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
