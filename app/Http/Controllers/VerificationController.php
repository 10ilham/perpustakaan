<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerificationMail;
use Illuminate\Support\Str;

class VerificationController extends Controller
{
    /**
     * Kirim email verifikasi ketika pengguna mengubah email.
     *
     * @param  \App\Models\User  $user
     * @param  string  $newEmail
     * @return \Illuminate\Http\Response
     */
    public function sendVerificationEmail(User $user, $newEmail)
    {
        // Generate token verifikasi
        $verificationToken = Str::random(64);

        // Simpan token verifikasi dan email baru
        $user->update([
            'email_verification_token' => $verificationToken . '|' . $newEmail,
        ]);

        // Kirim email verifikasi
        $verificationUrl = route('email.verify', ['token' => $verificationToken]);

        try {
            Mail::to($newEmail)->send(new EmailVerificationMail([
                'nama' => $user->nama, //dikirim ke view (verify_email.blade.php) untuk menampilkan nama pengguna di email
                'verificationUrl' => $verificationUrl,
            ]));

            // Logout pengguna
            Auth::logout();

            // Invalidasi session
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return redirect()->route('login')->with('info', 'Profile berhasil diperbarui. Silakan verifikasi email baru Anda melalui link yang telah dikirim ke ' . $newEmail);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengirim email verifikasi: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Verifikasi email pengguna.
     *
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */
    public function verify($token)
    {

        // Ambil pengguna berdasarkan token verifikasi
        // Menggunakan LIKE untuk menghindari masalah dengan token yang lebih panjang
        // Misalnya, jika token yang disimpan adalah "abc123|email@example.com"
        $user = User::where('email_verification_token', 'LIKE', $token . '%')->first();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Token verifikasi tidak valid atau sudah kadaluarsa!');
        }

        // Extract token parts (token|email)
        $tokenParts = explode('|', $user->email_verification_token);

        if (count($tokenParts) !== 2) {
            return redirect()->route('login')->with('error', 'Format token verifikasi tidak valid!');
        }

        $newEmail = $tokenParts[1];

        // Update user email dan status verifikasi
        $user->update([
            'email' => $newEmail,
            'email_verified_at' => now(),
            'email_verification_token' => $user->email_verification_token, // Mempertahankan token verifikasi yang ada didatabase (agar tidak hilang (null) setelah verifikasi)
        ]);

        return redirect()->route('login')->with('success', 'Email Anda berhasil diverifikasi! Silakan login kembali dengan email baru Anda.');
    }
}
