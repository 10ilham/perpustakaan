<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SiswaModel;

class SiswaController extends Controller
{
    public function showSiswaData()
    {
        // Mendapatkan data siswa dari user
        $user = User::find(1);
        $siswa = $user->siswa;

        // Mendapatkan data user dari siswa
        $siswa = SiswaModel::find(1);
        $user = $siswa->user;

        // Kirim data ke view
        return view('siswa.show', compact('user', 'siswa'));
    }
}
