<?php

namespace Database\Seeders;

use App\Http\Controllers\PortalController;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KataKunciSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus seluruh kata kunci fakultas lama terlebih dahulu (beserta relasinya di pivot tdetail_faqs)
        $oldFacultyKeywords = [
            'Teknik',
            'Ekonomi & Bisnis',
            'Hukum',
            'Ilmu Sosial',
            'Kedokteran',
            'Sains & Teknologi',
            'Ilmu Komputer',
        ];

        $oldTagIds = Tag::whereIn('nama', $oldFacultyKeywords)->pluck('id');
        if ($oldTagIds->isNotEmpty()) {
            DB::table('tdetail_faqs')->whereIn('tTag_id', $oldTagIds)->delete();
            Tag::whereIn('id', $oldTagIds)->delete();
        }

        // Gunakan daftar fakultas resmi dari pengecekan NRP (PortalController::FAKULTAS_MAP)
        $facultyKeywords = array_values(PortalController::FAKULTAS_MAP);

        $generalKeywords = [
            'Umum',
            'Akademik',
            'KRS',
            'Rencana Studi',
            'Cuti Akademik',
            'Yudisium',
            'Wisuda',
            'Surat Akademik',
            'Layanan Mahasiswa',
            'Transkrip Nilai',
            'Administrasi Akademik',
            'Tugas Akhir',
            'Pindah Prodi',
            'Biaya Kuliah',
            'Beasiswa',
            'Praktikum',
            'Magang MBKM',
            'Fasilitas Kampus',
        ];

        $keywords = array_values(array_unique(array_merge($facultyKeywords, $generalKeywords)));

        foreach ($keywords as $nama) {
            Tag::firstOrCreate(['nama' => $nama]);
        }
    }
}

