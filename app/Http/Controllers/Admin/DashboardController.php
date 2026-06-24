<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CheatLog;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Student;
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

        return view('admin.dashboard', compact(
            'totalSiswa', 'ujianAktif', 'sedangUjian', 'perluKoreksi',
            'activeExams', 'recentCheats',
        ));
    }
}
