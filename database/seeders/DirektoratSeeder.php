<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DirektoratSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Berisi seluruh Fakultas, Program Vokasi/Pascasarjana, Direktorat, Biro,
     * Lembaga, dan Unit Penunjang di lingkungan Universitas Surabaya (UBAYA).
     */
    public function run(): void
    {
        $direktorats = [
            // ── DIREKTORAT PUSAT KMS (ADMIK) ──────────────────────────────────────
            ['id' => 1,  'nama' => 'Direktorat Akademik', 'is_pusat' => 1],

            // ── SELURUH FAKULTAS & PROGRAM PENDIDIKAN UBAYA ───────────────────────
            ['id' => 2,  'nama' => 'Fakultas Teknik', 'is_pusat' => 0],
            ['id' => 3,  'nama' => 'Fakultas Farmasi', 'is_pusat' => 0],
            ['id' => 4,  'nama' => 'Fakultas Bisnis & Ekonomika', 'is_pusat' => 0],
            ['id' => 5,  'nama' => 'Fakultas Kedokteran', 'is_pusat' => 0],
            ['id' => 6,  'nama' => 'Fakultas Industri Kreatif', 'is_pusat' => 0],
            ['id' => 7,  'nama' => 'Fakultas Hukum', 'is_pusat' => 0],
            ['id' => 8,  'nama' => 'Fakultas Psikologi', 'is_pusat' => 0],
            ['id' => 9,  'nama' => 'Fakultas Teknobiologi', 'is_pusat' => 0],
            ['id' => 10, 'nama' => 'Politeknik Ubaya', 'is_pusat' => 0],
            ['id' => 11, 'nama' => 'Program Pascasarjana', 'is_pusat' => 0],

            // ── SELURUH DIREKTORAT, BIRO, LEMBAGA & UNIT KERJA UBAYA ─────────────
            ['id' => 12, 'nama' => 'Direktorat Pengembangan Kemahasiswaan (DPK)', 'is_pusat' => 0],
            ['id' => 13, 'nama' => 'Direktorat Keuangan', 'is_pusat' => 0],
            ['id' => 14, 'nama' => 'Direktorat Sumber Daya Manusia (DSDM)', 'is_pusat' => 0],
            ['id' => 15, 'nama' => 'Direktorat Sistem Informasi Manajemen (SIM)', 'is_pusat' => 0],
            ['id' => 16, 'nama' => 'Direktorat Kerjasama Kelembagaan (DKK)', 'is_pusat' => 0],
            ['id' => 17, 'nama' => 'Direktorat Penjaminan Mutu dan Audit Internal (DPMAI)', 'is_pusat' => 0],
            ['id' => 18, 'nama' => 'Direktorat Perpustakaan', 'is_pusat' => 0],
            ['id' => 19, 'nama' => 'Direktorat Manajemen Aset dan Pengadaan (MAP)', 'is_pusat' => 0],
            ['id' => 20, 'nama' => 'Direktorat Marketing & Public Relations (MPR)', 'is_pusat' => 0],
            ['id' => 21, 'nama' => 'Lembaga Penelitian dan Pengabdian kepada Masyarakat (LPPM)', 'is_pusat' => 0],
            ['id' => 22, 'nama' => 'Pusat Bahasa (Ubaya Language Center)', 'is_pusat' => 0],
            ['id' => 23, 'nama' => 'Pusat Pemberdayaan Komunitas Perkotaan (PUSBANGKOT)', 'is_pusat' => 0],
            ['id' => 24, 'nama' => 'Pusat Informasi dan Layanan Konsultasi Karir (PILKK)', 'is_pusat' => 0],
            ['id' => 25, 'nama' => 'Pusat Studi Hak Asasi Manusia (PUSHAM)', 'is_pusat' => 0],
            ['id' => 26, 'nama' => 'Pusat Studi Lingkungan (PSL)', 'is_pusat' => 0],
            ['id' => 27, 'nama' => 'Pusat Studi Energi Terbarukan (PSET)', 'is_pusat' => 0],
            ['id' => 28, 'nama' => 'Pusat Arsip dan Museum Ubaya', 'is_pusat' => 0],
            ['id' => 29, 'nama' => 'Kantor Urusan Internasional (International Office)', 'is_pusat' => 0],
            ['id' => 30, 'nama' => 'Ubaya Training Center (UTC)', 'is_pusat' => 0],
            ['id' => 31, 'nama' => 'Ubaya Medical Center (UMC)', 'is_pusat' => 0],
            ['id' => 32, 'nama' => 'Ubaya Sport Center (USC)', 'is_pusat' => 0],
            ['id' => 33, 'nama' => 'Sentra HKI Ubaya', 'is_pusat' => 0],
            ['id' => 34, 'nama' => 'Inkubator Bisnis Ubaya (IBU)', 'is_pusat' => 0],
            ['id' => 35, 'nama' => 'Lembaga Sertifikasi Profesi (LSP) Ubaya', 'is_pusat' => 0],
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

            $existingById = DB::table('tdirektorat')->where('id', $dir['id'])->first();
            $existingByName = DB::table('tdirektorat')->whereRaw('LOWER(nama) = ?', [mb_strtolower($dir['nama'])])->first();

            if ($existingByName && (!$existingById || (int) $existingByName->id !== (int) $dir['id'])) {
                DB::table('tdirektorat')->where('id', $existingByName->id)->update($payload);
                continue;
            }

            if (!$existingById) {
                $payload['created_at'] = $now;
            }

            DB::table('tdirektorat')->updateOrInsert(['id' => $dir['id']], $payload);
        }
    }
}
