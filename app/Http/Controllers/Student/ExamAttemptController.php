<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Jobs\ScoreAttemptEssaysJob;
use App\Models\AttemptAnswer;
use App\Models\CheatLog;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExamAttemptController extends Controller
{
    // ------------------------------------------------------------------
    // Show exam page (GET /exam/{examId}/take)
    // ------------------------------------------------------------------
    public function show(int $examId): View|RedirectResponse
    {
        $student = Auth::guard('student')->user();

        $attempt = ExamAttempt::with([
            'exam.examQuestions.question.options',
        ])
        ->where('exam_id', $examId)
        ->where('student_id', $student->id)
        ->where('is_void', false)
        ->firstOrFail();

        if (in_array($attempt->status, ['selesai', 'dibatalkan', 'dikeluarkan'])) {
            return redirect()->route('student.dashboard')
                ->with('info', 'Ujian ini sudah selesai.');
        }

        $exam      = $attempt->exam;
        $questions = $exam->examQuestions->sortBy('urutan');

        if ($exam->acak_soal) {
            $questions = $questions->shuffle();
        }

        if ($exam->acak_opsi) {
            $questions = $questions->map(function ($eq) {
                $eq->question->setRelation('options', $eq->question->options->shuffle());
                return $eq;
            });
        }

        $answers = AttemptAnswer::where('exam_attempt_id', $attempt->id)
            ->get()
            ->keyBy('question_id');

        $sisaDetik = (int) max(0, now()->diffInSeconds($attempt->batas_waktu_at, false));

        return view('exam.take', compact('attempt', 'questions', 'answers', 'sisaDetik'));
    }

    // ------------------------------------------------------------------
    // Review jawaban setelah ujian (GET /siswa/riwayat/{attempt})
    // ------------------------------------------------------------------
    public function review(int $attemptId): View|RedirectResponse
    {
        $student = Auth::guard('student')->user();

        $attempt = ExamAttempt::with([
            'exam.examQuestions.question.options',
            'answers.question.options',
        ])
        ->where('id', $attemptId)
        ->where('student_id', $student->id)
        ->where('is_void', false)
        ->whereIn('status', ['selesai', 'dikeluarkan'])
        ->firstOrFail();

        $answers = $attempt->answers->keyBy('question_id');
        $questions = $attempt->exam->examQuestions->sortBy('urutan');

        return view('student.review', compact('attempt', 'questions', 'answers'));
    }

    // ------------------------------------------------------------------
    // Save / update single answer  (POST /exam/attempt/{id}/answer/{questionId})
    // ------------------------------------------------------------------
    public function saveAnswer(Request $request, int $attemptId, int $questionId): JsonResponse
    {
        $student = Auth::guard('student')->user();

        $data = $request->validate([
            'jawaban_pg'   => ['nullable', 'string', 'max:5'],
            'jawaban_esai' => ['nullable', 'string', 'max:20000'],
        ]);

        DB::transaction(function () use ($attemptId, $questionId, $data, $student) {
            $attempt = ExamAttempt::where('id', $attemptId)
                ->where('student_id', $student->id)
                ->where('status', 'berlangsung')
                ->lockForUpdate()
                ->firstOrFail();

            abort_if(now()->gt($attempt->batas_waktu_at), 422, 'Waktu ujian sudah habis.');

            ExamQuestion::where('exam_id', $attempt->exam_id)
                ->where('question_id', $questionId)
                ->firstOrFail();

            AttemptAnswer::updateOrCreate(
                ['exam_attempt_id' => $attemptId, 'question_id' => $questionId],
                array_filter([
                    'jawaban_pg'   => $data['jawaban_pg']   ?? null,
                    'jawaban_esai' => $data['jawaban_esai'] ?? null,
                ], fn ($v) => $v !== null),
            );
        });

        return response()->json(['ok' => true]);
    }

    // ------------------------------------------------------------------
    // Heartbeat — sync sisa waktu + status (POST /exam/attempt/{id}/heartbeat)
    // ------------------------------------------------------------------
    public function heartbeat(int $attemptId): JsonResponse
    {
        $student = Auth::guard('student')->user();

        $attempt = ExamAttempt::where('id', $attemptId)
            ->where('student_id', $student->id)
            ->firstOrFail();

        if ($attempt->status !== 'berlangsung') {
            return response()->json([
                'status' => $attempt->status,
                'reason' => $attempt->void_reason,
            ]);
        }

        $sisaDetik = (int) max(0, now()->diffInSeconds($attempt->batas_waktu_at, false));

        if ($sisaDetik <= 0) {
            $this->_doSubmit($attempt, 'auto');
            return response()->json(['status' => 'selesai', 'sisa_detik' => 0]);
        }

        return response()->json([
            'status'          => 'berlangsung',
            'sisa_detik'      => $sisaDetik,
            'violation_count' => $attempt->jumlah_pelanggaran,
        ]);
    }

    // ------------------------------------------------------------------
    // Log cheat event  (POST /exam/attempt/{id}/cheat)
    // ------------------------------------------------------------------
    public function logCheat(Request $request, int $attemptId): JsonResponse
    {
        $student = Auth::guard('student')->user();

        $data = $request->validate([
            'jenis'      => ['required', 'string', 'max:50'],
            'detail'     => ['nullable', 'array'],
            'terjadi_at' => ['nullable', 'date'],
        ]);

        DB::transaction(function () use ($attemptId, $data, $student) {
            $attempt = ExamAttempt::where('id', $attemptId)
                ->where('student_id', $student->id)
                ->where('status', 'berlangsung')
                ->lockForUpdate()
                ->firstOrFail();

            CheatLog::create([
                'exam_attempt_id' => $attempt->id,
                'jenis'           => $data['jenis'],
                'detail'          => $data['detail'] ?? null,
                'terjadi_at'      => $data['terjadi_at'] ?? now(),
            ]);

            $attempt->increment('jumlah_pelanggaran');

            // Reload to get fresh count
            $attempt->refresh();

            $exam = $attempt->exam;
            if (
                $exam->auto_keluar &&
                $exam->max_pelanggaran > 0 &&
                $attempt->jumlah_pelanggaran >= $exam->max_pelanggaran
            ) {
                $this->_doSubmit($attempt, 'dikeluarkan', 'Batas pelanggaran tercapai.');
            }
        });

        return response()->json(['ok' => true, 'count' => $attemptId]);
    }

    // ------------------------------------------------------------------
    // Final submit  (POST /exam/attempt/{id}/submit)
    // ------------------------------------------------------------------
    public function submit(Request $request, int $attemptId): JsonResponse
    {
        $student = Auth::guard('student')->user();

        $data = $request->validate([
            'mode'   => ['nullable', 'in:manual,auto'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $result = DB::transaction(function () use ($attemptId, $data, $student) {
            $attempt = ExamAttempt::where('id', $attemptId)
                ->where('student_id', $student->id)
                ->whereIn('status', ['berlangsung'])
                ->lockForUpdate()
                ->firstOrFail();

            return $this->_doSubmit($attempt, $data['mode'] ?? 'manual', $data['reason'] ?? null);
        });

        return response()->json($result);
    }

    // ------------------------------------------------------------------
    // Internal submit helper — call inside transaction with lockForUpdate
    // ------------------------------------------------------------------
    private function _doSubmit(ExamAttempt $attempt, string $mode, ?string $reason = null): array
    {
        if (! in_array($attempt->status, ['berlangsung'])) {
            return ['status' => $attempt->status];
        }

        $newStatus = $mode === 'dikeluarkan' ? 'dikeluarkan' : 'selesai';

        // Auto-score PG answers
        $pgSkor = $this->_scorePG($attempt);

        $attempt->update([
            'status'      => $newStatus,
            'selesai_at'  => now(),
            'total_skor'  => $pgSkor,
            'void_reason' => $reason,
        ]);

        // Dispatch single job yang menskor semua essay attempt sekaligus (1 job per attempt)
        ScoreAttemptEssaysJob::dispatch($attempt->id);

        return ['status' => $newStatus, 'reason' => $reason];
    }

    private function _scorePG(ExamAttempt $attempt): float
    {
        $answers = AttemptAnswer::where('exam_attempt_id', $attempt->id)
            ->whereNotNull('jawaban_pg')
            ->with('question.options')
            ->get();

        if ($answers->isEmpty()) {
            return 0.0;
        }

        $rows  = [];
        $total = 0.0;

        foreach ($answers as $answer) {
            $correct = $answer->question->options
                ->firstWhere('is_correct', true)?->label;

            $skor = ($correct && $answer->jawaban_pg === $correct)
                ? (float) $answer->question->bobot
                : 0.0;

            $rows[]  = ['id' => $answer->id, 'skor' => $skor, 'dinilai_oleh' => 'ai'];
            $total  += $skor;
        }

        // Single bulk UPDATE instead of N individual UPDATEs
        AttemptAnswer::upsert($rows, ['id'], ['skor', 'dinilai_oleh']);

        return $total;
    }
}
