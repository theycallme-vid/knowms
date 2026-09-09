<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DetailNotaSeeder extends Seeder
{
    public function run(): void
    {
        // Setiap nota punya 2 baris detail. notas_id: 1–30, barangs_id: 1–20
        $dataku = [
            // Nota 1
            ['notas_id' => '1',  'barangs_id' => '1',  'qty' => 1, 'subtotal' => '15000.00'],
            ['notas_id' => '1',  'barangs_id' => '14', 'qty' => 2, 'subtotal' => '10000.00'],
            // Nota 2
            ['notas_id' => '2',  'barangs_id' => '2',  'qty' => 1, 'subtotal' => '20000.00'],
            ['notas_id' => '2',  'barangs_id' => '16', 'qty' => 2, 'subtotal' => '8000.00'],
            // Nota 3
            ['notas_id' => '3',  'barangs_id' => '3',  'qty' => 2, 'subtotal' => '24000.00'],
            ['notas_id' => '3',  'barangs_id' => '9',  'qty' => 1, 'subtotal' => '10000.00'],
            // Nota 4
            ['notas_id' => '4',  'barangs_id' => '4',  'qty' => 1, 'subtotal' => '12000.00'],
            ['notas_id' => '4',  'barangs_id' => '17', 'qty' => 1, 'subtotal' => '3000.00'],
            // Nota 5
            ['notas_id' => '5',  'barangs_id' => '1',  'qty' => 2, 'subtotal' => '30000.00'],
            ['notas_id' => '5',  'barangs_id' => '11', 'qty' => 2, 'subtotal' => '14000.00'],
            // Nota 6
            ['notas_id' => '6',  'barangs_id' => '5',  'qty' => 1, 'subtotal' => '8000.00'],
            ['notas_id' => '6',  'barangs_id' => '15', 'qty' => 2, 'subtotal' => '16000.00'],
            // Nota 7
            ['notas_id' => '7',  'barangs_id' => '2',  'qty' => 1, 'subtotal' => '20000.00'],
            ['notas_id' => '7',  'barangs_id' => '10', 'qty' => 1, 'subtotal' => '12000.00'],
            // Nota 8
            ['notas_id' => '8',  'barangs_id' => '3',  'qty' => 1, 'subtotal' => '12000.00'],
            ['notas_id' => '8',  'barangs_id' => '16', 'qty' => 1, 'subtotal' => '4000.00'],
            // Nota 9
            ['notas_id' => '9',  'barangs_id' => '1',  'qty' => 2, 'subtotal' => '30000.00'],
            ['notas_id' => '9',  'barangs_id' => '6',  'qty' => 2, 'subtotal' => '12000.00'],
            // Nota 10
            ['notas_id' => '10', 'barangs_id' => '4',  'qty' => 2, 'subtotal' => '24000.00'],
            ['notas_id' => '10', 'barangs_id' => '9',  'qty' => 1, 'subtotal' => '10000.00'],
            // Nota 11
            ['notas_id' => '11', 'barangs_id' => '7',  'qty' => 1, 'subtotal' => '10000.00'],
            ['notas_id' => '11', 'barangs_id' => '14', 'qty' => 3, 'subtotal' => '15000.00'],
            // Nota 12
            ['notas_id' => '12', 'barangs_id' => '2',  'qty' => 2, 'subtotal' => '40000.00'],
            ['notas_id' => '12', 'barangs_id' => '11', 'qty' => 2, 'subtotal' => '14000.00'],
            // Nota 13
            ['notas_id' => '13', 'barangs_id' => '1',  'qty' => 1, 'subtotal' => '15000.00'],
            ['notas_id' => '13', 'barangs_id' => '15', 'qty' => 2, 'subtotal' => '16000.00'],
            // Nota 14
            ['notas_id' => '14', 'barangs_id' => '3',  'qty' => 1, 'subtotal' => '12000.00'],
            ['notas_id' => '14', 'barangs_id' => '16', 'qty' => 1, 'subtotal' => '4000.00'],
            // Nota 15
            ['notas_id' => '15', 'barangs_id' => '5',  'qty' => 2, 'subtotal' => '16000.00'],
            ['notas_id' => '15', 'barangs_id' => '10', 'qty' => 3, 'subtotal' => '30000.00'],
            // Nota 16
            ['notas_id' => '16', 'barangs_id' => '4',  'qty' => 1, 'subtotal' => '12000.00'],
            ['notas_id' => '16', 'barangs_id' => '12', 'qty' => 2, 'subtotal' => '14000.00'],
            // Nota 17
            ['notas_id' => '17', 'barangs_id' => '2',  'qty' => 2, 'subtotal' => '40000.00'],
            ['notas_id' => '17', 'barangs_id' => '9',  'qty' => 2, 'subtotal' => '20000.00'],
            // Nota 18
            ['notas_id' => '18', 'barangs_id' => '6',  'qty' => 1, 'subtotal' => '6000.00'],
            ['notas_id' => '18', 'barangs_id' => '16', 'qty' => 4, 'subtotal' => '16000.00'],
            // Nota 19
            ['notas_id' => '19', 'barangs_id' => '1',  'qty' => 2, 'subtotal' => '30000.00'],
            ['notas_id' => '19', 'barangs_id' => '13', 'qty' => 2, 'subtotal' => '18000.00'],
            // Nota 20
            ['notas_id' => '20', 'barangs_id' => '3',  'qty' => 1, 'subtotal' => '12000.00'],
            ['notas_id' => '20', 'barangs_id' => '14', 'qty' => 2, 'subtotal' => '10000.00'],
            // Nota 21
            ['notas_id' => '21', 'barangs_id' => '2',  'qty' => 2, 'subtotal' => '40000.00'],
            ['notas_id' => '21', 'barangs_id' => '7',  'qty' => 2, 'subtotal' => '20000.00'],
            // Nota 22
            ['notas_id' => '22', 'barangs_id' => '4',  'qty' => 1, 'subtotal' => '12000.00'],
            ['notas_id' => '22', 'barangs_id' => '17', 'qty' => 1, 'subtotal' => '3000.00'],
            // Nota 23
            ['notas_id' => '23', 'barangs_id' => '5',  'qty' => 3, 'subtotal' => '24000.00'],
            ['notas_id' => '23', 'barangs_id' => '9',  'qty' => 3, 'subtotal' => '30000.00'],
            // Nota 24
            ['notas_id' => '24', 'barangs_id' => '1',  'qty' => 1, 'subtotal' => '15000.00'],
            ['notas_id' => '24', 'barangs_id' => '15', 'qty' => 2, 'subtotal' => '16000.00'],
            // Nota 25
            ['notas_id' => '25', 'barangs_id' => '8',  'qty' => 1, 'subtotal' => '10000.00'],
            ['notas_id' => '25', 'barangs_id' => '16', 'qty' => 1, 'subtotal' => '4000.00'],
            // Nota 26
            ['notas_id' => '26', 'barangs_id' => '2',  'qty' => 2, 'subtotal' => '40000.00'],
            ['notas_id' => '26', 'barangs_id' => '10', 'qty' => 3, 'subtotal' => '30000.00'],
            // Nota 27
            ['notas_id' => '27', 'barangs_id' => '3',  'qty' => 2, 'subtotal' => '24000.00'],
            ['notas_id' => '27', 'barangs_id' => '11', 'qty' => 1, 'subtotal' => '7000.00'],
            // Nota 28
            ['notas_id' => '28', 'barangs_id' => '1',  'qty' => 2, 'subtotal' => '30000.00'],
            ['notas_id' => '28', 'barangs_id' => '6',  'qty' => 3, 'subtotal' => '18000.00'],
            // Nota 29
            ['notas_id' => '29', 'barangs_id' => '4',  'qty' => 1, 'subtotal' => '12000.00'],
            ['notas_id' => '29', 'barangs_id' => '14', 'qty' => 2, 'subtotal' => '10000.00'],
            // Nota 30
            ['notas_id' => '30', 'barangs_id' => '2',  'qty' => 3, 'subtotal' => '60000.00'],
            ['notas_id' => '30', 'barangs_id' => '9',  'qty' => 3, 'subtotal' => '30000.00'],
        ];
        DB::table('detailnotas')->insert($dataku);
    }
}
