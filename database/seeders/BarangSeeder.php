<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarangSeeder extends Seeder
{
    public function run(): void
    {
        $dataku = [
            // Makanan Berat (kategori_id: 1)
            ['nama' => 'Nasi Goreng',         'harga' => '15000', 'stok' => '30', 'kategori_id' => '1'],
            ['nama' => 'Ayam Goreng',          'harga' => '20000', 'stok' => '25', 'kategori_id' => '1'],
            ['nama' => 'Bakso',               'harga' => '12000', 'stok' => '40', 'kategori_id' => '1'],
            ['nama' => 'Mie Ayam',            'harga' => '12000', 'stok' => '35', 'kategori_id' => '1'],
            // Makanan Ringan (kategori_id: 2)
            ['nama' => 'Keripik Kentang',     'harga' => '8000',  'stok' => '50', 'kategori_id' => '2'],
            ['nama' => 'Kacang Garuda',       'harga' => '6000',  'stok' => '60', 'kategori_id' => '2'],
            ['nama' => 'Biskuit Malkist',     'harga' => '10000', 'stok' => '45', 'kategori_id' => '2'],
            // Jus (kategori_id: 3)
            ['nama' => 'Jus Apel',            'harga' => '10000', 'stok' => '20', 'kategori_id' => '3'],
            ['nama' => 'Jus Jeruk',           'harga' => '10000', 'stok' => '20', 'kategori_id' => '3'],
            ['nama' => 'Jus Mangga',          'harga' => '12000', 'stok' => '15', 'kategori_id' => '3'],
            // Soda (kategori_id: 4)
            ['nama' => 'Coca Cola',           'harga' => '7000',  'stok' => '40', 'kategori_id' => '4'],
            ['nama' => 'Sprite',              'harga' => '7000',  'stok' => '40', 'kategori_id' => '4'],
            // Susu (kategori_id: 5)
            ['nama' => 'Susu Beruang',        'harga' => '9000',  'stok' => '30', 'kategori_id' => '5'],
            // Kopi (kategori_id: 6)
            ['nama' => 'Kopi Hitam',          'harga' => '5000',  'stok' => '80', 'kategori_id' => '6'],
            ['nama' => 'Kopi Susu',           'harga' => '8000',  'stok' => '60', 'kategori_id' => '6'],
            // Teh (kategori_id: 7)
            ['nama' => 'Es Teh Manis',        'harga' => '4000',  'stok' => '100', 'kategori_id' => '7'],
            ['nama' => 'Teh Tawar',           'harga' => '3000',  'stok' => '100', 'kategori_id' => '7'],
            // Jajanan Pasar (kategori_id: 8)
            ['nama' => 'Lemper',              'harga' => '3000',  'stok' => '60', 'kategori_id' => '8'],
            ['nama' => 'Onde-Onde',           'harga' => '2500',  'stok' => '70', 'kategori_id' => '8'],
            // Gorengan (kategori_id: 9)
            ['nama' => 'Tahu Isi',            'harga' => '2000',  'stok' => '100', 'kategori_id' => '9'],
        ];
        DB::table('barangs')->insert($dataku);
    }
}
