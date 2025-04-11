<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KirimEmailController;
use App\Http\Controllers\LoginController;
use App\Http\Middleware\LevelMiddleware;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('layouts.index');
});

Route::get('formemail', [KirimEmailController::class, 'index']);
Route::post('kirim', [KirimEmailController::class, 'kirim']);

// Route untuk login
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// Route untuk logout
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/')->with('success', 'Anda telah berhasil logout.');
})->name('logout');

// Route untuk dashboard user, memanggil middleware LevelMiddleware
Route::middleware(['auth', LevelMiddleware::class])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    });

    Route::get('/siswa/dashboard', function () {
        return view('siswa.dashboard');
    });

    Route::get('/guru/dashboard', function () {
        return view('guru.dashboard');
    });

    Route::get('/staff/dashboard', function () {
        return view('staff.dashboard');
    });
});
