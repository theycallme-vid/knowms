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
                $table->longText('keyword');
                $table->tinyInteger('hasil');
                $table->dateTime('waktu_pencarian');

                // FK ke sesi pencarian (nullable: bisa tanpa sesi)
                $table->integer('tSession_id')->nullable();
                $table->foreign('tSession_id', 'fk_tLogMahasiswa_tSession1')
                      ->references('id')
                      ->on('tsession');

                // FK ke FAQ yang ditemukan (nullable: bisa tidak ada hasil)
                $table->string('tFaqs_id', 20)->nullable();
                $table->foreign('tFaqs_id', 'fk_tLogMahasiswa_tFaqs1')
                      ->references('id')
                      ->on('tfaqs');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tlogmahasiswa');
    }
};
