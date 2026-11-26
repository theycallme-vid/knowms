<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tusers') && !Schema::hasColumn('tusers', 'password_encrypted')) {
            Schema::table('tusers', function (Blueprint $table) {
                $table->text('password_encrypted')->nullable()->after('password');
            });

            // Backfill akun bawaan seeder agar password dapat dilihat oleh Super Admin / Validator
            $knownDefaults = ['admin123', 'data123', 'validator123'];
            $users = DB::table('tusers')->select('id', 'password')->get();
            foreach ($users as $u) {
                foreach ($knownDefaults as $candidate) {
                    if (Hash::check($candidate, (string) $u->password)) {
                        DB::table('tusers')
                            ->where('id', $u->id)
                            ->update(['password_encrypted' => Crypt::encryptString($candidate)]);
                        break;
                    }
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tusers') && Schema::hasColumn('tusers', 'password_encrypted')) {
            Schema::table('tusers', function (Blueprint $table) {
                $table->dropColumn('password_encrypted');
            });
        }
    }
};
