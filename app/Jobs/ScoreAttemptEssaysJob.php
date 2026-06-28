<?php

namespace App\Jobs;

use App\Models\AttemptAnswer;
use App\Models\ExamAttempt;
use App\Services\GeminiScoringService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScoreAttemptEssaysJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 300; // 5 menit — satu job menghandle semua essay satu attempt
    public int $backoff = 15;

    public function __construct(
        private readonly int $attemptId,
    ) {}

    public function handle(GeminiScoringService $service): void
    {
        $answers = AttemptAnswer::where('exam_attempt_id', $this->attemptId)
            ->whereHas('question', fn ($q) => $q->where('tipe', 'esai'))
            ->whereNull('dinilai_oleh')
            ->with('question')
            ->get();

        if ($answers->isEmpty()) {
            return;
        }

        foreach ($answers as $answer) {
            try {
                DB::transaction(function () use ($answer, $service) {
                    $fresh = AttemptAnswer::lockForUpdate()->find($answer->id);
                    if ($fresh && $fresh->dinilai_oleh === null) {
                        $service->scoreAnswer($fresh);
                    }
                });
            } catch (\Throwable $e) {
                Log::error('ScoreAttemptEssaysJob: gagal menskor jawaban', [
                    'attempt_answer_id' => $answer->id,
                    'attempt_id'        => $this->attemptId,
                    'error'             => $e->getMessage(),
                ]);
            }
        }

        // Recalculate total_skor sekali setelah semua essay selesai
        $attempt = ExamAttempt::find($this->attemptId);
        if ($attempt) {
            $attempt->recalculateTotalSkor();
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('ScoreAttemptEssaysJob gagal permanen', [
            'attempt_id' => $this->attemptId,
            'error'      => $e->getMessage(),
        ]);
    }
}
