<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AdminModel;

class AdminController extends Controller
{
    public function showAdminData()
    {
        // Mendapatkan data admin dari user
        $user = User::find(1);
        $admin = $user->admin;

        // Mendapatkan data user dari admin
        $admin = AdminModel::find(1);
        $user = $admin->user;

        // Kirim data ke view
        return view('admin.dashboard', compact('users', 'admin'));
    }
}
