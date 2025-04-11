<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\GuruModel;

class GuruController extends Controller
{
    public function showGuruData()
    {
        // Mendapatkan data guru dari user
        $user = User::find(1);
        $guru = $user->guru;

        // Mendapatkan data user dari guru
        $guru = GuruModel::find(1);
        $user = $guru->user;

        // Kirim data ke view
        return view('guru.show', compact('user', 'guru'));
    }
}
