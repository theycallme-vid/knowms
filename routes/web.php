<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UtamaController;
//ini adalah perubahan dari daniel
// ini adalah homepage yang mengarah pada controller
Route::get('/', [UtamaController::class, 'boleh']);

// Route::get('/', function () {
//     return view('utama');
// });

Route::get('/horeee-saya-bisa', function(){
    return 'Ini adalah halaman saya...hore';
});

//ini adalah komentar dari daniel
Route::get('/bisa-aja-bikin-lagi', function(){
    return 'ini loh tak bikin lagi';
});

//haloo
//tes