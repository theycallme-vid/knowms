<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DirektoratSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $direktorats = [
            ['id' => 1, 'nama' => 'Direktorat Akademik'],
            ['id' => 2, 'nama' => 'Fakultas Teknik'],
            ['id' => 3, 'nama' => 'Fakultas Farmasi'],
            ['id' => 4, 'nama' => 'Fakultas Bisnis & Ekonomika'],
            ['id' => 5, 'nama' => 'Fakultas Kedokteran'],
            ['id' => 6, 'nama' => 'Fakultas Industri Kreatif'],
            ['id' => 7, 'nama' => 'Fakultas Hukum'],
            ['id' => 8, 'nama' => 'Fakultas Psikologi'],
            ['id' => 9, 'nama' => 'Fakultas Teknobiologi'],
        ];

        $now = now();

        foreach ($direktorats as $dir) {
            DB::table('tdirektorat')->updateOrInsert(
                ['id' => $dir['id']],
                [
                    'nama'       => $dir['nama'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}

