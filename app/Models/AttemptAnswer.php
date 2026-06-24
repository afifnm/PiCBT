<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttemptAnswer extends Model
{
    protected $fillable = [
        'exam_attempt_id', 'question_id',
        'jawaban_pg', 'jawaban_esai',
        'skor', 'ai_feedback', 'dinilai_oleh',
    ];

    protected function casts(): array
    {
        return ['skor' => 'float'];
    }

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------
    public function needsManualReview(): bool
    {
        return $this->dinilai_oleh === null
            && $this->question?->tipe === 'esai';
    }

    public function isAiScored(): bool
    {
        return $this->dinilai_oleh === 'ai';
    }

    public function isManuallyScored(): bool
    {
        return $this->dinilai_oleh === 'manual';
    }

    // Override manual oleh guru — panggil dalam DB::transaction()
    public function overrideSkor(float $skor, string $feedback = ''): void
    {
        $bobotMax = (float) ($this->question?->bobot ?? PHP_FLOAT_MAX);

        $this->update([
            'skor'         => min(max(0, $skor), $bobotMax),
            'ai_feedback'  => $feedback ?: $this->ai_feedback,
            'dinilai_oleh' => 'manual',
        ]);

        $this->attempt->recalculateTotalSkor();
    }
}
