<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tdetail_faqs')) {
            Schema::create('tdetail_faqs', function (Blueprint $table) {
                // Tabel pivot antara tFaqs dan tTag (Many-to-Many)
                // Tidak perlu 'id' autoincrement, PK gabungan dari dua FK sudah cukup
                // Tidak perlu timestamps karena ini murni tabel pivot

                $table->integer('tFaqs_id');
                $table->integer('tTag_id');

                // Primary Key gabungan (composite PK)
                $table->primary(['tFaqs_id', 'tTag_id'], 'pk_tdetail_faqs');

                $table->foreign('tFaqs_id', 'fk_tFaqs_has_tTag_tFaqs1')
                      ->references('id')
                      ->on('tfaqs')
                      ->onDelete('cascade')  // Jika FAQ dihapus, pivot-nya ikut terhapus
                      ->onUpdate('no action');

                $table->foreign('tTag_id', 'fk_tFaqs_has_tTag_tTag1')
                      ->references('id')
                      ->on('ttag')
                      ->onDelete('cascade')  // Jika Tag dihapus, pivot-nya ikut terhapus
                      ->onUpdate('no action');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tdetail_faqs');
    }
};
