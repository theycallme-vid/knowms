<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Percayai semua proxy (sangat penting untuk ngrok / tunnel HTTPS agar asset & redirect tidak terblokir)
        $middleware->trustProxies(at: '*');

        // Arahkan user yang sudah login jika mengakses halaman guest (seperti /login)
        $middleware->redirectUsersTo(function (Request $request) {
            $user = $request->user();
            return match ($user?->getRoleName()) {
                'Super Admin' => route('admin.dashboard'),
                'Validator'   => $user->isValidatorUnit()
                    ? route('validator.unit.daftarFAQ')
                    : route('validator.daftarFAQ'),
                'Data Entry'  => route('dataentry.daftarFAQ'),
                default       => route('login'),
            };
        });

        // Daftarkan RoleMiddleware dan ValidatorScopeMiddleware
        $middleware->alias([
            'role'            => \App\Http\Middleware\RoleMiddleware::class,
            'validator.scope' => \App\Http\Middleware\ValidatorScopeMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
