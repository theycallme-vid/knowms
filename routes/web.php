<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\dataentry\FaqController as DataEntryFaqController;
use App\Http\Controllers\dataentry\PengaturanAkunController as DataEntryPengaturanAkunController;
use App\Http\Controllers\superadmin\PenggunaAksesController;
use App\Http\Controllers\superadmin\PengaturanAkunController as SuperAdminPengaturanAkunController;
use App\Http\Controllers\validator\PengaturanAkunController as ValidatorPengaturanAkunController;
use App\Http\Controllers\validator\ValidatorFaqController;
use Illuminate\Support\Facades\Route;


// SIMULATOR PROTOTYPE STATIS (index.php)
Route::get('/simulator', function () {
    return response()->file(base_path('index.php'));
})->name('simulator');

// HALAMAN / ENDPOINT: Eksekusi FaqSeederReady (Semua FAQ Langsung Terbit Tanpa Perlu ACC)
Route::get('/faq-seeder-ready', function () {
    \Illuminate\Support\Facades\Artisan::call('db:seed', [
        '--class' => 'Database\\Seeders\\FaqSeederReady',
        '--force' => true,
    ]);
    $totalFaq  = \App\Models\Faq::count();
    $activeFaq = \App\Models\Faq::whereHas('versions', fn ($q) => $q->whereIn('status', ['Terbit', 'terbit']))->count();

    return response("
        <div style='font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif; padding: 40px; max-width: 620px; margin: 40px auto; border: 1px solid #E2E8F0; border-radius: 16px; background: #FFFFFF; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);'>
            <div style='display:inline-block; padding: 6px 12px; background: #ECFDF5; color: #059669; font-size: 12px; font-weight: 700; border-radius: 9999px; margin-bottom: 12px;'>STATUS: BERHASIL DITERBITKAN</div>
            <h2 style='color: #171A1F; margin: 0 0 12px 0; font-size: 22px;'>FaqSeederReady Selesai Dijalankan!</h2>
            <p style='color: #64748B; font-size: 14px; line-height: 1.6; margin-bottom: 20px;'>Semua FAQ dari <strong>seluruh fakultas</strong> dan kategori umum telah dimasukkan ke database dengan status <strong>Active (Terbit Langsung)</strong> tanpa perlu menunggu approval validator.</p>
            
            <div style='background: #FAF9F5; border: 1px solid #EAEAE4; border-radius: 12px; padding: 16px; margin-bottom: 24px;'>
                <div style='display:flex; justify-content:space-between; margin-bottom: 8px; font-size: 14px; color: #334155;'>
                    <span>Total FAQ di Database:</span>
                    <strong style='color:#171A1F;'>{$totalFaq} FAQ</strong>
                </div>
                <div style='display:flex; justify-content:space-between; font-size: 14px; color: #059669;'>
                    <span>Total FAQ Terbit (Active):</span>
                    <strong>{$activeFaq} FAQ</strong>
                </div>
            </div>

            <div style='display: flex; gap: 10px;'>
                <a href='" . route('mahasiswa.faq') . "' style='padding: 10px 18px; background: #85172C; color: #FFFFFF; text-decoration: none; border-radius: 9999px; font-size: 13px; font-weight: 600;'>Lihat di FAQ Mahasiswa &rarr;</a>
                <a href='" . route('validator.daftarFAQ') . "' style='padding: 10px 18px; background: #FFFFFF; color: #171A1F; border: 1px solid #CBD5E1; text-decoration: none; border-radius: 9999px; font-size: 13px; font-weight: 600;'>Lihat di Daftar FAQ</a>
            </div>
        </div>
    ");
})->name('faq.seeder.ready');

// HALAMAN PUBLIK: FAQ Mahasiswa, Riwayat Versi Jawaban, Sumber Pustaka & Pencatatan Log Akses Rekomendasi
Route::get('/faq', [PortalController::class, 'faq'])->name('mahasiswa.faq');
Route::get('/faq/{id}/riwayat', [PortalController::class, 'faqHistory'])->name('mahasiswa.faq.riwayat');
Route::post('/faq/log-access', [PortalController::class, 'logFaqAccess'])->name('faq.log-access');
Route::get('/sumber-pustaka/{id}/lihat', [DataEntryFaqController::class, 'viewPustaka'])->name('pustaka.lihat');

// HALAMAN GUEST: FAQ untuk Tamu / Pengunjung tanpa input NRP & tanpa Scan KTM
Route::get('/guest', [GuestController::class, 'index'])->name('guest.faq');
Route::get('/guest/faq', [GuestController::class, 'index']);
Route::post('/guest/end-session', [GuestController::class, 'endSession'])->name('guest.end-session');
Route::get('/guest/end-session', [GuestController::class, 'endSession']);

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

// LOGOUT
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// SUPER ADMIN ROUTES
Route::middleware(['auth', 'role:Super Admin'])->group(function () {
    // Dashboard Super Admin
    Route::get('/admin/dashboard', [PenggunaAksesController::class, 'dashboard'])->name('admin.dashboard');

    Route::get('/superadmin/dashboard', [PenggunaAksesController::class, 'dashboard'])->name('superadmin.dashboard');

    // Pengaturan Akun Super Admin
    Route::get('/superadmin/pengaturan-akun', [SuperAdminPengaturanAkunController::class, 'index'])->name('superadmin.pengaturanAkun');
    Route::post('/superadmin/pengaturan-akun/profil', [SuperAdminPengaturanAkunController::class, 'updateProfile'])->name('superadmin.pengaturanAkun.profile');
    Route::post('/superadmin/pengaturan-akun/kata-sandi', [SuperAdminPengaturanAkunController::class, 'updatePassword'])->name('superadmin.pengaturanAkun.password');

    // Pengaturan Sistem (Bobot Algoritma Rekomendasi & Idle Auto-Logout Mahasiswa)
    Route::get('/superadmin/pengaturan-sistem', [PenggunaAksesController::class, 'pengaturanSistem'])->name('superadmin.pengaturanSistem');
    Route::get('/superadmin/pengaturan-sistem/poin', [PenggunaAksesController::class, 'pengaturanPoin'])->name('superadmin.pengaturanPoin');
    Route::post('/superadmin/pengaturan-sistem/update', [PenggunaAksesController::class, 'updatePengaturanSistem'])->name('superadmin.pengaturanSistem.update');
    Route::post('/superadmin/pengaturan-sistem/reset', [PenggunaAksesController::class, 'resetPengaturanSistem'])->name('superadmin.pengaturanSistem.reset');
});

// DAFTAR FAQ & AKSI VALIDATOR (Dapat diakses Validator & Super Admin)
Route::middleware(['auth', 'role:Super Admin,Validator'])->prefix('validator')->name('validator.')->group(function () {
    Route::get('/daftar-faq', [ValidatorFaqController::class, 'index'])->name('daftarFAQ');
    Route::get('/faq/{id}/detail', [ValidatorFaqController::class, 'show'])->name('faq.show');
    Route::post('/faq/{id}/status', [ValidatorFaqController::class, 'updateStatus'])->name('faq.updateStatus');
    Route::delete('/faq/{id}', [ValidatorFaqController::class, 'destroy'])->name('faq.destroy');
    Route::post('/faq/{id}/approve', [ValidatorFaqController::class, 'approve'])->name('faq.approve');
    Route::post('/faq/{id}/reject', [ValidatorFaqController::class, 'reject'])->name('faq.reject');
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
Route::middleware(['auth', 'role:Super Admin,Validator'])->prefix('validator')->name('validator.')->group(function () {
    Route::get('/dashboard', [ValidatorFaqController::class, 'dashboard'])->name('dashboard');

    // Halaman Validasi FAQ (Antrean Validasi) + Aksi Approve, Reject, Massal Approve & Publish
    Route::get('/validasi-faq', [ValidatorFaqController::class, 'validasiFaq'])->name('validasiFAQ');
    Route::post('/validasi-faq/bulk-approve', [ValidatorFaqController::class, 'bulkApprove'])->name('validasiFAQ.bulkApprove');
    Route::post('/validasi-faq/publish', [ValidatorFaqController::class, 'publish'])->name('validasiFAQ.publish');
    Route::post('/validasi-faq/{id}/approve', [ValidatorFaqController::class, 'approve'])->name('validasiFAQ.approve');
    Route::post('/validasi-faq/{id}/reject', [ValidatorFaqController::class, 'reject'])->name('validasiFAQ.reject');

    // Buat FAQ & Pratinjau Unggahan File untuk Validator (Langsung Berstatus Diterima & Siap Terbit)
    Route::get('/buat-faq', [ValidatorFaqController::class, 'create'])->name('buatfaq');
    Route::post('/buat-faq', [ValidatorFaqController::class, 'store'])->name('buatfaq.store');
    Route::get('/pratinjau-unggah', [ValidatorFaqController::class, 'pratinjau'])->name('pratinjau');
    Route::post('/upload-excel', [ValidatorFaqController::class, 'uploadExcel'])->name('uploadExcel');
    Route::post('/pratinjau-unggah/update-item', [ValidatorFaqController::class, 'updatePreviewItem'])->name('pratinjau.updateItem');
    Route::post('/pratinjau-unggah/delete-item', [ValidatorFaqController::class, 'deletePreviewItem'])->name('pratinjau.deleteItem');
    Route::post('/pratinjau-unggah/submit', [ValidatorFaqController::class, 'submitPratinjau'])->name('pratinjau.submit');
    Route::get('/template/faq', [DataEntryFaqController::class, 'downloadTemplateFaq'])->name('template.faq');
    Route::get('/faq/check/duplicate', [ValidatorFaqController::class, 'checkDuplicate'])->name('faq.checkDuplicate');
    Route::get('/faq/{id}/edit', [ValidatorFaqController::class, 'edit'])->name('faq.edit');
    Route::match(['put', 'patch', 'post'], '/faq/{id}/update', [ValidatorFaqController::class, 'update'])->name('faq.update');

    // Daftar Kata Kunci (ttag) untuk Validator
    Route::get('/kata-kunci', [ValidatorFaqController::class, 'kataKunciIndex'])->name('katakunci');
    Route::post('/kata-kunci', [ValidatorFaqController::class, 'kataKunciStore'])->name('katakunci.store');
    Route::post('/kata-kunci/{id}/update', [ValidatorFaqController::class, 'kataKunciUpdate'])->name('katakunci.update');
    Route::delete('/kata-kunci/{id}', [ValidatorFaqController::class, 'kataKunciDestroy'])->name('katakunci.destroy');
    Route::post('/kata-kunci/upload', [ValidatorFaqController::class, 'kataKunciUpload'])->name('katakunci.upload');
    Route::post('/kata-kunci/save-import', [ValidatorFaqController::class, 'kataKunciSaveImport'])->name('katakunci.saveImport');
    Route::post('/kata-kunci/cancel-import', [ValidatorFaqController::class, 'kataKunciCancelImport'])->name('katakunci.cancelImport');
    Route::get('/template/kata-kunci', [DataEntryFaqController::class, 'downloadTemplateKataKunci'])->name('template.katakunci');

    // Sumber Pustaka (tdaftarpustaka) untuk Validator
    Route::get('/sumber-pustaka', [ValidatorFaqController::class, 'sumberPustakaIndex'])->name('sumberpustaka');
    Route::post('/sumber-pustaka', [ValidatorFaqController::class, 'sumberPustakaStore'])->name('sumberpustaka.store');
    Route::post('/sumber-pustaka/{id}/update', [ValidatorFaqController::class, 'sumberPustakaUpdate'])->name('sumberpustaka.update');
    Route::delete('/sumber-pustaka/{id}', [ValidatorFaqController::class, 'sumberPustakaDestroy'])->name('sumberpustaka.destroy');
    Route::post('/sumber-pustaka/upload', [ValidatorFaqController::class, 'sumberPustakaUpload'])->name('sumberpustaka.upload');
    Route::post('/sumber-pustaka/save-import', [ValidatorFaqController::class, 'sumberPustakaSaveImport'])->name('sumberpustaka.saveImport');
    Route::post('/sumber-pustaka/cancel-import', [ValidatorFaqController::class, 'sumberPustakaCancelImport'])->name('sumberpustaka.cancelImport');
    Route::get('/template/sumber-pustaka', [DataEntryFaqController::class, 'downloadTemplatePustaka'])->name('template.pustaka');
    Route::get('/pustaka/{id}/view', [DataEntryFaqController::class, 'viewPustaka'])->name('pustaka.view');
    Route::get('/pustaka/{id}/download', [DataEntryFaqController::class, 'downloadPustaka'])->name('pustaka.download');

    // Halaman Pengguna & Akses untuk Validator (disamakan sementara dengan Super Admin)
    Route::get('/pengguna-akses', [PenggunaAksesController::class, 'index'])->name('pengguna-akses');

    // Pengaturan Akun Validator
    Route::get('/pengaturan-akun', [ValidatorPengaturanAkunController::class, 'index'])->name('pengaturanAkun');
    Route::post('/pengaturan-akun/profil', [ValidatorPengaturanAkunController::class, 'updateProfile'])->name('pengaturanAkun.profile');
    Route::post('/pengaturan-akun/kata-sandi', [ValidatorPengaturanAkunController::class, 'updatePassword'])->name('pengaturanAkun.password');
});

// DATA ENTRY ROUTES
Route::middleware(['auth', 'role:Data Entry'])->group(function () {
    Route::prefix('data-entry')->name('dataentry.')->group(function () {
        // 1. Menu Daftar FAQ
        Route::get('/daftar-faq', [DataEntryFaqController::class, 'daftarFAQ'])->name('daftarFAQ');
        Route::get('/dashboard', fn () => redirect()->route('dataentry.daftarFAQ'));

        // 1b. Menu Pengajuan FAQ (Antrean Pengajuan Data Entry + Checklist)
        Route::get('/pengajuan-faq', [DataEntryFaqController::class, 'pengajuanFaq'])->name('pengajuan');
        Route::post('/pengajuan-faq/bulk-submit', [DataEntryFaqController::class, 'bulkSubmitFaq'])->name('pengajuan.bulkSubmit');
        Route::post('/pengajuan-faq/{id}/submit', [DataEntryFaqController::class, 'submitSingleFaq'])->name('pengajuan.submit');

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
        Route::post('/sumber-pustaka/{id}/update', [DataEntryFaqController::class, 'sumberPustakaUpdate'])->name('sumberpustaka.update');
        Route::delete('/sumber-pustaka/{id}', [DataEntryFaqController::class, 'sumberPustakaDestroy'])->name('sumberpustaka.destroy');
        Route::post('/sumber-pustaka/upload', [DataEntryFaqController::class, 'sumberPustakaUpload'])->name('sumberpustaka.upload');
        Route::post('/sumber-pustaka/save-import', [DataEntryFaqController::class, 'sumberPustakaSaveImport'])->name('sumberpustaka.saveImport');
        Route::post('/sumber-pustaka/cancel-import', [DataEntryFaqController::class, 'sumberPustakaCancelImport'])->name('sumberpustaka.cancelImport');
        Route::get('/template/sumber-pustaka', [DataEntryFaqController::class, 'downloadTemplatePustaka'])->name('template.pustaka');
        Route::get('/pustaka/{id}/view', [DataEntryFaqController::class, 'viewPustaka'])->name('pustaka.view');
        Route::get('/pustaka/{id}/download', [DataEntryFaqController::class, 'downloadPustaka'])->name('pustaka.download');

        // 5. Pengaturan Akun Data Entry
        Route::get('/pengaturan-akun', [DataEntryPengaturanAkunController::class, 'index'])->name('pengaturanAkun');
        Route::post('/pengaturan-akun/profil', [DataEntryPengaturanAkunController::class, 'updateProfile'])->name('pengaturanAkun.profile');
        Route::post('/pengaturan-akun/kata-sandi', [DataEntryPengaturanAkunController::class, 'updatePassword'])->name('pengaturanAkun.password');
    });

    Route::get('/dataentry/daftar-faq', fn () => redirect()->route('dataentry.daftarFAQ'));
    Route::get('/dataentry/dashboard', fn () => redirect()->route('dataentry.daftarFAQ'));
});
