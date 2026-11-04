<?php

namespace Database\Seeders;

use App\Models\Direktorat;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil ID dari tabel tdirektorat (Direktorat Akademik = 1, dst.)
        $dirAkademik = Direktorat::where('nama', 'Direktorat Akademik')->value('id') ?? 1;
        $dirTeknik   = Direktorat::where('nama', 'like', '%Teknik%')->value('id') ?? $dirAkademik;
        $dirFbe      = Direktorat::where('nama', 'like', '%Bisnis%')->value('id') ?? $dirAkademik;
        $dirHukum    = Direktorat::where('nama', 'like', '%Hukum%')->value('id') ?? $dirAkademik;

        $users = [
            [
                'nama'           => 'Admin KMS',
                'email'          => 'adminkms@gmail.com',
                'npk'            => 'ADM-2024-001',
                'password'       => Hash::make('admin123'),
                'tRoles_id'      => 1,
                'tDirektorat_id' => $dirAkademik,
                'is_active'      => 1,
            ],
            [
                'nama'           => 'Data Entry KMS',
                'email'          => 'dataentrykms@gmail.com',
                'npk'            => 'DTE-2024-001',
                'password'       => Hash::make('data123'),
                'tRoles_id'      => 2,
                'tDirektorat_id' => $dirAkademik,
                'is_active'      => 1,
            ],
            [
                'nama'           => 'Validator KMS',
                'email'          => 'validatorkms@gmail.com',
                'npk'            => 'VAL-2024-001',
                'password'       => Hash::make('validator123'),
                'tRoles_id'      => 3,
                'tDirektorat_id' => $dirAkademik,
                'is_active'      => 1,
            ],
            // Staf Data Entry tambahan untuk uji coba antrean validasi FAQ
            [
                'nama'           => 'Nadia Putri',
                'email'          => 'nadia.putri@campus.ac.id',
                'npk'            => 'DTE-2024-012',
                'password'       => Hash::make('data123'),
                'tRoles_id'      => 2,
                'tDirektorat_id' => $dirTeknik,
                'is_active'      => 1,
            ],
            [
                'nama'           => 'Aditya Pratama',
                'email'          => 'aditya.pratama@campus.ac.id',
                'npk'            => 'DTE-2024-015',
                'password'       => Hash::make('data123'),
                'tRoles_id'      => 2,
                'tDirektorat_id' => $dirFbe,
                'is_active'      => 1,
            ],
            [
                'nama'           => 'Dewi Lestari',
                'email'          => 'dewi.lestari@campus.ac.id',
                'npk'            => 'DTE-2024-018',
                'password'       => Hash::make('data123'),
                'tRoles_id'      => 2,
                'tDirektorat_id' => $dirHukum,
                'is_active'      => 1,
            ],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                $u
            );
        }
    }
}
