<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tfaqs')) {
            Schema::create('tfaqs', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();

                $table->longText('pertanyaan');
                $table->longText('jawaban');

                // Status FAQ sesuai alur bisnis KMS UBAYA
                // draft     : baru dibuat / sedang dikerjakan Data Entry
                // submitted : sudah diajukan ke Validator, menunggu review
                // approved  : disetujui Validator, tampil publik
                // rejected  : ditolak Validator, perlu diperbaiki Data Entry
                $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])
                      ->default('draft');

                // FK ke staf Data Entry yang membuat FAQ ini (WAJIB ADA)
                $table->integer('id_dataentry');
                $table->foreign('id_dataentry', 'fk_tFaqs_tUsers1')
                      ->references('id')
                      ->on('tusers')
                      ->onDelete('no action')
                      ->onUpdate('no action');

                // FK ke Validator yang memeriksa FAQ ini (NULLABLE saat draft)
                $table->integer('id_validator')->nullable();
                $table->foreign('id_validator', 'fk_tFaqs_tUsers2')
                      ->references('id')
                      ->on('tusers')
                      ->onDelete('no action')
                      ->onUpdate('no action');

                // Catatan dari Validator jika FAQ ditolak
                $table->text('catatanValidasi')->nullable();

                // Tanggal FAQ disetujui atau ditolak oleh Validator
                $table->timestamp('tglValidasi')->nullable();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tfaqs');
    }
};
