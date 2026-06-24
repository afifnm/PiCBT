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

class ScoreEssayAnswerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;
    public int $backoff = 10;

    public function __construct(
        private readonly int $attemptAnswerId,
    ) {}

    public function handle(GeminiScoringService $service): void
    {
        DB::transaction(function () use ($service) {
            $answer = AttemptAnswer::with('question')
                ->lockForUpdate()
                ->findOrFail($this->attemptAnswerId);

            // Skip if already scored (retry safety)
            if ($answer->dinilai_oleh !== null) {
                return;
            }

            $service->scoreAnswer($answer);

            // Recalculate total_skor on the attempt after each answer is scored
            $attempt = ExamAttempt::lockForUpdate()->find($answer->exam_attempt_id);
            if ($attempt) {
                $attempt->total_skor = AttemptAnswer::where('exam_attempt_id', $attempt->id)
                    ->sum('skor');
                $attempt->save();
            }
        });
    }

    public function failed(\Throwable $e): void
    {
        // Leave skor = null so guru knows it needs manual review
        \Illuminate\Support\Facades\Log::error('ScoreEssayAnswerJob failed permanently', [
            'attempt_answer_id' => $this->attemptAnswerId,
            'error'             => $e->getMessage(),
        ]);
    }
}
