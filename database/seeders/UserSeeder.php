<?php

namespace Database\Seeders;

use App\Models\Direktorat;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds (Hanya 3 akun inti: SuperAdminKMS, DataEntryKMS, ValidatorKMS).
     */
    public function run(): void
    {
        $dirAkademik = Direktorat::where('is_pusat', true)->value('id')
            ?? Direktorat::where('nama', 'Direktorat Akademik')->value('id')
            ?? 1;

        $users = [
            [
                'nama'               => 'SuperAdminKMS',
                'email'              => 'adminkms@gmail.com',
                'npk'                => 'ADM-2024-001',
                'password'           => Hash::make('admin123'),
                'password_encrypted' => Crypt::encryptString('admin123'),
                'tRoles_id'          => 1,
                'tDirektorat_id'     => $dirAkademik,
                'is_active'          => 1,
            ],
            [
                'nama'               => 'DataEntryKMS',
                'email'              => 'dataentrykms@gmail.com',
                'npk'                => 'DTE-2024-001',
                'password'           => Hash::make('data123'),
                'password_encrypted' => Crypt::encryptString('data123'),
                'tRoles_id'          => 2,
                'tDirektorat_id'     => $dirAkademik,
                'is_active'          => 1,
            ],
            [
                'nama'               => 'ValidatorKMS',
                'email'              => 'validatorkms@gmail.com',
                'npk'                => 'VAL-2024-001',
                'password'           => Hash::make('validator123'),
                'password_encrypted' => Crypt::encryptString('validator123'),
                'tRoles_id'          => 3,
                'tDirektorat_id'     => $dirAkademik,
                'is_active'          => 1,
            ],
        ];

        $keepEmails = array_column($users, 'email');

        foreach ($users as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                $u
            );
        }

        // Hapus user lain selain 3 akun inti apabila tidak lagi mereferensikan data aktif
        try {
            User::whereNotIn('email', $keepEmails)->delete();
        } catch (\Throwable $e) {
            // Abaikan bila masih ada FK pada database non-fresh; migrate:fresh --seed akan membersihkan total
        }
    }
}
