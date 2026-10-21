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
        if (!Schema::hasTable('tusers')) {
            Schema::create('tusers', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->string('nama', 100);
                $table->string('password', 255);
                $table->string('email', 50);
                $table->string('npk', 15)->nullable()->unique();
                $table->integer('tRoles_id');
                $table->boolean('is_active')->default(true);
                $table->rememberToken();
                $table->timestamps();

                $table->foreign('tRoles_id', 'fk_tUsers_tRoles1')
                      ->references('id')
                      ->on('troles')
                      ->onDelete('no action')
                      ->onUpdate('no action');
            });
        } elseif (!Schema::hasColumn('tusers', 'remember_token')) {
            Schema::table('tusers', function (Blueprint $table) {
                $table->rememberToken()->nullable()->after('is_active');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tusers');
    }
};
