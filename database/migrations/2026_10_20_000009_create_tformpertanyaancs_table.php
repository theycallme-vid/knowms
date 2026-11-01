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
                $table->longText('pertanyaan');
                $table->string('calonTag', 45)->nullable();

                // FK ke sesi (nullable)
                $table->integer('tSession_id')->nullable();
                $table->foreign('tSession_id', 'fk_tFormPertanyaanCs_tSession1')
                      ->references('id')
                      ->on('tsession');

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tformpertanyaancs');
    }
};
