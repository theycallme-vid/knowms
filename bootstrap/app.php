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
        // Arahkan user yang sudah login jika mengakses halaman guest (seperti /login)
        $middleware->redirectUsersTo(function (Request $request) {
            $user = $request->user();
            return match ($user?->getRoleName()) {
                'Super Admin' => route('admin.dashboard'),
                'Validator'   => route('validator.dashboard'),
                'Data Entry'  => route('dataentry.dashboard'),
                default       => route('login'),
            };
        });

        // Daftarkan RoleMiddleware dengan alias 'role'
        // Penggunaan di route: middleware('role:Admin') atau middleware('role:Validator,Data Entry')
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
