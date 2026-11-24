<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportVoters extends Command
{
    protected $signature = 'pilketos:import-voters {file : CSV dengan header nisn,name,class,birth_date}';

    protected $description = 'Impor atau perbarui daftar siswa dari CSV tanpa mengubah status suara';

    public function handle(): int
    {
        $path = $this->argument('file');
        $file = @fopen($path, 'rb');

        if ($file === false) {
            $this->error('File CSV tidak dapat dibuka.');

            return self::FAILURE;
        }

        try {
            $headers = fgetcsv($file);
            if ($headers === false) {
                $this->error('File CSV kosong atau tidak memiliki header.');

                return self::FAILURE;
            }

            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]);
            if (array_map('trim', $headers) !== ['nisn', 'name', 'class', 'birth_date']) {
                $this->error('Header CSV harus berurutan: nisn,name,class,birth_date.');

                return self::FAILURE;
            }

            $rows = [];
            $seenNisn = [];
            $line = 1;

            while (($values = fgetcsv($file)) !== false) {
                $line++;
                if ($values === [null] || $values === []) {
                    continue;
                }

                if (count($values) !== 4) {
                    $this->error("Baris {$line} harus berisi tepat 4 kolom.");

                    return self::FAILURE;
                }

                [$nisn, $name, $kelas, $birthDate] = array_map(
                    static fn ($value) => trim((string) $value),
                    $values,
                );

                if (! preg_match('/^\d{10}$/D', $nisn)
                    || $name === ''
                    || $kelas === ''
                    || ! preg_match('/^\d{4}-\d{2}-\d{2}$/D', $birthDate)
                    || ! checkdate(
                        (int) substr($birthDate, 5, 2),
                        (int) substr($birthDate, 8, 2),
                        (int) substr($birthDate, 0, 4),
                    )) {
                    $this->error("Baris {$line} memiliki NISN, nama, kelas, atau tanggal lahir yang tidak valid.");

                    return self::FAILURE;
                }

                if (isset($seenNisn[$nisn])) {
                    $this->error("NISN duplikat pada file CSV: {$nisn}.");

                    return self::FAILURE;
                }

                $seenNisn[$nisn] = true;
                $rows[] = [
                    'nisn' => $nisn,
                    'name' => $name,
                    'kelas' => $kelas,
                    'birth_date' => $birthDate,
                    'role' => 'siswa',
                    'has_voted' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        } finally {
            fclose($file);
        }

        if ($rows === []) {
            $this->error('Tidak ada data siswa untuk diimpor.');

            return self::FAILURE;
        }

        $conflictingUsers = User::query()
            ->whereIn('nisn', array_column($rows, 'nisn'))
            ->where('role', '!=', 'siswa')
            ->exists();

        if ($conflictingUsers) {
            $this->error('Salah satu NISN pada CSV sudah digunakan oleh akun non-siswa.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($rows): void {
            foreach (array_chunk($rows, 500) as $chunk) {
                User::upsert($chunk, ['nisn'], ['name', 'kelas', 'birth_date', 'updated_at']);
            }
        });

        $this->info(count($rows).' data siswa berhasil diimpor atau diperbarui.');

        return self::SUCCESS;
    }
}
