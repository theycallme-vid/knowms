<?php

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