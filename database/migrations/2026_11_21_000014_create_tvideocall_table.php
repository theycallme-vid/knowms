<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tvideocall')) {
            Schema::create('tvideocall', function (Blueprint $table) {
                $table->id();
                $table->string('room_id', 150)->unique()->index();
                $table->string('nama_mahasiswa', 150)->nullable();
                $table->string('fakultas', 100)->nullable();
                $table->string('status', 30)->default('waiting'); // waiting, connected, ended
                $table->unsignedBigInteger('admin_id')->nullable();
                $table->string('admin_nama', 150)->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('ended_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tvideocall');
    }
};
