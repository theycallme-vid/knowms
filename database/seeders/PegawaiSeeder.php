<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PegawaiSeeder extends Seeder
{
    public function run(): void
    {
        $dataku = [
            ['nama' => 'Budi Santoso',   'telp' => '081234567890', 'jabatan' => 'Kasir'],
            ['nama' => 'Siti Aminah',    'telp' => '081234567891', 'jabatan' => 'Pelayan'],
            ['nama' => 'Andi Darmawan',  'telp' => '081234567892', 'jabatan' => 'Manajer'],
            ['nama' => 'Rina Melati',    'telp' => '081234567893', 'jabatan' => 'Kasir'],
            ['nama' => 'Joko Susilo',    'telp' => '081234567894', 'jabatan' => 'Koki'],
        ];
        DB::table('pegawais')->insert($dataku);
    }
}
