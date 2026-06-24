<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['kode' => 'MTK',  'nama' => 'Matematika'],
            ['kode' => 'BIN',  'nama' => 'Bahasa Indonesia'],
            ['kode' => 'BIG',  'nama' => 'Bahasa Inggris'],
            ['kode' => 'IPA',  'nama' => 'Ilmu Pengetahuan Alam'],
            ['kode' => 'PKN',  'nama' => 'Pendidikan Kewarganegaraan'],
            ['kode' => 'TKJ',  'nama' => 'Teknik Komputer & Jaringan'],
            ['kode' => 'RPL',  'nama' => 'Rekayasa Perangkat Lunak'],
        ];

        foreach ($subjects as $subject) {
            Subject::firstOrCreate(['kode' => $subject['kode']], $subject);
        }
    }
}
