<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\BannedLoginSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Menampilkan form login
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Proses login
     */
    public function login(LoginRequest $request)
    {
        $credentials = $this->getCredentials($request);

        // ⭐ CEK STATUS USER SEBELUM LOGIN
        $user = User::where(array_key_first($credentials), $credentials[array_key_first($credentials)])->first();

        if ($user && $user->status === 'nonaktif') {
            return back()
                ->withErrors([
                    'login' => 'Akun Anda telah dinonaktifkan. Silakan hubungi admin untuk informasi lebih lanjut.',
                ])
                ->onlyInput('login');
        }

        // ⭐ CEK APAKAH IP/DEVICE USER SEDANG DIBLOKIR
        if ($user) {
            $ip = $request->ip();
            $ua = $request->userAgent() ?? '';
            $isBanned = BannedLoginSession::where('user_id', $user->id)
                ->where(function ($q) use ($ip, $ua) {
                    $q->where(function ($q2) use ($ip, $ua) {
                        $q2->where('ip_address', $ip)->where('user_agent', $ua);
                    })->orWhere(function ($q2) use ($ip) {
                        // banned tanpa UA = blokir semua device di IP itu
                        $q2->where('ip_address', $ip)->whereNull('user_agent');
                    })->orWhere(function ($q2) use ($ua) {
                        // banned tanpa IP = blokir device itu di semua IP (jarang)
                        $q2->whereNull('ip_address')->where('user_agent', $ua);
                    });
                })
                ->exists();

            if ($isBanned) {
                $banned = BannedLoginSession::where('user_id', $user->id)->latest('banned_at')->first();
                $reason = $banned?->reason ? " Alasan: {$banned->reason}" : '';

                return back()
                    ->withErrors([
                        'login' => 'Perangkat/IP Anda diblokir oleh admin.'.$reason.' Hubungi admin untuk membuka blokir.',
                    ])
                    ->onlyInput('login');
            }
        }

        // Proses login normal
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            $role = $user->role;

            // ==========================================
            // REDIRECT BERDASARKAN ROLE
            // ==========================================
            return match ($role) {
                'admin' => redirect()->route('admin.dashboard.index'),
                'pengelola_surat' => redirect()->route('admin.kelolasurat.index'),
                'pengelola_aula' => redirect()->route('admin.kelolabooking.index'),
                'pengelola_absensi' => redirect()->route('admin.absensi.index'),
                default => redirect()->route('booking.index'), // User biasa
            };
        }

        return back()->withErrors([
            'login' => 'Email/Username atau password salah.',
        ])->onlyInput('login');
    }

    /**
     * Get credentials dari input (email atau username)
     */
    protected function getCredentials(LoginRequest $request)
    {
        $login = $request->input('login');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        return [
            $field => $login,
            'password' => $request->input('password'),
        ];
    }

    /**
     * Proses logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda berhasil log out!');
    }
}