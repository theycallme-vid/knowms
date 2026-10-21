<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Root: redirect ke login jika belum login, atau dashboard jika sudah
Route::get('/', function () {
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
        return view('dashboard.dataentry');
    })->name('dashboard');
});

