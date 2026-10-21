<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    // FORM LOGIN
    public function showLoginForm()
    {
        //gunannya ketika user sudah login, tak perlu login lagi
        if (Auth::check()) {
            return $this->redirectToDashboard();
        }
        return view('auth.login');
    }

    // LOGIN
    public function login(Request $request)
    {
        // Langkah 1: Validasi input dari form
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // Langkah 2: Autentikasi menggunakan Auth::attempt()
        // Auth::attempt() akan otomatis mencocokkan email dan password (hash)
        $rememberMe = $request->boolean('remember');

        if (!Auth::attempt($credentials, $rememberMe)) {
            // Jika gagal, kembali ke halaman login dengan pesan error
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email atau password yang Anda masukkan salah.']);
        }

        // Langkah 3: Cek apakah akun masih aktif
        if (!Auth::user()->is_active) {
            // Logout paksa karena akun nonaktif
            Auth::logout();
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Akun Anda telah dinonaktifkan. Hubungi Admin.']);
        }

        // Langkah 4: Regenerasi session untuk keamanan (mencegah session fixation attack)
        $request->session()->regenerate();

        // Langkah 5: Redirect ke dashboard sesuai role
        return $this->redirectToDashboard();
    }

    
    // LOGOUT
    public function logout(Request $request)
    {
        Auth::logout();

        // Hapus data session dan buat token CSRF baru
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda berhasil keluar.');
    }

    private function redirectToDashboard()
    {
        $roleName = Auth::user()->getRoleName();

        return match ($roleName) {
            'Super Admin' => redirect()->intended(route('admin.dashboard')),
            'Validator'   => redirect()->intended(route('validator.dashboard')),
            'Data Entry'  => redirect()->intended(route('dataentry.dashboard')),
            'CS'          => redirect()->intended(route('cs.dashboard')),
            default       => redirect('/'),
        };
    }
}
