<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataku = [
            [
                'nama' => 'Admin KMS',
                'email' => 'adminkms@gmail.com',
                'password' => Hash::make('admin123'),
                'tRoles_id' => 1,
                'is_active' => 1
            ],
            [
                'nama' => 'Data Entry KMS',
                'email' => 'dataentrykms@gmail.com',
                'password' => Hash::make('data123'),
                'tRoles_id' => 2,
                'is_active' => 1
            ],
            [
                'nama' => 'Validator KMS',
                'email' => 'validatorkms@gmail.com',
                'password' => Hash::make('validator123'),
                'tRoles_id' => 3,
                'is_active' => 1
            ],
        ];

        User::insert($dataku);
    }
}
