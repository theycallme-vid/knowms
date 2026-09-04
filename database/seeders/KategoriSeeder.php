<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataku = [
            ['nama_kategori' => 'Makanan Berat', 'deskripsi' => ' ini adalah makan yang akan memberatkan perutmu'],
            ['nama_kategori' => 'Makanan Ringan', 'deskripsi' => ' ini adalah makan yang akan meringankan perutmu'],
            ['nama_kategori' => 'Jus', 'deskripsi' => ' ini adalah minuman yang berasalah dari olahan buah'],
            ['nama_kategori' => 'Soda', 'deskripsi' => ' ini adalah minuman yang membuatmu bersendawa'],
            ['nama_kategori' => 'Susu', 'deskripsi' => ' ini adalah minuman yang menyehatkan']
        ];
        DB::table('kategoris')->insert($dataku);
            
    }
}
