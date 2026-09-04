


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
        Schema::create('barangs', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->decimal('harga', total: 9, places: 2);
            $table->integer('stok')->default('0');
            $table->unsignedBigInteger('kategori_id'); //membuat field kategori_id sebagai foreign key
            $table->foreign('kategori_id')->references('id')->on('kategoris'); //membuat relasi one to many antara barang dan kategori
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};
