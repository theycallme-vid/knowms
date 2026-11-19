<?php

use App\Models\FaqVersion;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Perintah untuk memproses jadwal publikasi FAQ yang sudah tiba waktunya
 */
Artisan::command('faq:publish-scheduled', function () {
    $count = FaqVersion::processDueScheduledPublications();
    $this->info("Berhasil menerbitkan {$count} FAQ yang telah tiba jadwal publikasinya.");
})->purpose('Menerbitkan FAQ yang telah tiba jadwal publikasinya');

/**
 * Penjadwal otomatis Laravel (dijalankan setiap menit)
 */
Schedule::call(function () {
    FaqVersion::processDueScheduledPublications();
})->everyMinute()->name('process-faq-scheduled-publications');
