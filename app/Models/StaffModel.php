<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StaffModel extends Model
{
    use HasFactory;

    // Nama tabel yang digunakan
    protected $table = 'staff';

    protected $fillable = [
        'user_id',
        'nip',
        'jabatan',
        'tanggal_lahir',
        'alamat',
        'no_telepon',
    ];

    // Relasi ke tabel users
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
