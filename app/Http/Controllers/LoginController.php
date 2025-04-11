<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        // Kembali ke halaman utama karena login menggunakan modal
        return redirect('/');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();

            // Redirect berdasarkan level pengguna
            $user = Auth::user();
            if ($user->level === 'admin') {
                return redirect('/admin/dashboard');
            } elseif ($user->level === 'siswa') {
                return redirect('/siswa/dashboard');
            } elseif ($user->level === 'guru') {
                return redirect('/guru/dashboard');
            } elseif ($user->level === 'staff') {
                return redirect('/staff/dashboard');
            }

            return redirect('/');

            // Versi simple
            // $user = Auth::user();
            // return match ($user->level) {
            //     'admin' => redirect('/admin/dashboard'),
            //     'siswa' => redirect('/siswa/dashboard'),
            //     'guru' => redirect('/guru/dashboard'),
            //     'staff' => redirect('/staff/dashboard'),
            //     default => redirect('/'),
            // };
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ]);
    }
}
