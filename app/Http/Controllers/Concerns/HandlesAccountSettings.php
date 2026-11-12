<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Logika bersama halaman Pengaturan Akun.
 * Dipakai oleh PengaturanAkunController pada folder tiap role
 * (dataentry, validator, superadmin) agar perilaku tetap seragam.
 */
trait HandlesAccountSettings
{
    /** Nama view blade milik role, mis. 'dataentry.pengaturanakun' */
    abstract protected function settingsView(): string;

    /** Nama route halaman pengaturan akun milik role, mis. 'dataentry.pengaturanAkun' */
    abstract protected function settingsRoute(): string;

    public function index()
    {
        $user = Auth::user()->load(['role', 'direktorat']);

        return view($this->settingsView(), [
            'user' => $user,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validateWithBag('profile', [
            'nama'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', Rule::unique('tusers', 'email')->ignore($user->id)],
        ], [
            'nama.required'  => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.unique'   => 'Email sudah digunakan oleh akun lain.',
        ]);

        $user->update([
            'nama'  => trim($validated['nama']),
            'email' => strtolower(trim($validated['email'])),
        ]);

        return redirect()->route($this->settingsRoute())
            ->with('success', 'Profil akun berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validateWithBag('password', [
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'confirmed', 'different:current_password', Password::min(8)->letters()->numbers()],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'password.required'         => 'Kata sandi baru wajib diisi.',
            'password.confirmed'        => 'Konfirmasi kata sandi baru tidak sama.',
            'password.different'        => 'Kata sandi baru harus berbeda dari kata sandi saat ini.',
            'password.min'              => 'Kata sandi baru minimal 8 karakter.',
        ]);

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return redirect()->route($this->settingsRoute(), ['tab' => 'keamanan'])
                ->withErrors(['current_password' => 'Kata sandi saat ini tidak sesuai.'], 'password');
        }

        $user->password = $request->input('password'); // otomatis di-hash (cast 'hashed')
        $user->save();

        return redirect()->route($this->settingsRoute(), ['tab' => 'keamanan'])
            ->with('success', 'Kata sandi berhasil diperbarui.');
    }
}

