<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttemptAnswer;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ResultsExport;

class ResultController extends Controller
{
    public function index(): View
    {
        $exams = Exam::with('questionBank.subject')
            ->whereIn('status', ['published', 'closed'])
            ->orderByDesc('created_at')
            ->get();

        return view('admin.results.index', compact('exams'));
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

    // Export Excel
    public function exportExcel(Request $request)
    {
        $request->validate(['exam_id' => ['required', 'exists:exams,id']]);
        $exam = Exam::findOrFail($request->exam_id);
        $filename = 'rekap_' . str($exam->judul)->slug() . '_' . now()->format('Ymd') . '.xlsx';
        return Excel::download(new ResultsExport($exam->id), $filename);
    }

    // Export PDF
    public function exportPdf(Request $request)
    {
        $request->validate(['exam_id' => ['required', 'exists:exams,id']]);

        $exam = Exam::with('questionBank.subject')->findOrFail($request->exam_id);

        $attempts = ExamAttempt::where('exam_id', $exam->id)
            ->where('is_void', false)
            ->with('student')
            ->orderBy('total_skor', 'desc')
            ->get();

        $pdf = Pdf::loadView('admin.results.pdf', compact('exam', 'attempts'))
            ->setPaper('a4', 'portrait');

        $filename = 'rekap_' . str($exam->judul)->slug() . '_' . now()->format('Ymd') . '.pdf';
        return $pdf->download($filename);
    }
}
