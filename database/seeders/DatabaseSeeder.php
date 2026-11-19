<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\DirektoratSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\KataKunciSeeder;
use Database\Seeders\SumberPustakaSeeder;
use Database\Seeders\FaqSeeder;
use Database\Seeders\LogMahasiswaSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DirektoratSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            KataKunciSeeder::class,
            SumberPustakaSeeder::class,
            FaqSeeder::class,
            LogMahasiswaSeeder::class,
        ]);
    }
}
