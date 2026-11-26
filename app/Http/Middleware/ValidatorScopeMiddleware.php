<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidatorScopeMiddleware
{
    /**
     * Memisahkan akses ruang kerja Validator Pusat (Admik) dan Validator Unit secara ketat.
     *
     * @param  string  $scope  'pusat' atau 'unit'
     */
    public function handle(Request $request, Closure $next, string $scope = 'pusat'): Response
    {
        $user = $request->user();
        if (!$user || !$user->is_active || !$user->isValidator()) {
            abort(403, 'Anda tidak memiliki hak akses Validator.');
        }

        $user->loadMissing('direktorat');

        if ($scope === 'pusat' && !$user->isPusat()) {
            if ($request->isMethod('GET') && !$request->expectsJson() && !$request->ajax()) {
                return redirect()->route('validator.unit.daftarFAQ');
            }
            abort(403, 'Akses ditolak: Halaman ini khusus untuk Validator Direktorat Akademik (Pusat).');
        }

        if ($scope === 'unit' && $user->isPusat()) {
            if ($request->isMethod('GET') && !$request->expectsJson() && !$request->ajax()) {
                return redirect()->route('validator.daftarFAQ');
            }
            abort(403, 'Akses ditolak: Halaman ini khusus untuk Validator Unit / Fakultas.');
        }

        return $next($request);
    }
}
