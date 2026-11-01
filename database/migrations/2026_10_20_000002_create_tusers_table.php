<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tusers')) {
            Schema::create('tusers', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();

                // Self-referencing FK (misal: supervisor/atasan)
                $table->integer('superId')->nullable();
                $table->foreign('superId', 'fk_tUsers_tUsers1')
                      ->references('id')
                      ->on('tusers');

                $table->string('nama', 100);
                $table->string('password', 255);
                $table->string('email', 50)->unique();
                $table->string('npk', 15)->nullable()->unique();

                $table->integer('tRoles_id');
                $table->foreign('tRoles_id', 'fk_tUsers_tRoles1')
                      ->references('id')
                      ->on('troles');

                $table->tinyInteger('is_active')->default(1);

                $table->timestamps();
                $table->rememberToken()->nullable();

                // FK ke direktorat (nullable)
                $table->integer('tDirektorat_id')->nullable();
                $table->foreign('tDirektorat_id', 'fk_tUsers_tDirektorat1')
                      ->references('id')
                      ->on('tdirektorat');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tusers');
    }
};
