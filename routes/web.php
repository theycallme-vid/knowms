<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/superadmin/dashboard', function () {
    return view('superadmin.dashboard');
});

// Super Admin - Pengguna & Akses
use App\Http\Controllers\superadmin\PenggunaAksesController;

Route::get('/superadmin/pengguna-akses', [PenggunaAksesController::class, 'index'])->name('superadmin.pengguna-akses');
Route::get('/superadmin/pengguna&akses', [PenggunaAksesController::class, 'index']);
Route::post('/superadmin/pengguna-akses/store', [PenggunaAksesController::class, 'store'])->name('superadmin.pengguna.store');
Route::post('/superadmin/pengguna-akses/update/{id}', [PenggunaAksesController::class, 'update'])->name('superadmin.pengguna.update');
Route::post('/superadmin/pengguna-akses/role/{id}', [PenggunaAksesController::class, 'updateRole'])->name('superadmin.pengguna.updateRole');
Route::post('/superadmin/pengguna-akses/remove-role/{id}', [PenggunaAksesController::class, 'removeRole'])->name('superadmin.pengguna.removeRole');
Route::post('/superadmin/pengguna-akses/toggle-status/{id}', [PenggunaAksesController::class, 'toggleStatus'])->name('superadmin.pengguna.toggleStatus');

Route::get('/dataentry/dashboard', function () {
    return view('dataentry.dashboard');
});

Route::get('/validator/dashboard', function () {
    return view('validator.dashboard');
});

// Root: redirect ke login jika belum login, atau dashboard jika sudah
Route::get('/', function () {
    if (Auth::check()) {
        $roleName = Auth::user()->getRoleName();
        return match ($roleName) {
            'Super Admin' => redirect()->route('admin.dashboard'),
            'Validator'   => redirect()->route('validator.dashboard'),
            'Data Entry'  => redirect()->route('dataentry.dashboard'),
            default       => redirect()->route('login'),
        };
    }

    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    // --- Sign In ---
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

// =============================================================================
// LOGOUT (Hanya untuk yang SUDAH login)
Route::middleware('auth')->post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- Super Admin ---
Route::middleware(['auth', 'role:Super Admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard.admin');
    })->name('dashboard');
});

// --- Validator ---
Route::middleware(['auth', 'role:Validator'])->prefix('validator')->name('validator.')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard.validator');
    })->name('dashboard');
});

// --- Data Entry ---
Route::middleware(['auth', 'role:Data Entry'])->prefix('data-entry')->name('dataentry.')->group(function () {
    Route::get('/dashboard', function () {
        return view('dataentry.dashboard');
    })->name('dashboard');
});
