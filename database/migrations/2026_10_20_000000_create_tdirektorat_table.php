<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tdirektorat')) {
            Schema::create('tdirektorat', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->string('nama', 45);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tdirektorat');
    }
};
