<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tdaftarpustaka')) {
            Schema::create('tdaftarpustaka', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->enum('type', ['link', 'upload']);
                $table->string('link', 45)->nullable();
                $table->binary('upload')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tdaftarpustaka');
    }
};
