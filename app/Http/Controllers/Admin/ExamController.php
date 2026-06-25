<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\QuestionBank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function index(): View
    {
        $banks = QuestionBank::with('subject')->orderBy('judul')->get();
        return view('admin.exams.index', compact('banks'));
    }

    public function json(Request $request): JsonResponse
    {
        $exams = Exam::with('questionBank.subject')
            ->withCount('examQuestions as jumlah_soal')
            ->withSum('examQuestions as total_bobot', 'bobot_snapshot')
            ->when($request->filled('search'), fn ($q) =>
                $q->where('judul', 'like', "%{$request->search}%")
            )
            ->when($request->filled('status'), fn ($q) =>
                $q->where('status', $request->status)
            )
            ->latest()
            ->get();

        return response()->json($exams);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'judul'              => ['required', 'string', 'max:255'],
            'question_bank_id'   => ['required', 'exists:question_banks,id'],
            'target_kelas'       => ['required', 'in:X,XI,XII'],
            'target_tahun_masuk' => ['nullable', 'integer', 'min:2000'],
            'durasi_menit'       => ['required', 'integer', 'min:1', 'max:480'],
            'mulai_pada'         => ['nullable', 'date'],
            'selesai_pada'       => ['nullable', 'date', 'after_or_equal:mulai_pada'],
            'acak_soal'            => ['boolean'],
            'acak_opsi'            => ['boolean'],
            'auto_keluar'          => ['boolean'],
            'tampilkan_peringatan' => ['boolean'],
            'max_pelanggaran'      => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $exam = DB::transaction(function () use ($data) {
            $exam = Exam::create([
                ...$data,
                'token'  => Exam::generateToken(),
                'status' => 'draft',
            ]);

            // Snapshot soal dari bank
            $this->snapshotQuestions($exam);

            return $exam;
        });

        return response()->json($exam->load('questionBank'), 201);
    }

    public function update(Request $request, Exam $exam): JsonResponse
    {
        abort_if($exam->status === 'published', 422, 'Ujian yang sedang aktif tidak dapat diedit. Tutup dahulu.');

        $data = $request->validate([
            'judul'              => ['required', 'string', 'max:255'],
            'question_bank_id'   => ['required', 'exists:question_banks,id'],
            'target_kelas'       => ['required', 'in:X,XI,XII'],
            'target_tahun_masuk' => ['nullable', 'integer', 'min:2000'],
            'durasi_menit'       => ['required', 'integer', 'min:1', 'max:480'],
            'mulai_pada'         => ['nullable', 'date'],
            'selesai_pada'       => ['nullable', 'date', 'after_or_equal:mulai_pada'],
            'acak_soal'            => ['boolean'],
            'acak_opsi'            => ['boolean'],
            'auto_keluar'          => ['boolean'],
            'tampilkan_peringatan' => ['boolean'],
            'max_pelanggaran'      => ['nullable', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($exam, $data) {
            $exam->update($data);

            // Re-snapshot jika bank soal berubah
            if ((int) $data['question_bank_id'] !== $exam->question_bank_id) {
                $exam->examQuestions()->delete();
                $this->snapshotQuestions($exam->fresh());
            }
        });

        return response()->json($exam->fresh()->load('questionBank'));
    }

    public function updateStatus(Request $request, Exam $exam): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:draft,published,closed'],
        ]);

        DB::transaction(fn () => $exam->update(['status' => $data['status']]));

        return response()->json(['status' => $data['status']]);
    }

    public function monitor(Exam $exam): View
    {
        return view('admin.exams.monitor', compact('exam'));
    }

    public function monitorJson(Exam $exam): JsonResponse
    {
        $attempts = ExamAttempt::where('exam_id', $exam->id)
            ->with('student')
            ->where('is_void', false)
            ->orderByDesc('mulai_at')
            ->get()
            ->map(fn ($a) => [
                'id'                  => $a->id,
                'student_nama'        => $a->student->nama,
                'student_nis'         => $a->student->nis,
                'status'              => $a->status,
                'sisa_detik'          => $a->sisaDetik(),
                'jumlah_pelanggaran'  => $a->jumlah_pelanggaran,
                'total_skor'          => $a->total_skor,
            ]);

        $counts = [
            'berlangsung'       => $attempts->where('status', 'berlangsung')->count(),
            'selesai'           => $attempts->where('status', 'selesai')->count(),
            'dikeluarkan'       => $attempts->where('status', 'dikeluarkan')->count(),
            'total_pelanggaran' => $attempts->sum('jumlah_pelanggaran'),
        ];

        $recentCheats = \App\Models\CheatLog::whereHas('attempt', fn ($q) => $q->where('exam_id', $exam->id))
            ->with('attempt.student')
            ->latest('terjadi_at')
            ->take(20)
            ->get()
            ->map(fn ($log) => [
                'id'           => $log->id,
                'student_nama' => $log->attempt->student->nama,
                'jenis_label'  => $log->jenis_label,
                'terjadi_at'   => $log->terjadi_at->format('H:i:s'),
            ]);

        // Progres per soal: berapa siswa yang sudah menjawab masing-masing soal
        $totalQuestions = $exam->examQuestions()->count();
        $questionProgress = \App\Models\AttemptAnswer::whereHas('attempt', fn ($q) =>
                $q->where('exam_id', $exam->id)->where('is_void', false)
            )
            ->select('question_id', DB::raw('count(*) as jawab_count'))
            ->groupBy('question_id')
            ->get()
            ->keyBy('question_id')
            ->map(fn ($r) => $r->jawab_count);

        $soalProgress = $exam->examQuestions()
            ->with('question:id,pertanyaan')
            ->orderBy('urutan')
            ->get()
            ->map(fn ($eq) => [
                'urutan'      => $eq->urutan,
                'jawab_count' => $questionProgress[$eq->question_id] ?? 0,
            ]);

        return response()->json(compact('attempts', 'counts', 'recentCheats', 'soalProgress', 'totalQuestions'));
    }

    // -----------------------------------------------------------------------
    // Internal: snapshot soal ke exam_questions
    // -----------------------------------------------------------------------
    private function snapshotQuestions(Exam $exam): void
    {
        $questions = $exam->questionBank->questions()->get();

        $urutan = 1;
        foreach ($questions as $q) {
            ExamQuestion::create([
                'exam_id'        => $exam->id,
                'question_id'    => $q->id,
                'urutan'         => $urutan++,
                'bobot_snapshot' => $q->bobot,
            ]);
        }
    }
}
