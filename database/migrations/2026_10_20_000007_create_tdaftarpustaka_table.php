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
                $table->enum('type', ['public', 'private'])->default('public');
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
            DB::statement("ALTER TABLE tdaftarpustaka MODIFY type VARCHAR(20) NOT NULL DEFAULT 'public'");
            DB::statement("UPDATE tdaftarpustaka SET type = 'public' WHERE LOWER(type) NOT IN ('public', 'private')");
            DB::statement("UPDATE tdaftarpustaka SET type = LOWER(type)");
            DB::statement("ALTER TABLE tdaftarpustaka MODIFY type ENUM('public', 'private') NOT NULL DEFAULT 'public'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tdaftarpustaka');
    }
};
