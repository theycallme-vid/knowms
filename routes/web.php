<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\LayananAkademikController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\dataentry\FaqController as DataEntryFaqController;
use App\Http\Controllers\dataentry\PengaturanAkunController as DataEntryPengaturanAkunController;
use App\Http\Controllers\superadmin\PenggunaAksesController;
use App\Http\Controllers\superadmin\PengaturanAkunController as SuperAdminPengaturanAkunController;
use App\Http\Controllers\validator\PengaturanAkunController as ValidatorPengaturanAkunController;
use App\Http\Controllers\validator\ValidatorFaqController;
use Illuminate\Support\Facades\Route;


// ROUTE PENGEMBANGAN LOKAL (Hanya aktif di APP_ENV=local & khusus Super Admin)
if (app()->environment('local')) {
    // SIMULATOR PROTOTYPE STATIS (index.php)
    Route::middleware(['auth', 'role:Super Admin'])->get('/simulator', function () {
        return response()->file(base_path('index.php'));
    })->name('simulator');

    // HALAMAN / ENDPOINT: Eksekusi FaqSeederReady (Semua FAQ Langsung Terbit Tanpa Perlu ACC)
    Route::middleware(['auth', 'role:Super Admin'])->get('/faq-seeder-ready', function () {
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
                <a href='" . route('superadmin.daftarFAQ') . "' style='padding: 10px 18px; background: #FFFFFF; color: #171A1F; border: 1px solid #CBD5E1; text-decoration: none; border-radius: 9999px; font-size: 13px; font-weight: 600;'>Lihat di Daftar FAQ</a>
            </div>
        </div>
    ");
    })->name('faq.seeder.ready');
}

// HALAMAN PUBLIK: FAQ Mahasiswa, Riwayat Versi Jawaban, Sumber Pustaka & Pencatatan Log Akses Rekomendasi
Route::get('/faq', [PortalController::class, 'faq'])->name('mahasiswa.faq');
Route::get('/faq/search', [PortalController::class, 'searchPublishedFaq'])->middleware('throttle:120,1')->name('faq.search');
Route::get('/faq/{id}/riwayat', [PortalController::class, 'faqHistory'])->name('mahasiswa.faq.riwayat');
Route::post('/faq/log-access', [PortalController::class, 'logFaqAccess'])->name('faq.log-access');
Route::get('/sumber-pustaka/{id}/lihat', [DataEntryFaqController::class, 'viewPustaka'])->name('pustaka.lihat');

// HALAMAN LAYANAN AKADEMIK: KONTAK LANGSUNG (PANGGILAN SUARA AUDIO ONLY & HOTLINE WHATSAPP QR)
Route::get('/layanan-akademik/kontak', [LayananAkademikController::class, 'kontak'])->name('layanan.kontak');
Route::post('/layanan-akademik/api/start-audio', [LayananAkademikController::class, 'startAudioCallApi'])->name('layanan.audiocall.api.start');

// LAYANAN AKADEMIK: VIDEO/AUDIO CALL JITSI MEET & API
Route::get('/layanan-akademik/video-call/start', [LayananAkademikController::class, 'startVideoCall'])->name('layanan.videocall.create');
Route::get('/layanan-akademik/video-call/{roomId}', [LayananAkademikController::class, 'videoCallRoom'])->name('layanan.videocall.room');
Route::post('/layanan-akademik/video-call/join', [LayananAkademikController::class, 'joinRoom'])->name('layanan.videocall.join');
Route::get('/layanan-akademik/api/active-calls', [LayananAkademikController::class, 'getActiveCalls'])->middleware('throttle:60,1')->name('layanan.videocall.api.active');
Route::post('/layanan-akademik/api/end/{roomId}', [LayananAkademikController::class, 'endCall'])->middleware('throttle:30,1')->name('layanan.videocall.api.end');

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
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');
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

    // Daftar FAQ Super Admin (Read-Only Monitoring)
    Route::get('/superadmin/daftar-faq', [ValidatorFaqController::class, 'index'])->name('superadmin.daftarFAQ');
    Route::get('/superadmin/faq/{id}/detail', [ValidatorFaqController::class, 'show'])->name('superadmin.faq.show');

    // Kelola Unit / Direktorat Super Admin
    Route::get('/superadmin/kelola-unit', [PenggunaAksesController::class, 'unitIndex'])->name('superadmin.kelolaUnit');
    Route::get('/superadmin/unit', [PenggunaAksesController::class, 'unitIndex']);
    Route::post('/superadmin/kelola-unit/store', [PenggunaAksesController::class, 'unitStore'])->name('superadmin.kelolaUnit.store');
    Route::post('/superadmin/kelola-unit/update/{id}', [PenggunaAksesController::class, 'unitUpdate'])->name('superadmin.kelolaUnit.update');
    Route::post('/superadmin/kelola-unit/toggle-status/{id}', [PenggunaAksesController::class, 'unitToggleStatus'])->name('superadmin.kelolaUnit.toggleStatus');

    // Pengguna & Hak Akses Super Admin
    Route::get('/superadmin/pengguna-akses', [PenggunaAksesController::class, 'index'])->name('superadmin.pengguna-akses');
    Route::get('/superadmin/pengguna&akses', [PenggunaAksesController::class, 'index']);
    Route::post('/superadmin/pengguna-akses/store', [PenggunaAksesController::class, 'store'])->name('superadmin.pengguna.store');
    Route::post('/superadmin/pengguna-akses/update/{id}', [PenggunaAksesController::class, 'update'])->name('superadmin.pengguna.update');
    Route::post('/superadmin/pengguna-akses/role/{id}', [PenggunaAksesController::class, 'updateRole'])->name('superadmin.pengguna.updateRole');
    Route::post('/superadmin/pengguna-akses/remove-role/{id}', [PenggunaAksesController::class, 'removeRole'])->name('superadmin.pengguna.removeRole');
    Route::post('/superadmin/pengguna-akses/toggle-status/{id}', [PenggunaAksesController::class, 'toggleStatus'])->name('superadmin.pengguna.toggleStatus');
    Route::post('/superadmin/pengguna-akses/delete/{id}', [PenggunaAksesController::class, 'destroy'])->name('superadmin.pengguna.destroy');

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

// VALIDATOR PUSAT (DIREKTORAT AKADEMIK / ADMIK) ROUTES
Route::middleware(['auth', 'role:Validator', 'validator.scope:pusat'])->prefix('validator')->name('validator.')->group(function () {
    Route::get('/daftar-faq', [ValidatorFaqController::class, 'index'])->name('daftarFAQ');
    Route::get('/faq/{id}/detail', [ValidatorFaqController::class, 'show'])->name('faq.show');
    Route::post('/faq/{id}/status', [ValidatorFaqController::class, 'updateStatus'])->name('faq.updateStatus');
    Route::delete('/faq/{id}', [ValidatorFaqController::class, 'destroy'])->name('faq.destroy');
    Route::post('/faq/{id}/approve', [ValidatorFaqController::class, 'approve'])->name('faq.approve');
    Route::post('/faq/{id}/reject', [ValidatorFaqController::class, 'reject'])->name('faq.reject');

    Route::get('/dashboard', [ValidatorFaqController::class, 'dashboard'])->name('dashboard');

    // Halaman Validasi FAQ Tahap 2 (Admik) + Aksi Approve, Reject, Massal Approve & Publish
    Route::get('/validasi-faq', [ValidatorFaqController::class, 'validasiFaq'])->name('validasiFAQ');
    Route::post('/validasi-faq/bulk-approve', [ValidatorFaqController::class, 'bulkApprove'])->name('validasiFAQ.bulkApprove');
    Route::post('/validasi-faq/publish', [ValidatorFaqController::class, 'publish'])->name('validasiFAQ.publish');
    Route::post('/validasi-faq/{id}/approve', [ValidatorFaqController::class, 'approve'])->name('validasiFAQ.approve');
    Route::post('/validasi-faq/{id}/reject', [ValidatorFaqController::class, 'reject'])->name('validasiFAQ.reject');

    // Buat FAQ & Pratinjau Unggahan File untuk Validator Admik (Langsung Berstatus Diterima & Siap Terbit)
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

    // Daftar Kata Kunci (ttag)
    Route::get('/kata-kunci', [ValidatorFaqController::class, 'kataKunciIndex'])->name('katakunci');
    Route::post('/kata-kunci', [ValidatorFaqController::class, 'kataKunciStore'])->name('katakunci.store');
    Route::post('/kata-kunci/{id}/update', [ValidatorFaqController::class, 'kataKunciUpdate'])->name('katakunci.update');
    Route::delete('/kata-kunci/{id}', [ValidatorFaqController::class, 'kataKunciDestroy'])->name('katakunci.destroy');
    Route::post('/kata-kunci/upload', [ValidatorFaqController::class, 'kataKunciUpload'])->name('katakunci.upload');
    Route::post('/kata-kunci/save-import', [ValidatorFaqController::class, 'kataKunciSaveImport'])->name('katakunci.saveImport');
    Route::post('/kata-kunci/cancel-import', [ValidatorFaqController::class, 'kataKunciCancelImport'])->name('katakunci.cancelImport');
    Route::get('/template/kata-kunci', [DataEntryFaqController::class, 'downloadTemplateKataKunci'])->name('template.katakunci');

    // Sumber Pustaka (tdaftarpustaka)
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

    // Kelola Unit / Direktorat (tdirektorat) untuk Validator Admik
    Route::get('/kelola-unit', [PenggunaAksesController::class, 'unitIndex'])->name('kelolaUnit');
    Route::get('/unit', [PenggunaAksesController::class, 'unitIndex']);
    Route::post('/kelola-unit/store', [PenggunaAksesController::class, 'unitStore'])->name('kelolaUnit.store');
    Route::post('/kelola-unit/update/{id}', [PenggunaAksesController::class, 'unitUpdate'])->name('kelolaUnit.update');
    Route::post('/kelola-unit/toggle-status/{id}', [PenggunaAksesController::class, 'unitToggleStatus'])->name('kelolaUnit.toggleStatus');

    // Pengguna & Akses untuk Validator Admik (Kelola Validator Unit & Data Entry Semua Unit; Super Admin Terkunci)
    Route::get('/pengguna-akses', [PenggunaAksesController::class, 'index'])->name('pengguna-akses');
    Route::post('/pengguna-akses/store', [PenggunaAksesController::class, 'store'])->name('pengguna.store');
    Route::post('/pengguna-akses/update/{id}', [PenggunaAksesController::class, 'update'])->name('pengguna.update');
    Route::post('/pengguna-akses/role/{id}', [PenggunaAksesController::class, 'updateRole'])->name('pengguna.updateRole');
    Route::post('/pengguna-akses/remove-role/{id}', [PenggunaAksesController::class, 'removeRole'])->name('pengguna.removeRole');
    Route::post('/pengguna-akses/toggle-status/{id}', [PenggunaAksesController::class, 'toggleStatus'])->name('pengguna.toggleStatus');
    Route::post('/pengguna-akses/delete/{id}', [PenggunaAksesController::class, 'destroy'])->name('pengguna.destroy');

    // Pengaturan Unit Penerima Alert Call (Khusus Validator Admik)
    Route::get('/pengaturan-alert-call', [ValidatorFaqController::class, 'pengaturanAlertCall'])->name('pengaturanAlertCall');
    Route::post('/pengaturan-alert-call/update', [ValidatorFaqController::class, 'updatePengaturanAlertCall'])->name('pengaturanAlertCall.update');

    // Pengaturan Akun Validator Admik
    Route::get('/pengaturan-akun', [ValidatorPengaturanAkunController::class, 'index'])->name('pengaturanAkun');
    Route::post('/pengaturan-akun/profil', [ValidatorPengaturanAkunController::class, 'updateProfile'])->name('pengaturanAkun.profile');
    Route::post('/pengaturan-akun/kata-sandi', [ValidatorPengaturanAkunController::class, 'updatePassword'])->name('pengaturanAkun.password');
});

// VALIDATOR UNIT (DINAMIS UNTUK SELURUH FAKULTAS / UNIT NON-PUSAT) ROUTES
Route::middleware(['auth', 'role:Validator', 'validator.scope:unit'])->prefix('validator-unit')->name('validator.unit.')->group(function () {
    Route::get('/daftar-faq', [ValidatorFaqController::class, 'index'])->name('daftarFAQ');
    Route::get('/dashboard', [ValidatorFaqController::class, 'dashboard'])->name('dashboard');
    Route::get('/faq/{id}/detail', [ValidatorFaqController::class, 'show'])->name('faq.show');
    Route::delete('/faq/{id}', [ValidatorFaqController::class, 'destroy'])->name('faq.destroy');

    // Validasi Tahap 1 (Unit): Setujui & Teruskan ke Admik, atau Tolak ke Data Entry Unit (Tanpa Publish)
    Route::get('/validasi-faq', [ValidatorFaqController::class, 'validasiFaq'])->name('validasiFAQ');
    Route::post('/validasi-faq/bulk-approve', [ValidatorFaqController::class, 'bulkApprove'])->name('validasiFAQ.bulkApprove');
    Route::post('/validasi-faq/{id}/approve', [ValidatorFaqController::class, 'approve'])->name('validasiFAQ.approve');
    Route::post('/validasi-faq/{id}/reject', [ValidatorFaqController::class, 'reject'])->name('validasiFAQ.reject');

    // Buat FAQ & Pratinjau Unggahan untuk Validator Unit (Langsung Menunggu Validasi Admik)
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

    // Daftar Kata Kunci & Sumber Pustaka Bersama
    Route::get('/kata-kunci', [ValidatorFaqController::class, 'kataKunciIndex'])->name('katakunci');
    Route::post('/kata-kunci', [ValidatorFaqController::class, 'kataKunciStore'])->name('katakunci.store');
    Route::post('/kata-kunci/{id}/update', [ValidatorFaqController::class, 'kataKunciUpdate'])->name('katakunci.update');
    Route::delete('/kata-kunci/{id}', [ValidatorFaqController::class, 'kataKunciDestroy'])->name('katakunci.destroy');
    Route::post('/kata-kunci/upload', [ValidatorFaqController::class, 'kataKunciUpload'])->name('katakunci.upload');
    Route::post('/kata-kunci/save-import', [ValidatorFaqController::class, 'kataKunciSaveImport'])->name('katakunci.saveImport');
    Route::post('/kata-kunci/cancel-import', [ValidatorFaqController::class, 'kataKunciCancelImport'])->name('katakunci.cancelImport');
    Route::get('/template/kata-kunci', [DataEntryFaqController::class, 'downloadTemplateKataKunci'])->name('template.katakunci');

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

    // Pengguna & Akses Unit (Hanya Data Entry Unitnya Sendiri)
    Route::get('/pengguna-akses', [PenggunaAksesController::class, 'index'])->name('pengguna-akses');
    Route::post('/pengguna-akses/store', [PenggunaAksesController::class, 'store'])->name('pengguna.store');
    Route::post('/pengguna-akses/update/{id}', [PenggunaAksesController::class, 'update'])->name('pengguna.update');
    Route::post('/pengguna-akses/remove-role/{id}', [PenggunaAksesController::class, 'removeRole'])->name('pengguna.removeRole');
    Route::post('/pengguna-akses/toggle-status/{id}', [PenggunaAksesController::class, 'toggleStatus'])->name('pengguna.toggleStatus');
    Route::post('/pengguna-akses/delete/{id}', [PenggunaAksesController::class, 'destroy'])->name('pengguna.destroy');

    // Pengaturan Akun Validator Unit
    Route::get('/pengaturan-akun', [ValidatorPengaturanAkunController::class, 'index'])->name('pengaturanAkun');
    Route::post('/pengaturan-akun/profil', [ValidatorPengaturanAkunController::class, 'updateProfile'])->name('pengaturanAkun.profile');
    Route::post('/pengaturan-akun/kata-sandi', [ValidatorPengaturanAkunController::class, 'updatePassword'])->name('pengaturanAkun.password');
});

// DATA ENTRY ROUTES (Dinamis untuk Data Entry Admik maupun Data Entry Unit)
Route::middleware(['auth', 'role:Data Entry'])->group(function () {
    Route::prefix('data-entry')->name('dataentry.')->group(function () {
        // 1. Menu Daftar FAQ
        Route::get('/daftar-faq', [DataEntryFaqController::class, 'daftarFAQ'])->name('daftarFAQ');
        Route::get('/dashboard', fn () => redirect()->route('dataentry.daftarFAQ'))->name('dashboard');

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
