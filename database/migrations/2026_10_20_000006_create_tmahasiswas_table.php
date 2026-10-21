<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tmahasiswas')) {
            Schema::create('tmahasiswas', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();

                // NRP mahasiswa UBAYA, unik per mahasiswa
                $table->string('nrp', 15)->unique();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tmahasiswas');
    }
};
