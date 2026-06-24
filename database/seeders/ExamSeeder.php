<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\QuestionBank;
use Illuminate\Database\Seeder;

class ExamSeeder extends Seeder
{
    public function run(): void
    {
        $bankMtk = QuestionBank::where('judul', 'Matematika Dasar Kelas X')->first();
        $bankRpl = QuestionBank::where('judul', 'Dasar Pemrograman Web Kelas XI')->first();

        // ----------------------------------------------------------------
        // Ujian 1: Matematika untuk kelas X (aktif sekarang)
        // ----------------------------------------------------------------
        $examMtk = Exam::firstOrCreate(
            ['judul' => 'UH Matematika Dasar — Kelas X'],
            [
                'question_bank_id'   => $bankMtk->id,
                'token'              => Exam::generateToken(),
                'target_kelas'       => 'X',
                'durasi_menit'       => 60,
                'acak_soal'          => true,
                'acak_opsi'          => false,
                'mulai_pada'         => now()->subHour(),
                'selesai_pada'       => now()->addHours(5),
                'max_pelanggaran'    => 3,
                'auto_keluar'        => true,
                'status'             => 'published',
            ]
        );

        if ($examMtk->wasRecentlyCreated) {
            $this->attachQuestions($examMtk, $bankMtk);
        }

        // ----------------------------------------------------------------
        // Ujian 2: RPL untuk kelas XI (masih draft)
        // ----------------------------------------------------------------
        $examRpl = Exam::firstOrCreate(
            ['judul' => 'UH Pemrograman Web — Kelas XI'],
            [
                'question_bank_id'   => $bankRpl->id,
                'token'              => Exam::generateToken(),
                'target_kelas'       => 'XI',
                'durasi_menit'       => 45,
                'acak_soal'          => false,
                'acak_opsi'          => false,
                'mulai_pada'         => now()->addDay(),
                'selesai_pada'       => now()->addDay()->addHours(3),
                'max_pelanggaran'    => null,
                'auto_keluar'        => false,
                'status'             => 'draft',
            ]
        );

        if ($examRpl->wasRecentlyCreated) {
            $this->attachQuestions($examRpl, $bankRpl);
        }
    }

    private function attachQuestions(Exam $exam, QuestionBank $bank): void
    {
        $questions = $bank->questions()->get();

        foreach ($questions as $q) {
            ExamQuestion::firstOrCreate(
                ['exam_id' => $exam->id, 'question_id' => $q->id],
                [
                    'urutan'         => $q->urutan,
                    'bobot_snapshot' => $q->bobot,
                ]
            );
        }
    }
}
