<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tfaqs')) {
            Schema::create('tfaqs', function (Blueprint $table) {
                $table->string('id', 20)->primary();

                // FK ke staf Data Entry yang membuat FAQ ini
                $table->integer('id_dataentry');
                $table->foreign('id_dataentry', 'fk_tFaqs_tUsers1')->references('id')->on('tusers');
                $table->text('pertanyaan');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tfaqs');
    }
};
