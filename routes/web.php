<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UtamaController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/coba', function () {
    return view('coba');
});

Route::get('/coba', [UtamaController::class, 'boleh']);
