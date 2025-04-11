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

            // Redirect berdasarkan level
            if ($user->level === 'admin' && !$request->is('admin/dashboard')) {
                return redirect('/admin/dashboard');
            } elseif ($user->level === 'siswa' && !$request->is('siswa/dashboard')) {
                return redirect('/siswa/dashboard');
            } elseif ($user->level === 'guru' && !$request->is('guru/dashboard')) {
                return redirect('/guru/dashboard');
            } elseif ($user->level === 'staff' && !$request->is('staff/dashboard')) {
                return redirect('/staff/dashboard');
            }
        } else {
            // Jika tidak login, arahkan ke halaman utama
            return redirect('/')->with('error', 'Silakan login terlebih dahulu.');
        }

        return $next($request);
    }
}
