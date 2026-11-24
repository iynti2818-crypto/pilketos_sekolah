<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
        * Mengisi basis data dengan akun contoh.
     */
    public function run(): void
    {
        $this->call(VoterSeeder::class);

        User::updateOrCreate(
            ['email' => 'admin@sekolah.com'],
            [
                'name' => 'Admin Sekolah',
                'password' => 'admin123',
                'role' => 'admin',
                'nis' => null,
            ]
        );

        foreach ([
            ['name' => 'Budi Siswa', 'email' => 'siswa1@sekolah.com', 'nis' => '2026001'],
            ['name' => 'Siti Siswa', 'email' => 'siswa2@sekolah.com', 'nis' => '2026002'],
            ['name' => 'Andi Siswa', 'email' => 'siswa3@sekolah.com', 'nis' => '2026003'],
        ] as $siswa) {
            User::updateOrCreate(
                ['email' => $siswa['email']],
                [...$siswa, 'password' => 'siswa123', 'role' => 'siswa']
            );
        }
    }
}
