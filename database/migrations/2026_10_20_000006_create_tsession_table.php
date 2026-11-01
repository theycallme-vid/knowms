<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tsession')) {
            Schema::create('tsession', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->string('kode_fakultas', 100)->nullable();
                $table->timestamps();
                $table->softDeletes('deleted_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tsession');
    }
};
