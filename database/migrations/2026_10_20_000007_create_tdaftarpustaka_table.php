<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
                $table->timestamps();
            });

            // Sesuaikan tipe kolom upload menjadi MEDIUMBLOB sesuai ERD
            DB::statement('ALTER TABLE tdaftarpustaka MODIFY upload MEDIUMBLOB NULL');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tdaftarpustaka');
    }
};
