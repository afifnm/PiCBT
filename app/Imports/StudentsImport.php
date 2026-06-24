<?php

namespace App\Imports;

use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class StudentsImport implements ToCollection, WithHeadingRow, WithBatchInserts, SkipsEmptyRows
{
    public int   $successCount = 0;
    public int   $failureCount = 0;
    public array $errors       = [];

    public function collection(Collection $rows): void
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $i => $row) {
                $data = [
                    'nis'          => trim((string) ($row['nis'] ?? '')),
                    'nama'         => trim((string) ($row['nama'] ?? '')),
                    'tahun_masuk'  => (int) ($row['tahun_masuk'] ?? 0),
                    'jurusan'      => trim((string) ($row['jurusan'] ?? '')),
                ];

                $validator = Validator::make($data, [
                    'nis'         => ['required', 'string', 'max:20'],
                    'nama'        => ['required', 'string', 'max:255'],
                    'tahun_masuk' => ['required', 'integer', 'min:2000', 'max:' . date('Y')],
                ]);

                if ($validator->fails()) {
                    $this->failureCount++;
                    $this->errors[] = "Baris " . ($i + 2) . ": " . implode(', ', $validator->errors()->all());
                    continue;
                }

                if (Student::where('nis', $data['nis'])->exists()) {
                    $this->failureCount++;
                    $this->errors[] = "Baris " . ($i + 2) . ": NIS {$data['nis']} sudah ada.";
                    continue;
                }

                Student::create([
                    'nis'         => $data['nis'],
                    'nama'        => $data['nama'],
                    'tahun_masuk' => $data['tahun_masuk'],
                    'jurusan'     => $data['jurusan'] ?: null,
                    'password'    => Hash::make($data['nis']),
                ]);

                $this->successCount++;
            }
        });
    }

    public function batchSize(): int
    {
        return 100;
    }
}
