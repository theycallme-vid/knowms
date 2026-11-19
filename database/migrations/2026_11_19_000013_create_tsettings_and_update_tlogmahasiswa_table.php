<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tsettings')) {
            Schema::create('tsettings', function (Blueprint $table) {
                $table->id();
                $table->string('key', 100)->unique();
                $table->string('value', 255);
                $table->string('keterangan', 255)->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('tlogmahasiswa') && !Schema::hasColumn('tlogmahasiswa', 'jenis_interaksi')) {
            Schema::table('tlogmahasiswa', function (Blueprint $table) {
                $table->string('jenis_interaksi', 20)->default('view')->after('id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tlogmahasiswa') && Schema::hasColumn('tlogmahasiswa', 'jenis_interaksi')) {
            Schema::table('tlogmahasiswa', function (Blueprint $table) {
                $table->dropColumn('jenis_interaksi');
            });
        }

        Schema::dropIfExists('tsettings');
    }
};
