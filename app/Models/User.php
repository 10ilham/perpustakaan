<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'users'; // Tentukan nama tabel secara eksplisit
    // Jika nama primary key berbeda dari default (id), tentukan di sini
    // protected $primaryKey = 'id';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'level',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relasi ke tabel admin
    public function admin()
    {
        return $this->hasOne(AdminModel::class, 'user_id');
    }

    // Relasi ke tabel siswa
    public function siswa()
    {
        return $this->hasOne(SiswaModel::class, 'user_id');
    }

    // Relasi ke tabel guru
    public function guru()
    {
        return $this->hasOne(GuruModel::class, 'user_id');
    }

    // Relasi ke tabel staff
    public function staff()
    {
        return $this->hasOne(StaffModel::class, 'user_id');
    }
}
