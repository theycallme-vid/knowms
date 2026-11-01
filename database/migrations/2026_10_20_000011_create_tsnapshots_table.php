<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tsnapshots')) {
            Schema::create('tsnapshots', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->date('periode_awal');
                $table->date('periode_akhir');
                $table->double('total_pencarian')->default(0);
                $table->double('total_sukses')->default(0);
                $table->double('total_gagal')->default(0);
                $table->double('total_mhs_aktif')->default(0);
                $table->double('total_form_cs')->default(0);
                $table->longText('top_keywords')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tsnapshots');
    }
};
