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
                $table->string('judul', 100)->nullable();
                $table->string('link', 60)->nullable();
                $table->binary('upload')->nullable();
                $table->timestamps();
            });

            // Sesuaikan tipe kolom upload menjadi MEDIUMBLOB sesuai ERD
            DB::statement('ALTER TABLE tdaftarpustaka MODIFY upload MEDIUMBLOB NULL');
        } else {
            if (!Schema::hasColumn('tdaftarpustaka', 'judul')) {
                DB::statement('ALTER TABLE tdaftarpustaka ADD COLUMN judul VARCHAR(100) NULL AFTER id');
                DB::statement("UPDATE tdaftarpustaka SET judul = link WHERE judul IS NULL OR judul = ''");
            }
            DB::statement('ALTER TABLE tdaftarpustaka MODIFY link VARCHAR(60) NULL');
            if (Schema::hasColumn('tdaftarpustaka', 'type')) {
                DB::statement('ALTER TABLE tdaftarpustaka DROP COLUMN type');
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tdaftarpustaka');
    }
};
