<?php

use App\Models\FaqVersion;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Command untuk memproses publikasi FAQ yang sudah tiba jadwalnya
 */
Artisan::command('faq:publish-scheduled', function () {
    $count = FaqVersion::processDueScheduledPublications();
    $this->info("Berhasil mengaktifkan {$count} FAQ yang telah tiba waktu publikasinya.");
})->purpose('Mengaktifkan FAQ yang telah tiba jadwal publikasinya');

/**
 * Jadwal otomatis Laravel Scheduler (dijalankan setiap menit)
 */
Schedule::call(function () {
    FaqVersion::processDueScheduledPublications();
})->everyMinute()->name('process-faq-scheduled-publications');
