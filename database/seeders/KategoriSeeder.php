<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $dataku = [
            ['nama_kategori' => 'Makanan Berat',        'deskripsi' => 'makanan yang mengenyangkan seperti nasi dan lauk'],
            ['nama_kategori' => 'Makanan Ringan',        'deskripsi' => 'cemilan enak untuk bersantai'],
            ['nama_kategori' => 'Jus',                   'deskripsi' => 'minuman segar berbahan dasar buah'],
            ['nama_kategori' => 'Soda',                  'deskripsi' => 'minuman berkarbonasi yang menyegarkan'],
            ['nama_kategori' => 'Susu',                  'deskripsi' => 'minuman sehat dan bergizi'],
            ['nama_kategori' => 'Kopi',                  'deskripsi' => 'minuman kopi arabika dan robusta'],
            ['nama_kategori' => 'Teh',                   'deskripsi' => 'minuman teh panas dan dingin'],
            ['nama_kategori' => 'Jajanan Pasar',         'deskripsi' => 'kue-kue tradisional nusantara'],
            ['nama_kategori' => 'Gorengan',              'deskripsi' => 'aneka gorengan renyah dan gurih'],
            ['nama_kategori' => 'Minuman Tradisional',   'deskripsi' => 'minuman herbal dan tradisional'],
        ];
        DB::table('kategoris')->insert($dataku);
    }
}
