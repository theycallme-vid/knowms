<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class BarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataku = [
            ['nama' => 'Nasi Goreng', 'harga' => '10000', 'stok' => '10', 'kategori_id' => '1'],
            ['nama' => 'Ayam Goreng', 'harga' => '20000', 'stok' => '15', 'kategori_id' => '1'],
            ['nama' => 'Bakso', 'harga' => '30000', 'stok' => '20', 'kategori_id' => '1'],
            ['nama' => 'Mie Ayam', 'harga' => '40000', 'stok' => '18', 'kategori_id' => '1'],
            ['nama' => 'Mie Goreng', 'harga' => '50000', 'stok' => '22', 'kategori_id' => '1'],
            ['nama' => 'Jus Apel', 'harga' => '23000', 'stok' => '22', 'kategori_id' => '3'],
            ['nama' => 'Jus Mangga', 'harga' => '15000', 'stok' => '19', 'kategori_id' => '3'],
            ['nama' => 'Soda Enak', 'harga' => '18000', 'stok' => '30', 'kategori_id' => '4'],

        ];
        DB::table('barangs')->insert($dataku);
    }
}
