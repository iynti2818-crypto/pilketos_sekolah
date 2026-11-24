<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class VoterSeeder extends Seeder
{
    public function run(): void
    {
        $voters = [
            ['nisn' => '2026000001', 'name' => 'Alya Putri', 'kelas' => 'X PPLG 1', 'birth_date' => '2008-02-14'],
            ['nisn' => '2026000002', 'name' => 'Bima Pratama', 'kelas' => 'X PPLG 1', 'birth_date' => '2008-05-21'],
            ['nisn' => '2026000003', 'name' => 'Citra Lestari', 'kelas' => 'X PPLG 2', 'birth_date' => '2008-08-03'],
            ['nisn' => '2026000004', 'name' => 'Daffa Rizky', 'kelas' => 'XI PPLG 1', 'birth_date' => '2007-01-19'],
            ['nisn' => '2026000005', 'name' => 'Eka Wulandari', 'kelas' => 'XI PPLG 2', 'birth_date' => '2007-04-27'],
            ['nisn' => '2026000006', 'name' => 'Farhan Maulana', 'kelas' => 'XI TKJ 1', 'birth_date' => '2007-07-11'],
            ['nisn' => '2026000007', 'name' => 'Gita Amelia', 'kelas' => 'XI TKJ 2', 'birth_date' => '2007-10-05'],
            ['nisn' => '2026000008', 'name' => 'Hafidz Ramadhan', 'kelas' => 'XII PPLG 1', 'birth_date' => '2006-03-16'],
            ['nisn' => '2026000009', 'name' => 'Intan Permata', 'kelas' => 'XII PPLG 2', 'birth_date' => '2006-06-22'],
            ['nisn' => '2026000010', 'name' => 'Jihan Aulia', 'kelas' => 'XII TKJ 1', 'birth_date' => '2006-09-30'],
        ];

        foreach ($voters as $voter) {
            User::updateOrCreate(
                ['nisn' => $voter['nisn']],
                [
                    'name' => $voter['name'],
                    'kelas' => $voter['kelas'],
                    'birth_date' => $voter['birth_date'],
                    'nis' => $voter['nisn'],
                    'role' => 'siswa',
                ],
            );
        }
    }
}
