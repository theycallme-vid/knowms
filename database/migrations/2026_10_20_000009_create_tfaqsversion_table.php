<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tfaqsversion')) {
            Schema::create('tfaqsversion', function (Blueprint $table) {
                $table->string('id', 15)->primary();

                // FK ke FAQ (tFaqs.id bertipe VARCHAR(20))
                $table->string('tFaqs_id', 20);
                $table->foreign('tFaqs_id', 'fk_tFaqsVersion_tFaqs1')
                      ->references('id')
                      ->on('tfaqs');

                // FK ke Validator yang memeriksa versi ini
                $table->integer('id_validator');
                $table->foreign('id_validator', 'fk_tFaqsVersion_tUsers1')
                      ->references('id')
                      ->on('tusers');

                $table->text('jawaban');
                $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])
                      ->default('draft');
                $table->text('catatan')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tfaqsversion');
    }
};
