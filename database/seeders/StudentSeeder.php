<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $students = [
            // Kelas X (masuk 2025 → kelas X sampai Juli 2026)
            ['nis' => '2025001', 'nama' => 'Ahmad Fauzi',       'tahun_masuk' => 2025, 'jurusan' => 'TKJ'],
            ['nis' => '2025002', 'nama' => 'Bela Safitri',      'tahun_masuk' => 2025, 'jurusan' => 'TKJ'],
            ['nis' => '2025003', 'nama' => 'Cahya Purnama',     'tahun_masuk' => 2025, 'jurusan' => 'RPL'],
            ['nis' => '2025004', 'nama' => 'Dewi Anggraini',    'tahun_masuk' => 2025, 'jurusan' => 'RPL'],
            ['nis' => '2025005', 'nama' => 'Eko Prasetyo',      'tahun_masuk' => 2025, 'jurusan' => 'AK'],

            // Kelas XI (masuk 2024)
            ['nis' => '2024001', 'nama' => 'Fajar Nugroho',     'tahun_masuk' => 2024, 'jurusan' => 'TKJ'],
            ['nis' => '2024002', 'nama' => 'Gita Permata',      'tahun_masuk' => 2024, 'jurusan' => 'RPL'],
            ['nis' => '2024003', 'nama' => 'Hendra Wijaya',     'tahun_masuk' => 2024, 'jurusan' => 'AK'],

            // Kelas XII (masuk 2023)
            ['nis' => '2023001', 'nama' => 'Indah Lestari',     'tahun_masuk' => 2023, 'jurusan' => 'TKJ'],
            ['nis' => '2023002', 'nama' => 'Joko Susanto',      'tahun_masuk' => 2023, 'jurusan' => 'RPL'],
        ];

        foreach ($students as $data) {
            Student::firstOrCreate(
                ['nis' => $data['nis']],
                array_merge($data, ['password' => Hash::make('siswa123')])
            );
        }
    }
}
