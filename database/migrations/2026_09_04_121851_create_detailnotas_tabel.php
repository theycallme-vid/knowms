<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('detailnotas', function (Blueprint $table) {
            $table->unsignedBigInteger('notas_id'); 
            $table->foreign('notas_id')->references('id')->on('notas');
            $table->unsignedBigInteger('barangs_id'); 
            $table->foreign('barangs_id')->references('id')->on('barangs');
            $table->integer('qty');
            $table->decimal('subtotal', total:9, places:2);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detailnotas_tabel');
    }
};
