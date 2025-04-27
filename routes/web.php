<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KirimEmailController;
use App\Http\Controllers\LoginController;
use App\Http\Middleware\LevelMiddleware;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriController;

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
    Route::get('/admin/dashboard', [AdminController::class, 'showAdminData'])->name('admin.dashboard');
    Route::get('/siswa/dashboard', [SiswaController::class, 'showSiswaData'])->name('siswa.dashboard');
    Route::get('/guru/dashboard', [GuruController::class, 'showGuruData'])->name('guru.dashboard');
    Route::get('/staff/dashboard', [StaffController::class, 'showStaffData'])->name('staff.dashboard');

    // Route untuk anggota (halaman admin)
    Route::get('/anggota', [AnggotaController::class, 'index'])->name('anggota.index');
    Route::get('/anggota/detail/{id}', [AnggotaController::class, 'detail'])->name('anggota.detail');
    Route::get('/anggota/tambah', [AnggotaController::class, 'tambah'])->name('anggota.tambah');
    Route::post('/anggota/simpan', [AnggotaController::class, 'simpan'])->name('anggota.simpan');
    Route::get('/anggota/edit/{id}', [AnggotaController::class, 'edit'])->name('anggota.edit');
    Route::post('/anggota/update/{id}', [AnggotaController::class, 'update'])->name('anggota.update');
    Route::post('/anggota/hapus/{id}', [AnggotaController::class, 'hapus'])->name('anggota.hapus');

    // Route untuk buku
    Route::get('/buku', [BukuController::class, 'index'])->name('buku.index');
    Route::get('/buku/detail/{id}', [BukuController::class, 'detail'])->name('buku.detail');
    Route::get('/buku/tambah', [BukuController::class, 'tambah'])->name('buku.tambah');
    Route::post('/buku/simpan', [BukuController::class, 'simpan'])->name('buku.simpan');
    Route::get('/buku/edit/{id}', [BukuController::class, 'edit'])->name('buku.edit');
    Route::post('/buku/update/{id}', [BukuController::class, 'update'])->name('buku.update');
    Route::post('/buku/hapus/{id}', [BukuController::class, 'hapus'])->name('buku.hapus');
    Route::post('/buku/pinjam/{id}', [BukuController::class, 'pinjamBuku'])->name('buku.pinjam');

    // Route untuk kategori
    Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
    Route::get('/kategori/detail/{id}', [KategoriController::class, 'detail'])->name('kategori.detail');
    Route::get('/kategori/tambah', [KategoriController::class, 'tambah'])->name('kategori.tambah');
    Route::post('/kategori/simpan', [KategoriController::class, 'simpan'])->name('kategori.simpan');
    Route::get('/kategori/edit/{id}', [KategoriController::class, 'edit'])->name('kategori.edit');
    Route::post('/kategori/update/{id}', [KategoriController::class, 'update'])->name('kategori.update');
    Route::post('/kategori/hapus/{id}', [KategoriController::class, 'hapus'])->name('kategori.hapus');

    // Route untuk profile
    Route::get('/admin/profile', [AdminController::class, 'showProfile'])->name('admin.profile');
    Route::get('/siswa/profile', [SiswaController::class, 'showProfile'])->name('siswa.profile');
    Route::get('/guru/profile', [GuruController::class, 'showProfile'])->name('guru.profile');
    Route::get('/staff/profile', [StaffController::class, 'showProfile'])->name('staff.profile');

    // Route untuk edit profile
    Route::get('/admin/profile/edit', [AdminController::class, 'editProfile'])->name('admin.profile.edit');
    Route::post('/admin/profile/update', [AdminController::class, 'updateProfile'])->name('admin.profile.update');
    Route::get('/siswa/profile/edit', [SiswaController::class, 'editProfile'])->name('siswa.profile.edit');
    Route::post('/siswa/profile/update', [SiswaController::class, 'updateProfile'])->name('siswa.profile.update');
    Route::get('/guru/profile/edit', [GuruController::class, 'editProfile'])->name('guru.profile.edit');
    Route::post('/guru/profile/update', [GuruController::class, 'updateProfile'])->name('guru.profile.update');
    Route::get('/staff/profile/edit', [StaffController::class, 'editProfile'])->name('staff.profile.edit');
    Route::post('/staff/profile/update', [StaffController::class, 'updateProfile'])->name('staff.profile.update');
});
