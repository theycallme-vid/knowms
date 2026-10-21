<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tformpertanyaancs')) {
            Schema::create('tformpertanyaancs', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();

                // Isi pertanyaan yang disampaikan mahasiswa ke Customer Support
                $table->longText('pertanyaan');

                // Calon tag yang disarankan untuk pertanyaan ini
                $table->string('calonTag', 45)->nullable();

                // FK ke mahasiswa pengirim (NULLABLE: Guest tidak punya akun mahasiswa)
                $table->integer('tMahasiswas_id')->nullable();
                $table->foreign('tMahasiswas_id', 'fk_tPertanyaanMahasiswas_tMahasiswas1')
                      ->references('id')
                      ->on('tmahasiswas')
                      ->onDelete('no action')
                      ->onUpdate('no action');

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tformpertanyaancs');
    }
};
