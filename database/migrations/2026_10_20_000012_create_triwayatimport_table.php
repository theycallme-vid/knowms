<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('triwayatimport')) {
            Schema::create('triwayatimport', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();

                // FK ke tusers (melacak siapa yang melakukan import)
                $table->integer('id_user');
                $table->foreign('id_user', 'fk_tRiwayatImport_tUsers1')
                      ->references('id')
                      ->on('tusers');

                $table->string('nama_file', 255);
                $table->string('file_path', 255);
                $table->enum('jenis_data', ['FAQ', 'Kata Kunci']);
                $table->enum('status', ['Sukses', 'Gagal', 'Diproses'])->default('Diproses');
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // Pemetaan Asal Data (Opsional): Tambahkan kolom id_riwayat_import (Nullable FK) pada tabel tfaqs
        if (Schema::hasTable('tfaqs') && !Schema::hasColumn('tfaqs', 'id_riwayat_import')) {
            Schema::table('tfaqs', function (Blueprint $table) {
                $table->integer('id_riwayat_import')->nullable()->after('id_dataentry');
                $table->foreign('id_riwayat_import', 'fk_tFaqs_tRiwayatImport1')
                      ->references('id')
                      ->on('triwayatimport')
                      ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tfaqs') && Schema::hasColumn('tfaqs', 'id_riwayat_import')) {
            Schema::table('tfaqs', function (Blueprint $table) {
                $table->dropForeign('fk_tFaqs_tRiwayatImport1');
                $table->dropColumn('id_riwayat_import');
            });
        }

        Schema::dropIfExists('triwayatimport');
    }
};

