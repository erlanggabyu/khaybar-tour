<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Show registration form.
     */
    public function register()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    /**
     * Handle registration request.
     */
    public function registerPost(Request $request)
    {
        // 1. Backend Input Validation
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'no_hp' => 'required|string|max:20',
        ], [
            'email.unique' => 'Email ini sudah terdaftar di sistem.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        try {
            // 2. Transaction for atomic user and profile insertion
            $user = DB::transaction(function () use ($validated) {
                // Create User (Role: pelanggan)
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'no_hp' => $validated['no_hp'],
                    'role' => 'pelanggan',
                ]);

                Pelanggan::create([
                    'user_id' => $user->id,
                ]);

                return $user;
            });

            // 3. Log important security activity
            ActivityLog::create([
                'user_id' => $user->id,
                'activity' => 'REGISTER',
                'description' => 'Registrasi akun baru berhasil: '.$user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // 4. Log in the user
            Auth::login($user);
            $request->session()->regenerate(); // Protect against session fixation

            return redirect()->route('dashboard')->with('success', 'Registrasi berhasil! Akun Anda telah aktif.');

        } catch (\Exception $e) {
            // Hide technical error from users and write to laravel log
            Log::error('Registration Error: '.$e->getMessage());

            return back()->withInput()->with('error', 'Terjadi kesalahan sistem saat registrasi. Silakan coba beberapa saat lagi.');
        }
    }

    /**
     * Show login form.
     */
    public function login()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function loginPost(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            // Regenerate session for session fixation protection
            $request->session()->regenerate();

            // Record security log
            ActivityLog::log('LOGIN_SUCCESS', 'User berhasil masuk sistem: '.Auth::user()->email);

            return redirect()->route('dashboard');
        }

        // Record failed login attempt (useful to check brute force)
        ActivityLog::create([
            'user_id' => null,
            'activity' => 'LOGIN_FAILED',
            'description' => 'Gagal login menggunakan email: '.$request->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->withErrors([
            'email' => 'Kredensial yang diberikan tidak cocok dengan catatan kami.',
        ])->onlyInput('email');
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        if (Auth::check()) {
            ActivityLog::log('LOGOUT', 'User keluar sistem: '.Auth::user()->email);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken(); // Regenerate CSRF token

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar sistem.');
    }
}
