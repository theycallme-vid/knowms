<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataku = [
            ['nama' => 'Super Admin'],
            ['nama' => 'Data Entry'],
            ['nama' => 'Validator'],
        ];

        Role::insert($dataku);
    }

    
}
