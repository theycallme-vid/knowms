<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\dataentry\FaqController as DataEntryFaqController;
use App\Http\Controllers\superadmin\PenggunaAksesController;
use App\Http\Controllers\validator\ValidatorFaqController;
use Illuminate\Support\Facades\Route;


// HALAMAN PUBLIK: FAQ Mahasiswa
Route::get('/faq', [PortalController::class, 'faq'])->name('mahasiswa.faq');

// HALAMAN PORTAL MAHASISWA & PROSES SIMPAN / AKHIRI SESSION FAKULTAS
Route::get('/', [PortalController::class, 'index'])->name('portal');
Route::get('/portal', [PortalController::class, 'index']);
Route::post('/portal/session', [PortalController::class, 'storeSession'])->name('portal.session');
Route::post('/portal/end-session', [PortalController::class, 'endSession'])->name('portal.end-session');
Route::get('/portal/end-session', [PortalController::class, 'endSession']);

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
});

// PENGGUNA & HAK AKSES (Dapat diakses Super Admin & Validator sementara disamakan)
Route::middleware(['auth', 'role:Super Admin,Validator'])->group(function () {
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
    Route::get('/dashboard', [ValidatorFaqController::class, 'dashboard'])->name('dashboard');
    Route::get('/daftar-faq', [ValidatorFaqController::class, 'index'])->name('daftarFAQ');

    // Halaman Validasi FAQ (Antrean Validasi) + Aksi Approve, Reject & Massal Approve
    Route::get('/validasi-faq', [ValidatorFaqController::class, 'validasiFaq'])->name('validasiFAQ');
    Route::post('/validasi-faq/bulk-approve', [ValidatorFaqController::class, 'bulkApprove'])->name('validasiFAQ.bulkApprove');
    Route::post('/validasi-faq/{id}/approve', [ValidatorFaqController::class, 'approve'])->name('validasiFAQ.approve');
    Route::post('/validasi-faq/{id}/reject', [ValidatorFaqController::class, 'reject'])->name('validasiFAQ.reject');

    // Alias route untuk kompatibilitas
    Route::post('/faq/{id}/approve', [ValidatorFaqController::class, 'approve'])->name('faq.approve');
    Route::post('/faq/{id}/reject', [ValidatorFaqController::class, 'reject'])->name('faq.reject');

    // Halaman Pengguna & Akses untuk Validator (disamakan sementara dengan Super Admin)
    Route::get('/pengguna-akses', [PenggunaAksesController::class, 'index'])->name('pengguna-akses');
});

// DATA ENTRY ROUTES
Route::middleware(['auth', 'role:Data Entry'])->group(function () {
    Route::prefix('data-entry')->name('dataentry.')->group(function () {
        // 1. Menu Daftar FAQ (Dashboard)
        Route::get('/dashboard', [DataEntryFaqController::class, 'dashboard'])->name('dashboard');

        // 2. Menu Buat FAQ (Input Satuan & Pratinjau Import File)
        Route::get('/buat-faq', [DataEntryFaqController::class, 'create'])->name('buatfaq');
        Route::post('/buat-faq', [DataEntryFaqController::class, 'store'])->name('buatfaq.store');
        Route::get('/pratinjau-unggah', [DataEntryFaqController::class, 'pratinjau'])->name('pratinjau');
        Route::post('/upload-excel', [DataEntryFaqController::class, 'uploadExcel'])->name('uploadExcel');
        Route::post('/pratinjau-unggah/update-item', [DataEntryFaqController::class, 'updatePreviewItem'])->name('pratinjau.updateItem');
        Route::post('/pratinjau-unggah/delete-item', [DataEntryFaqController::class, 'deletePreviewItem'])->name('pratinjau.deleteItem');
        Route::post('/pratinjau-unggah/submit', [DataEntryFaqController::class, 'submitPratinjau'])->name('pratinjau.submit');
        Route::get('/template/faq', [DataEntryFaqController::class, 'downloadTemplateFaq'])->name('template.faq');

        // Detail, Edit, Update, Delete FAQ
        Route::get('/faq/check/duplicate', [DataEntryFaqController::class, 'checkDuplicate'])->name('faq.checkDuplicate');
        Route::get('/faq/{id}', [DataEntryFaqController::class, 'show'])->name('faq.show');
        Route::get('/faq/{id}/edit', [DataEntryFaqController::class, 'edit'])->name('faq.edit');
        Route::match(['put', 'patch', 'post'], '/faq/{id}/update', [DataEntryFaqController::class, 'update'])->name('faq.update');
        Route::delete('/faq/{id}', [DataEntryFaqController::class, 'destroy'])->name('faq.destroy');

        // 3. Menu Daftar Kata Kunci (ttag)
        Route::get('/kata-kunci', [DataEntryFaqController::class, 'kataKunciIndex'])->name('katakunci');
        Route::post('/kata-kunci', [DataEntryFaqController::class, 'kataKunciStore'])->name('katakunci.store');
        Route::post('/kata-kunci/{id}/update', [DataEntryFaqController::class, 'kataKunciUpdate'])->name('katakunci.update');
        Route::delete('/kata-kunci/{id}', [DataEntryFaqController::class, 'kataKunciDestroy'])->name('katakunci.destroy');
        Route::post('/kata-kunci/upload', [DataEntryFaqController::class, 'kataKunciUpload'])->name('katakunci.upload');
        Route::post('/kata-kunci/save-import', [DataEntryFaqController::class, 'kataKunciSaveImport'])->name('katakunci.saveImport');
        Route::post('/kata-kunci/cancel-import', [DataEntryFaqController::class, 'kataKunciCancelImport'])->name('katakunci.cancelImport');
        Route::get('/template/kata-kunci', [DataEntryFaqController::class, 'downloadTemplateKataKunci'])->name('template.katakunci');

        // 4. Menu Sumber Pustaka (tdaftarpustaka)
        Route::get('/sumber-pustaka', [DataEntryFaqController::class, 'sumberPustakaIndex'])->name('sumberpustaka');
        Route::post('/sumber-pustaka', [DataEntryFaqController::class, 'sumberPustakaStore'])->name('sumberpustaka.store');
        Route::delete('/sumber-pustaka/{id}', [DataEntryFaqController::class, 'sumberPustakaDestroy'])->name('sumberpustaka.destroy');
        Route::post('/sumber-pustaka/upload', [DataEntryFaqController::class, 'sumberPustakaUpload'])->name('sumberpustaka.upload');
        Route::post('/sumber-pustaka/save-import', [DataEntryFaqController::class, 'sumberPustakaSaveImport'])->name('sumberpustaka.saveImport');
        Route::post('/sumber-pustaka/cancel-import', [DataEntryFaqController::class, 'sumberPustakaCancelImport'])->name('sumberpustaka.cancelImport');
        Route::get('/template/sumber-pustaka', [DataEntryFaqController::class, 'downloadTemplatePustaka'])->name('template.pustaka');
        Route::get('/pustaka/{id}/download', [DataEntryFaqController::class, 'downloadPustaka'])->name('pustaka.download');
    });

    Route::get('/dataentry/dashboard', [DataEntryFaqController::class, 'dashboard']);
});
