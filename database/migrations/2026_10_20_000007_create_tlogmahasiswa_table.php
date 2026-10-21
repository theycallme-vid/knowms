<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tlogmahasiswa')) {
            Schema::create('tlogmahasiswa', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();

                // Kata kunci yang diketikkan saat mencari
                $table->longText('keyword');

                // 1 = ditemukan hasil, 0 = tidak ditemukan
                $table->tinyInteger('hasil');

                // Kapan pencarian terjadi (menggantikan peran created_at untuk log)
                $table->dateTime('waktu_pencarian');

                // FK ke mahasiswa (NULLABLE: bisa NULL jika yang mencari adalah Guest)
                $table->integer('tMahasiswas_id')->nullable();
                $table->foreign('tMahasiswas_id', 'fk_tLogMahasiswa_tMahasiswas1')
                      ->references('id')
                      ->on('tmahasiswas')
                      ->onDelete('no action')
                      ->onUpdate('no action');

                // FK ke FAQ yang ditemukan (NULLABLE: bisa NULL jika hasil pencarian kosong)
                $table->integer('tFaqs_id')->nullable();
                $table->foreign('tFaqs_id', 'fk_tLogMahasiswa_tFaqs1')
                      ->references('id')
                      ->on('tfaqs')
                      ->onDelete('no action')
                      ->onUpdate('no action');

                // Log tidak memerlukan updated_at karena data log tidak pernah diubah
                $table->timestamps(false);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tlogmahasiswa');
    }
};
