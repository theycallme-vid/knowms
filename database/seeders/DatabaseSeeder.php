<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\KategoriSeeder;
use Database\Seeders\BarangSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            KategoriSeeder::class,
            BarangSeeder::class,
            PegawaiSeeder::class,
            PelangganSeeder::class,
            NotaSeeder::class,
            DetailNotaSeeder::class
    ]); // bisa menambahkan seeder baru


    }
}

