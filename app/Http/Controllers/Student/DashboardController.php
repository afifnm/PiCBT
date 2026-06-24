<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $student = Auth::guard('student')->user();
        $kelas   = $student->kelas_sekarang;

        // Ujian yang tersedia untuk kelas siswa saat ini
        $availableExams = Exam::published()
            ->activeWindow()
            ->where('target_kelas', $kelas)
            ->where(fn ($q) =>
                $q->whereNull('target_tahun_masuk')
                  ->orWhere('target_tahun_masuk', $student->tahun_masuk)
            )
            ->with('questionBank.subject')
            ->get()
            ->map(function ($exam) use ($student) {
                $attempt = ExamAttempt::where('exam_id', $exam->id)
                    ->where('student_id', $student->id)
                    ->where('is_void', false)
                    ->first();

                return [
                    'exam'    => $exam,
                    'attempt' => $attempt,
                ];
            });

        // Riwayat ujian selesai
        $history = ExamAttempt::where('student_id', $student->id)
            ->whereIn('status', ['selesai', 'dikeluarkan'])
            ->where('is_void', false)
            ->with('exam.questionBank.subject')
            ->latest('selesai_at')
            ->take(10)
            ->get();

        return view('student.dashboard', compact('student', 'availableExams', 'history', 'kelas'));
    }

    public function startExam(int $examId): View|RedirectResponse
    {
        $student = Auth::guard('student')->user();
        $exam    = Exam::published()->activeWindow()->findOrFail($examId);

        // Cek eligibility kelas
        if ($exam->target_kelas !== $student->kelas_sekarang) {
            return back()->withErrors(['exam' => 'Ujian ini bukan untuk kelas Anda.']);
        }

        // Cek / buat attempt dalam transaksi
        $attempt = DB::transaction(function () use ($exam, $student) {
            $existing = ExamAttempt::where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->where('is_void', false)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            return ExamAttempt::create([
                'exam_id'        => $exam->id,
                'student_id'     => $student->id,
                'mulai_at'       => now(),
                'batas_waktu_at' => now()->addMinutes($exam->durasi_menit),
                'status'         => 'berlangsung',
            ]);
        });

        if (in_array($attempt->status, ['selesai', 'dikeluarkan', 'dibatalkan'])) {
            return redirect()->route('student.dashboard')
                ->with('info', 'Anda sudah menyelesaikan ujian ini.');
        }

        return redirect()->route('exam.take', $examId);
    }
}
