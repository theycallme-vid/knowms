<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotaSeeder extends Seeder
{
    public function run(): void
    {
        // pegawai_id: 1–5, pelanggans_id: 1–30 (setiap pelanggan 1 nota)
        $dataku = [
            ['tanggal' => Carbon::now()->subDays(29)->format('Y-m-d'), 'grandtotal' => '35000.00',  'pegawai_id' => '1', 'pelanggans_id' => '1'],
            ['tanggal' => Carbon::now()->subDays(28)->format('Y-m-d'), 'grandtotal' => '28000.00',  'pegawai_id' => '2', 'pelanggans_id' => '2'],
            ['tanggal' => Carbon::now()->subDays(27)->format('Y-m-d'), 'grandtotal' => '42000.00',  'pegawai_id' => '3', 'pelanggans_id' => '3'],
            ['tanggal' => Carbon::now()->subDays(26)->format('Y-m-d'), 'grandtotal' => '19000.00',  'pegawai_id' => '4', 'pelanggans_id' => '4'],
            ['tanggal' => Carbon::now()->subDays(25)->format('Y-m-d'), 'grandtotal' => '56000.00',  'pegawai_id' => '5', 'pelanggans_id' => '5'],
            ['tanggal' => Carbon::now()->subDays(24)->format('Y-m-d'), 'grandtotal' => '33000.00',  'pegawai_id' => '1', 'pelanggans_id' => '6'],
            ['tanggal' => Carbon::now()->subDays(23)->format('Y-m-d'), 'grandtotal' => '47000.00',  'pegawai_id' => '2', 'pelanggans_id' => '7'],
            ['tanggal' => Carbon::now()->subDays(22)->format('Y-m-d'), 'grandtotal' => '21000.00',  'pegawai_id' => '3', 'pelanggans_id' => '8'],
            ['tanggal' => Carbon::now()->subDays(21)->format('Y-m-d'), 'grandtotal' => '62000.00',  'pegawai_id' => '4', 'pelanggans_id' => '9'],
            ['tanggal' => Carbon::now()->subDays(20)->format('Y-m-d'), 'grandtotal' => '38000.00',  'pegawai_id' => '5', 'pelanggans_id' => '10'],
            ['tanggal' => Carbon::now()->subDays(19)->format('Y-m-d'), 'grandtotal' => '25000.00',  'pegawai_id' => '1', 'pelanggans_id' => '11'],
            ['tanggal' => Carbon::now()->subDays(18)->format('Y-m-d'), 'grandtotal' => '71000.00',  'pegawai_id' => '2', 'pelanggans_id' => '12'],
            ['tanggal' => Carbon::now()->subDays(17)->format('Y-m-d'), 'grandtotal' => '44000.00',  'pegawai_id' => '3', 'pelanggans_id' => '13'],
            ['tanggal' => Carbon::now()->subDays(16)->format('Y-m-d'), 'grandtotal' => '18000.00',  'pegawai_id' => '4', 'pelanggans_id' => '14'],
            ['tanggal' => Carbon::now()->subDays(15)->format('Y-m-d'), 'grandtotal' => '53000.00',  'pegawai_id' => '5', 'pelanggans_id' => '15'],
            ['tanggal' => Carbon::now()->subDays(14)->format('Y-m-d'), 'grandtotal' => '39000.00',  'pegawai_id' => '1', 'pelanggans_id' => '16'],
            ['tanggal' => Carbon::now()->subDays(13)->format('Y-m-d'), 'grandtotal' => '66000.00',  'pegawai_id' => '2', 'pelanggans_id' => '17'],
            ['tanggal' => Carbon::now()->subDays(12)->format('Y-m-d'), 'grandtotal' => '22000.00',  'pegawai_id' => '3', 'pelanggans_id' => '18'],
            ['tanggal' => Carbon::now()->subDays(11)->format('Y-m-d'), 'grandtotal' => '48000.00',  'pegawai_id' => '4', 'pelanggans_id' => '19'],
            ['tanggal' => Carbon::now()->subDays(10)->format('Y-m-d'), 'grandtotal' => '31000.00',  'pegawai_id' => '5', 'pelanggans_id' => '20'],
            ['tanggal' => Carbon::now()->subDays(9)->format('Y-m-d'),  'grandtotal' => '75000.00',  'pegawai_id' => '1', 'pelanggans_id' => '21'],
            ['tanggal' => Carbon::now()->subDays(8)->format('Y-m-d'),  'grandtotal' => '29000.00',  'pegawai_id' => '2', 'pelanggans_id' => '22'],
            ['tanggal' => Carbon::now()->subDays(7)->format('Y-m-d'),  'grandtotal' => '57000.00',  'pegawai_id' => '3', 'pelanggans_id' => '23'],
            ['tanggal' => Carbon::now()->subDays(6)->format('Y-m-d'),  'grandtotal' => '43000.00',  'pegawai_id' => '4', 'pelanggans_id' => '24'],
            ['tanggal' => Carbon::now()->subDays(5)->format('Y-m-d'),  'grandtotal' => '16000.00',  'pegawai_id' => '5', 'pelanggans_id' => '25'],
            ['tanggal' => Carbon::now()->subDays(4)->format('Y-m-d'),  'grandtotal' => '82000.00',  'pegawai_id' => '1', 'pelanggans_id' => '26'],
            ['tanggal' => Carbon::now()->subDays(3)->format('Y-m-d'),  'grandtotal' => '36000.00',  'pegawai_id' => '2', 'pelanggans_id' => '27'],
            ['tanggal' => Carbon::now()->subDays(2)->format('Y-m-d'),  'grandtotal' => '64000.00',  'pegawai_id' => '3', 'pelanggans_id' => '28'],
            ['tanggal' => Carbon::now()->subDays(1)->format('Y-m-d'),  'grandtotal' => '27000.00',  'pegawai_id' => '4', 'pelanggans_id' => '29'],
            ['tanggal' => Carbon::now()->format('Y-m-d'),              'grandtotal' => '91000.00',  'pegawai_id' => '5', 'pelanggans_id' => '30'],
        ];
        DB::table('notas')->insert($dataku);
    }
}
