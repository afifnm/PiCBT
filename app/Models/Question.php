<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'question_bank_id', 'tipe', 'pertanyaan', 'gambar',
        'bobot', 'kunci_jawaban', 'urutan',
    ];

    protected function casts(): array
    {
        return [
            'bobot'  => 'float',
            'urutan' => 'integer',
        ];
    }

    public function questionBank(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('label');
    }

    public function examQuestions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class);
    }

    public function attemptAnswers(): HasMany
    {
        return $this->hasMany(AttemptAnswer::class);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------
    public function isPilihanGanda(): bool
    {
        return $this->tipe === 'pilihan_ganda';
    }

    public function isEsai(): bool
    {
        return $this->tipe === 'esai';
    }

    public function correctOption(): ?QuestionOption
    {
        return $this->options->firstWhere('is_correct', true);
    }
}
