<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ttag')) {
            Schema::create('ttag', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->string('nama', 100);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ttag');
    }
};
