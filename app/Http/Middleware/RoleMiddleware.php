<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Nama-nama role yang diizinkan, dipisah koma
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Ambil user yang sedang login
        $user = $request->user();

        // Pastikan user sudah login dan akun-nya aktif
        if (!$user || !$user->is_active) {
            abort(403, 'Akun Anda tidak aktif atau Anda belum login.');
        }

        // Cek apakah role user ada di dalam daftar role yang diizinkan
        // Contoh: $roles = ['Admin', 'Validator']
        if (!in_array($user->getRoleName(), $roles)) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman ini.');
        }

        // Lolos pengecekan, lanjutkan request
        return $next($request);
    }
}
