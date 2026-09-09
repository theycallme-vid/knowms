<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PelangganSeeder extends Seeder
{
    public function run(): void
    {
        $dataku = [
            ['nama' => 'Agus Supriyadi',   'telp' => '089876543210', 'alamat' => 'Jl. Merdeka No. 1'],
            ['nama' => 'Dewi Lestari',     'telp' => '089876543211', 'alamat' => 'Jl. Sudirman No. 2'],
            ['nama' => 'Eko Prasetyo',     'telp' => '089876543212', 'alamat' => 'Jl. Thamrin No. 3'],
            ['nama' => 'Fani Rahmawati',   'telp' => '089876543213', 'alamat' => 'Jl. Gatot Subroto No. 4'],
            ['nama' => 'Gilang Ramadhan',  'telp' => '089876543214', 'alamat' => 'Jl. Diponegoro No. 5'],
            ['nama' => 'Hani Permata',     'telp' => '089876543215', 'alamat' => 'Jl. Imam Bonjol No. 6'],
            ['nama' => 'Irwan Setiawan',   'telp' => '089876543216', 'alamat' => 'Jl. Ahmad Yani No. 7'],
            ['nama' => 'Juwita Sari',      'telp' => '089876543217', 'alamat' => 'Jl. Pahlawan No. 8'],
            ['nama' => 'Krisna Bayu',      'telp' => '089876543218', 'alamat' => 'Jl. Cendana No. 9'],
            ['nama' => 'Lina Marlina',     'telp' => '089876543219', 'alamat' => 'Jl. Kenanga No. 10'],
            ['nama' => 'Miko Prasetya',    'telp' => '089876543220', 'alamat' => 'Jl. Melati No. 11'],
            ['nama' => 'Nanda Kurnia',     'telp' => '089876543221', 'alamat' => 'Jl. Mawar No. 12'],
            ['nama' => 'Oki Firmansyah',   'telp' => '089876543222', 'alamat' => 'Jl. Dahlia No. 13'],
            ['nama' => 'Putri Andini',     'telp' => '089876543223', 'alamat' => 'Jl. Anggrek No. 14'],
            ['nama' => 'Qori Hidayat',     'telp' => '089876543224', 'alamat' => 'Jl. Flamboyan No. 15'],
            ['nama' => 'Rizky Maulana',    'telp' => '089876543225', 'alamat' => 'Jl. Cemara No. 16'],
            ['nama' => 'Sandi Wibowo',     'telp' => '089876543226', 'alamat' => 'Jl. Pinus No. 17'],
            ['nama' => 'Tika Wulandari',   'telp' => '089876543227', 'alamat' => 'Jl. Akasia No. 18'],
            ['nama' => 'Udin Saputra',     'telp' => '089876543228', 'alamat' => 'Jl. Bambang No. 19'],
            ['nama' => 'Vina Oktavia',     'telp' => '089876543229', 'alamat' => 'Jl. Manggis No. 20'],
            ['nama' => 'Wahyu Haryanto',   'telp' => '089876543230', 'alamat' => 'Jl. Rambutan No. 21'],
            ['nama' => 'Xena Pratiwi',     'telp' => '089876543231', 'alamat' => 'Jl. Salak No. 22'],
            ['nama' => 'Yoga Saputro',     'telp' => '089876543232', 'alamat' => 'Jl. Durian No. 23'],
            ['nama' => 'Zahra Fadilla',    'telp' => '089876543233', 'alamat' => 'Jl. Nangka No. 24'],
            ['nama' => 'Arif Budiman',     'telp' => '089876543234', 'alamat' => 'Jl. Jeruk No. 25'],
            ['nama' => 'Bella Safitri',    'telp' => '089876543235', 'alamat' => 'Jl. Apel No. 26'],
            ['nama' => 'Cahyo Nugroho',    'telp' => '089876543236', 'alamat' => 'Jl. Mangga No. 27'],
            ['nama' => 'Dika Sasmita',     'telp' => '089876543237', 'alamat' => 'Jl. Pisang No. 28'],
            ['nama' => 'Ela Fitriani',     'telp' => '089876543238', 'alamat' => 'Jl. Pepaya No. 29'],
            ['nama' => 'Fauzi Akbar',      'telp' => '089876543239', 'alamat' => 'Jl. Markisa No. 30'],
        ];
        DB::table('pelanggans')->insert($dataku);
    }
}
