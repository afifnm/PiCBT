<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAttempt extends Model
{
    protected $fillable = [
        'exam_id', 'student_id', 'mulai_at', 'selesai_at', 'batas_waktu_at',
        'status', 'total_skor', 'jumlah_pelanggaran', 'is_void', 'void_reason',
    ];

    protected function casts(): array
    {
        return [
            'mulai_at'           => 'datetime',
            'selesai_at'         => 'datetime',
            'batas_waktu_at'     => 'datetime',
            'total_skor'         => 'float',
            'jumlah_pelanggaran' => 'integer',
            'is_void'            => 'boolean',
        ];
    }

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AttemptAnswer::class);
    }

    public function cheatLogs(): HasMany
    {
        return $this->hasMany(CheatLog::class)->orderBy('terjadi_at');
    }

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------
    public function scopeActive($query)
    {
        return $query->where('status', 'berlangsung')->where('is_void', false);
    }

    public function scopeFinished($query)
    {
        return $query->whereIn('status', ['selesai', 'dikeluarkan']);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------
    public function isOngoing(): bool
    {
        return $this->status === 'berlangsung' && ! $this->is_void;
    }

    public function sisaDetik(): int
    {
        return max(0, now()->diffInSeconds($this->batas_waktu_at, false));
    }

    public function recalculateTotalSkor(): void
    {
        $this->total_skor = (float) $this->answers()->sum('skor');
        $this->saveQuietly();
    }

    // -----------------------------------------------------------------------
    // Void (soft reversal) — panggil dalam DB::transaction()
    // -----------------------------------------------------------------------
    public function voidAttempt(string $reason): void
    {
        $this->update([
            'is_void'     => true,
            'void_reason' => $reason,
            'status'      => 'dibatalkan',
        ]);
    }
}
