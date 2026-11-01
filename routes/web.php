<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\superadmin\PenggunaAksesController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


// HALAMAN PUBLIK: FAQ Mahasiswa (halaman pertama). Login hanya dibuka lewat "/login"
Route::get('/', function () {
    return view('mahasiswa.faq');
})->name('mahasiswa.faq');

// GUEST ROUTES (Autentikasi)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

// LOGOUT (Hanya untuk yang sudah login)
Route::middleware('auth')->post('/logout', [AuthController::class, 'logout'])->name('logout');

// SUPER ADMIN ROUTES
Route::middleware(['auth', 'role:Super Admin'])->group(function () {
    // Dashboard Super Admin
    Route::get('/admin/dashboard', function () {
        return view('superadmin.dashboard');
    })->name('admin.dashboard');

    Route::get('/superadmin/dashboard', function () {
        return view('superadmin.dashboard');
    })->name('superadmin.dashboard');

    // Pengguna & Hak Akses
    Route::get('/superadmin/pengguna-akses', [PenggunaAksesController::class, 'index'])->name('superadmin.pengguna-akses');
    Route::get('/superadmin/pengguna&akses', [PenggunaAksesController::class, 'index']);
    Route::post('/superadmin/pengguna-akses/store', [PenggunaAksesController::class, 'store'])->name('superadmin.pengguna.store');
    Route::post('/superadmin/pengguna-akses/update/{id}', [PenggunaAksesController::class, 'update'])->name('superadmin.pengguna.update');
    Route::post('/superadmin/pengguna-akses/role/{id}', [PenggunaAksesController::class, 'updateRole'])->name('superadmin.pengguna.updateRole');
    Route::post('/superadmin/pengguna-akses/remove-role/{id}', [PenggunaAksesController::class, 'removeRole'])->name('superadmin.pengguna.removeRole');
    Route::post('/superadmin/pengguna-akses/toggle-status/{id}', [PenggunaAksesController::class, 'toggleStatus'])->name('superadmin.pengguna.toggleStatus');
});

// VALIDATOR ROUTES
Route::middleware(['auth', 'role:Validator'])->prefix('validator')->name('validator.')->group(function () {
    Route::get('/dashboard', function () {
        return view('validator.dashboard');
    })->name('dashboard');
});

// DATA ENTRY ROUTES
Route::middleware(['auth', 'role:Data Entry'])->group(function () {
    Route::prefix('data-entry')->name('dataentry.')->group(function () {
        Route::get('/dashboard', function () {
            return view('dataentry.dashboard');
        })->name('dashboard');

        Route::get('/buat-faq', function () {
            return view('dataentry.buatfaq');
        })->name('buatfaq');
    });

    Route::get('/dataentry/dashboard', function () {
        return view('dataentry.dashboard');
    });
});