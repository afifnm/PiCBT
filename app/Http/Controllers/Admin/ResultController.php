<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttemptAnswer;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Student;
use App\Services\GeminiScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ResultsExport;

class ResultController extends Controller
{
    public function index(): View
    {
        $exams = Exam::with('questionBank.subject')
            ->withCount(['attempts as peserta_count' => fn ($q) => $q->where('is_void', false)])
            ->whereIn('status', ['published', 'closed'])
            ->orderByDesc('created_at')
            ->get();

        // Annotate each exam with count of essay answers needing correction
        $examIds = $exams->pluck('id');
        $perluMap = AttemptAnswer::join('exam_attempts', 'attempt_answers.exam_attempt_id', '=', 'exam_attempts.id')
            ->whereIn('exam_attempts.exam_id', $examIds)
            ->where('exam_attempts.is_void', false)
            ->whereNull('attempt_answers.dinilai_oleh')
            ->whereHas('question', fn ($q) => $q->where('tipe', 'esai'))
            ->groupBy('exam_attempts.exam_id')
            ->selectRaw('exam_attempts.exam_id, count(*) as cnt')
            ->pluck('cnt', 'exam_id');

        $exams->each(fn ($e) => $e->perlu_koreksi_count = (int) ($perluMap[$e->id] ?? 0));

        return view('admin.results.index', compact('exams'));
    }

    // Rekap nilai per ujian — halaman tersendiri (dibuka di tab baru)
    public function exam(Exam $exam): View
    {
        $exam->load(['questionBank.subject', 'examQuestions']);
        $exam->total_bobot = $exam->examQuestions->sum('bobot_snapshot');

        $attempts = ExamAttempt::where('exam_id', $exam->id)
            ->where('is_void', false)
            ->with(['student', 'answers'])
            ->orderBy('total_skor', 'desc')
            ->get();

        $totalBobot = $exam->examQuestions->sum('bobot_snapshot');

        $validSkor = $attempts->whereNotNull('total_skor');
        $stats = [
            'total'       => $attempts->count(),
            'selesai'     => $attempts->where('status', 'selesai')->count(),
            'dikeluarkan' => $attempts->where('status', 'dikeluarkan')->count(),
            'rata_rata'   => round($validSkor->avg('total_skor') ?? 0, 1),
            'tertinggi'   => $validSkor->max('total_skor') ?? 0,
            'terendah'    => $validSkor->min('total_skor') ?? 0,
            'total_bobot' => $totalBobot,
        ];

        return view('admin.results.exam', compact('exam', 'attempts', 'stats'));
    }

    // JSON rekap per ujian: daftar peserta + skor
    public function json(Request $request): JsonResponse
    {
        $request->validate(['exam_id' => ['required', 'exists:exams,id']]);

        $exam = Exam::with('questionBank.subject')->findOrFail($request->exam_id);

        $attempts = ExamAttempt::where('exam_id', $exam->id)
            ->where('is_void', false)
            ->with(['student', 'answers'])
            ->orderBy('total_skor', 'desc')
            ->get()
            ->map(function ($a) use ($exam) {
                $perlu = $a->answers
                    ->filter(fn ($ans) => $ans->dinilai_oleh === null)
                    ->count();

                return [
                    'id'                  => $a->id,
                    'student_id'          => $a->student_id,
                    'nis'                 => $a->student->nis,
                    'nama'                => $a->student->nama,
                    'kelas'               => $a->student->kelas_sekarang,
                    'status'              => $a->status,
                    'total_skor'          => $a->total_skor,
                    'jumlah_pelanggaran'  => $a->jumlah_pelanggaran,
                    'perlu_koreksi'       => $perlu,
                    'selesai_at'          => $a->selesai_at?->toISOString(),
                ];
            });

        $stats = [
            'total'      => $attempts->count(),
            'selesai'    => $attempts->where('status', 'selesai')->count(),
            'dikeluarkan'=> $attempts->where('status', 'dikeluarkan')->count(),
            'rata_rata'  => round($attempts->whereNotNull('total_skor')->avg('total_skor') ?? 0, 1),
            'tertinggi'  => $attempts->whereNotNull('total_skor')->max('total_skor') ?? 0,
            'terendah'   => $attempts->whereNotNull('total_skor')->min('total_skor') ?? 0,
            'total_bobot'=> $exam->examQuestions()->sum('bobot_snapshot'),
        ];

        return response()->json(compact('exam', 'attempts', 'stats'));
    }

    // Detail jawaban per siswa untuk koreksi manual esai
    public function detail(ExamAttempt $attempt): View
    {
        abort_if($attempt->is_void, 404);

        $attempt->load([
            'student',
            'exam.examQuestions.question.options',
            'answers',
            'cheatLogs',
        ]);

        $answersByQuestion = $attempt->answers->keyBy('question_id');

        return view('admin.results.detail', compact('attempt', 'answersByQuestion'));
    }

    // Reset attempt siswa — hapus jawaban, cheat log, dan attempt itu sendiri
    public function resetAttempt(ExamAttempt $attempt): JsonResponse
    {
        $examId = $attempt->exam_id;

        DB::transaction(function () use ($attempt) {
            $attempt->answers()->delete();
            $attempt->cheatLogs()->delete();
            $attempt->delete();
        });

        return response()->json(['ok' => true, 'exam_id' => $examId]);
    }

    // Reset semua attempt dalam satu ujian
    public function resetAllAttempts(Exam $exam): JsonResponse
    {
        DB::transaction(function () use ($exam) {
            $attemptIds = $exam->attempts()->pluck('id');
            AttemptAnswer::whereIn('exam_attempt_id', $attemptIds)->delete();
            \App\Models\CheatLog::whereIn('exam_attempt_id', $attemptIds)->delete();
            $exam->attempts()->delete();
        });

        return response()->json(['ok' => true]);
    }

    // Override skor esai oleh guru
    public function overrideScore(Request $request, AttemptAnswer $answer): JsonResponse
    {
        $data = $request->validate([
            'skor'     => ['required', 'numeric', 'min:0'],
            'feedback' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($answer, $data) {
            $answer->lockForUpdate();
            $answer->overrideSkor((float) $data['skor'], $data['feedback'] ?? '');
        });

        return response()->json([
            'skor'         => $answer->fresh()->skor,
            'total_skor'   => $answer->attempt->fresh()->total_skor,
            'dinilai_oleh' => 'manual',
        ]);
    }

    // Koreksi AI untuk satu jawaban esai
    public function aiScore(AttemptAnswer $answer, GeminiScoringService $gemini): JsonResponse
    {
        if ($answer->question->tipe !== 'esai') {
            return response()->json(['message' => 'Hanya untuk jawaban esai.'], 422);
        }

        // Jangan koreksi ulang esai yang sudah dinilai (AI maupun manual)
        if ($answer->dinilai_oleh !== null) {
            return response()->json([
                'message'      => 'Jawaban ini sudah dikoreksi.',
                'skor'         => $answer->skor,
                'ai_feedback'  => $answer->ai_feedback,
                'dinilai_oleh' => $answer->dinilai_oleh,
            ], 422);
        }

        $ok = DB::transaction(function () use ($answer, $gemini) {
            $answer->refresh()->lockForUpdate();
            return $gemini->scoreAnswer($answer);
        });

        $answer->refresh();

        return response()->json([
            'ok'           => $ok,
            'skor'         => $answer->skor,
            'ai_feedback'  => $answer->ai_feedback,
            'dinilai_oleh' => $answer->dinilai_oleh,
        ], $ok ? 200 : 422);
    }

    // Export Excel
    public function exportExcel(Request $request)
    {
        $request->validate(['exam_id' => ['required', 'exists:exams,id']]);
        $exam = Exam::findOrFail($request->exam_id);
        $filename = 'rekap_' . str($exam->judul)->slug() . '_' . now()->format('Ymd') . '.xlsx';
        return Excel::download(new ResultsExport($exam->id), $filename);
    }

    // Cetak rekap — halaman HTML standalone untuk print
    public function printResults(Request $request)
    {
        $request->validate(['exam_id' => ['required', 'exists:exams,id']]);

        $exam = Exam::with(['questionBank.subject', 'examQuestions'])->findOrFail($request->exam_id);
        $exam->total_bobot = $exam->examQuestions->sum('bobot_snapshot');

        $attempts = ExamAttempt::where('exam_id', $exam->id)
            ->where('is_void', false)
            ->with('student')
            ->orderBy('total_skor', 'desc')
            ->get();

        return view('admin.results.print', compact('exam', 'attempts'));
    }
}
