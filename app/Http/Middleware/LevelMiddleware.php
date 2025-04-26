<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class LevelMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Cek apakah pengguna memiliki level yang valid
            if (!in_array($user->level, ['admin', 'siswa', 'guru', 'staff'])) {
                return redirect('/')->with('error', 'Akses tidak diizinkan.');
            }
            // Cek apakah pengguna memiliki akses ke halaman yang diminta
            if ($request->is('admin/*') && $user->level !== 'admin') {
                return redirect('/')->with('error', 'Akses tidak diizinkan.');
            }
            if ($request->is('siswa/*') && $user->level !== 'siswa') {
                return redirect('/')->with('error', 'Akses tidak diizinkan.');
            }
            if ($request->is('guru/*') && $user->level !== 'guru') {
                return redirect('/')->with('error', 'Akses tidak diizinkan.');
            }
            if ($request->is('staff/*') && $user->level !== 'staff') {
                return redirect('/')->with('error', 'Akses tidak diizinkan.');
            }
            // Jika pengguna memiliki akses yang sesuai, lanjutkan ke permintaan berikutnya
            return $next($request);
        }
        return redirect('/')->with('error', 'Akses tidak diizinkan.');
    }
}
