<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DirektoratSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $direktorats = [
            ['id' => 1, 'nama' => 'Direktorat Akademik', 'is_pusat' => 1],
            ['id' => 2, 'nama' => 'Fakultas Teknik', 'is_pusat' => 0],
            ['id' => 3, 'nama' => 'Fakultas Farmasi', 'is_pusat' => 0],
            ['id' => 4, 'nama' => 'Fakultas Bisnis & Ekonomika', 'is_pusat' => 0],
            ['id' => 5, 'nama' => 'Fakultas Kedokteran', 'is_pusat' => 0],
            ['id' => 6, 'nama' => 'Fakultas Industri Kreatif', 'is_pusat' => 0],
            ['id' => 7, 'nama' => 'Fakultas Hukum', 'is_pusat' => 0],
            ['id' => 8, 'nama' => 'Fakultas Psikologi', 'is_pusat' => 0],
            ['id' => 9, 'nama' => 'Fakultas Teknobiologi', 'is_pusat' => 0],
        ];

        $now = now();
        $hasIsPusat  = Schema::hasColumn('tdirektorat', 'is_pusat');
        $hasIsActive = Schema::hasColumn('tdirektorat', 'is_active');

        foreach ($direktorats as $dir) {
            $payload = [
                'nama'       => $dir['nama'],
                'updated_at' => $now,
            ];
            if ($hasIsPusat) {
                $payload['is_pusat'] = $dir['is_pusat'];
            }
            if ($hasIsActive) {
                $payload['is_active'] = 1;
            }

            $exists = DB::table('tdirektorat')->where('id', $dir['id'])->exists();
            if (!$exists) {
                $payload['created_at'] = $now;
            }

            DB::table('tdirektorat')->updateOrInsert(['id' => $dir['id']], $payload);
        }
    }
}
