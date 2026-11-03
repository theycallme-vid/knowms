<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'nama'      => 'Admin KMS',
                'email'     => 'adminkms@gmail.com',
                'npk'       => 'ADM-2024-001',
                'password'  => Hash::make('admin123'),
                'tRoles_id' => 1,
                'is_active' => 1,
            ],
            [
                'nama'      => 'Data Entry KMS',
                'email'     => 'dataentrykms@gmail.com',
                'npk'       => 'DTE-2024-001',
                'password'  => Hash::make('data123'),
                'tRoles_id' => 2,
                'is_active' => 1,
            ],
            [
                'nama'      => 'Validator KMS',
                'email'     => 'validatorkms@gmail.com',
                'npk'       => 'VAL-2024-001',
                'password'  => Hash::make('validator123'),
                'tRoles_id' => 3,
                'is_active' => 1,
            ],
            // Staf Data Entry tambahan untuk uji coba antrean validasi FAQ
            [
                'nama'      => 'Nadia Putri',
                'email'     => 'nadia.putri@campus.ac.id',
                'npk'       => 'DTE-2024-012',
                'password'  => Hash::make('data123'),
                'tRoles_id' => 2,
                'is_active' => 1,
            ],
            [
                'nama'      => 'Aditya Pratama',
                'email'     => 'aditya.pratama@campus.ac.id',
                'npk'       => 'DTE-2024-015',
                'password'  => Hash::make('data123'),
                'tRoles_id' => 2,
                'is_active' => 1,
            ],
            [
                'nama'      => 'Dewi Lestari',
                'email'     => 'dewi.lestari@campus.ac.id',
                'npk'       => 'DTE-2024-018',
                'password'  => Hash::make('data123'),
                'tRoles_id' => 2,
                'is_active' => 1,
            ],
        ];
    }
}
