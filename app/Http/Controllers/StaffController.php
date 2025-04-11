<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\StaffModel;

class StaffController extends Controller
{
    public function showStaffData()
    {
        // Mendapatkan data staff dari user
        $user = User::find(1);
        $staff = $user->staff;

        // Mendapatkan data user dari staff
        $staff = StaffModel::find(1);
        $user = $staff->user;

        // Kirim data ke view
        return view('staff.show', compact('user', 'staff'));
    }
}
