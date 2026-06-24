<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Exam extends Model
{
    protected $fillable = [
        'question_bank_id', 'judul', 'token', 'target_kelas',
        'target_tahun_masuk', 'durasi_menit', 'acak_soal', 'acak_opsi',
        'mulai_pada', 'selesai_pada', 'max_pelanggaran', 'auto_keluar', 'status',
    ];

    protected function casts(): array
    {
        return [
            'acak_soal'          => 'boolean',
            'acak_opsi'          => 'boolean',
            'auto_keluar'        => 'boolean',
            'mulai_pada'         => 'datetime',
            'selesai_pada'       => 'datetime',
            'durasi_menit'       => 'integer',
            'max_pelanggaran'    => 'integer',
            'target_tahun_masuk' => 'integer',
        ];
    }

    // -----------------------------------------------------------------------
    // Atomic token generation — panggil sebelum save() pada record baru
    // -----------------------------------------------------------------------
    public static function generateToken(): string
    {
        do {
            $token = strtoupper(Str::random(8));
        } while (static::where('token', $token)->exists());

        return $token;
    }

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------
    public function questionBank(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class);
    }

    public function examQuestions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('urutan');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeActiveWindow($query)
    {
        $now = now();
        return $query->where('mulai_pada', '<=', $now)
                     ->where('selesai_pada', '>=', $now);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------
    public function isAccessibleNow(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }
        $now = now();
        return (! $this->mulai_pada   || $now->gte($this->mulai_pada))
            && (! $this->selesai_pada || $now->lte($this->selesai_pada));
    }

    public function getJumlahSoalAttribute(): int
    {
        return $this->examQuestions()->count();
    }

    public function getTotalBobotAttribute(): float
    {
        return (float) $this->examQuestions()->sum('bobot_snapshot');
    }
}
