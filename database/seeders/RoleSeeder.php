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
        $roles = [
            ['id' => 1, 'nama' => 'Super Admin'],
            ['id' => 2, 'nama' => 'Data Entry'],
            ['id' => 3, 'nama' => 'Validator'],
        ];
    }
}
