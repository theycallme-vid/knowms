<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['id' => 1, 'nama' => 'Super Admin'],
            ['id' => 2, 'nama' => 'Data Entry'],
            ['id' => 3, 'nama' => 'Validator'],
        ];

        // Query builder dipakai agar kolom id bisa diisi eksplisit
        // (id tidak ada di $fillable model Role).
        foreach ($roles as $r) {
            DB::table('troles')->updateOrInsert(
                ['id' => $r['id']],
                ['nama' => $r['nama']]
            );
        }
    }
}
