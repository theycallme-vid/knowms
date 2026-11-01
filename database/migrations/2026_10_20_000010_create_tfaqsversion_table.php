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

                // FK ke pustaka sumber (nullable)
                $table->integer('tDaftarPustakas_id')->nullable();
                $table->foreign('tDaftarPustakas_id', 'fk_tFaqsVersion_tDaftarPustaka1')
                      ->references('id')
                      ->on('tdaftarpustaka');

                // FK ke FAQ induk
                $table->string('tFaqs_id', 20);
                $table->foreign('tFaqs_id', 'fk_tFaqsVersion_tFaqs1')
                      ->references('id')
                      ->on('tfaqs');

                // FK ke Validator (nullable: bisa belum divalidasi)
                $table->integer('id_validator')->nullable();
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
