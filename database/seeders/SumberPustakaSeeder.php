<?php

namespace Database\Seeders;

use App\Models\DaftarPustaka;
use Illuminate\Database\Seeder;

class SumberPustakaSeeder extends Seeder
{
    /**
     * Run the database seeds (Khusus Tautan / Link saja).
     */
    public function run(): void
    {
        DaftarPustaka::ensurePublicPrivateEnum();

        $links = [
            [
                'judul' => 'Pedoman Akademik Universitas 2026/2027',
                'link'  => 'https://akademik.kampus.ac.id/pedoman-2026',
                'type'  => 'public',
            ],
            [
                'judul' => 'SOP Layanan Administrasi BAAK',
                'link'  => 'https://baak.kampus.ac.id/standar-operasional',
                'type'  => 'private',
            ],
            [
                'judul' => 'Panduan Pengisian KRS & Perwalian',
                'link'  => 'https://akademik.kampus.ac.id/panduan-krs',
                'type'  => 'public',
            ],
            [
                'judul' => 'Ketentuan Beasiswa & Bantuan Biaya Pendidikan',
                'link'  => 'https://kemahasiswaan.kampus.ac.id/beasiswa',
                'type'  => 'public',
            ],
            [
                'judul' => 'Pedoman Tugas Akhir & Yudisium Mahasiswa',
                'link'  => 'https://akademik.kampus.ac.id/pedoman-ta',
                'type'  => 'public',
            ],
            [
                'judul' => 'Aturan Internal Keuangan & Cicilan UKT',
                'link'  => 'https://keuangan.kampus.ac.id/sop-ukt',
                'type'  => 'private',
            ],
            [
                'judul' => 'Kalender Akademik Tahun Ajaran 2026/2027',
                'link'  => 'https://akademik.kampus.ac.id/kalender-2026',
                'type'  => 'public',
            ],
        ];

        foreach ($links as $item) {
            DaftarPustaka::updateOrCreate(
                ['link' => $item['link']],
                [
                    'judul'  => $item['judul'],
                    'type'   => $item['type'],
                    'upload' => null,
                ]
            );
        }
    }
}

