<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VoterImportController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ], [
            'csv_file.required' => 'Silakan pilih file CSV.',
            'csv_file.mimes' => 'File harus berformat CSV.',
            'csv_file.max' => 'Ukuran file maksimal 10 MB.',
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            return back()->with('error', 'File CSV tidak dapat dibuka.');
        }

        try {
            $headers = fgetcsv($handle);
            if ($headers === false) {
                return back()->with('error', 'File CSV kosong.');
            }

            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]);
            $headers = array_map(
                static fn ($header) => strtolower(trim((string) $header)),
                $headers,
            );

            if (count($headers) !== 4
                || $headers[0] !== 'nisn'
                || $headers[1] !== 'name'
                || ! in_array($headers[2], ['class', 'kelas'], true)
                || $headers[3] !== 'birth_date') {
                return back()->with('error', 'Header CSV harus berurutan: nisn,name,class,birth_date.');
            }

            $imported = 0;

            DB::transaction(function () use ($handle, &$imported): void {
                $line = 1;

                while (($row = fgetcsv($handle)) !== false) {
                    $line++;

                    if ($row === [] || $row === [null]) {
                        continue;
                    }

                    if (count($row) !== 4) {
                        throw new \InvalidArgumentException("Baris {$line} harus berisi tepat 4 kolom.");
                    }

                    [$nisn, $name, $class, $birthDate] = array_map(
                        static fn ($value) => trim((string) $value),
                        $row,
                    );

                    if (! preg_match('/^\d{10}$/D', $nisn)
                        || $name === ''
                        || $class === ''
                        || ! preg_match('/^\d{4}-\d{2}-\d{2}$/D', $birthDate)
                        || ! checkdate(
                            (int) substr($birthDate, 5, 2),
                            (int) substr($birthDate, 8, 2),
                            (int) substr($birthDate, 0, 4),
                        )) {
                        throw new \InvalidArgumentException("Baris {$line} memiliki NISN, nama, kelas, atau tanggal lahir yang tidak valid.");
                    }

                    User::updateOrCreate(
                        ['nisn' => $nisn],
                        [
                            'name' => $name,
                            'kelas' => $class,
                            'birth_date' => $birthDate,
                            'nis' => $nisn,
                            'role' => 'siswa',
                        ],
                    );

                    $imported++;
                }
            });
        } catch (\InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        } finally {
            fclose($handle);
        }

        if ($imported === 0) {
            return back()->with('error', 'Tidak ada data siswa untuk diimpor.');
        }

        return redirect('/admin')->with('success', "Berhasil mengimpor {$imported} data pemilih!");
    }

    public function template(): StreamedResponse
    {
        return response()->streamDownload(
            function (): void {
                $handle = fopen('php://output', 'wb');
                fputcsv($handle, ['nisn', 'name', 'class', 'birth_date']);
                fputcsv($handle, ['2026000001', 'Contoh Nama Siswa', 'XII PPLG 1', '2006-02-14']);
                fclose($handle);
            },
            'template-data-pemilih.csv',
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ],
        );
    }
}
