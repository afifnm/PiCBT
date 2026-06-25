<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CheatLog;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalSiswa   = Student::count();
        $ujianAktif   = Exam::published()->activeWindow()->count();
        $sedangUjian  = ExamAttempt::where('status', 'berlangsung')->where('is_void', false)->count();
        $perluKoreksi = \App\Models\AttemptAnswer::whereNull('dinilai_oleh')
            ->whereHas('question', fn ($q) => $q->where('tipe', 'esai'))
            ->count();

        $activeExams = Exam::published()
            ->activeWindow()
            ->with(['attempts' => fn ($q) => $q->where('status', 'berlangsung')])
            ->latest()
            ->take(5)
            ->get();

        $recentCheats = CheatLog::with(['attempt.student', 'attempt.exam'])
            ->latest('terjadi_at')
            ->take(10)
            ->get();

        // Gemini 2.5 Flash pricing (per 1M tokens) — source: Google AI pricing page
        // Input: $0.30 / 1M tokens (≤200k ctx), Output: $2.50 / 1M tokens
        $aiTokensInput  = (int) Setting::get('ai_tokens_input',  0);
        $aiTokensOutput = (int) Setting::get('ai_tokens_output', 0);
        $aiCostUsd      = round(($aiTokensInput / 1_000_000) * 0.30 + ($aiTokensOutput / 1_000_000) * 2.50, 4);
        $usdToIdr       = 16300;
        $aiCostIdr      = round($aiCostUsd * $usdToIdr);

        // Analytics: 5 ujian terbaru dengan rata-rata & distribusi skor
        $analytics = Exam::where('status', 'closed')
            ->withCount(['attempts as peserta_count' => fn ($q) => $q->where('is_void', false)->whereIn('status', ['selesai', 'dikeluarkan'])])
            ->having('peserta_count', '>', 0)
            ->orderByDesc('selesai_pada')
            ->take(5)
            ->get()
            ->map(function ($exam) {
                $scores = ExamAttempt::where('exam_id', $exam->id)
                    ->where('is_void', false)
                    ->whereIn('status', ['selesai', 'dikeluarkan'])
                    ->whereNotNull('total_skor')
                    ->pluck('total_skor');

                if ($scores->isEmpty()) {
                    return null;
                }

                $avg = round($scores->avg(), 1);
                $max = $scores->max();

                // Distribusi: 5 bucket (0-19, 20-39, 40-59, 60-79, 80-100)
                $buckets = [0, 0, 0, 0, 0];
                foreach ($scores as $s) {
                    $idx = min(4, (int) floor($s / 20));
                    $buckets[$idx]++;
                }

                return [
                    'judul'    => $exam->judul,
                    'peserta'  => $scores->count(),
                    'avg'      => $avg,
                    'max'      => $max,
                    'buckets'  => $buckets,
                ];
            })
            ->filter()
            ->values();

        return view('admin.dashboard', compact(
            'totalSiswa', 'ujianAktif', 'sedangUjian', 'perluKoreksi',
            'activeExams', 'recentCheats',
            'aiTokensInput', 'aiTokensOutput', 'aiCostUsd', 'aiCostIdr',
            'analytics',
        ));
    }
}
