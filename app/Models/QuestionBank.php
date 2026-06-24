<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionBank extends Model
{
    protected $fillable = ['subject_id', 'judul', 'deskripsi', 'created_by'];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('urutan');
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function getTotalBobotAttribute(): float
    {
        return (float) $this->questions()->sum('bobot');
    }
}
