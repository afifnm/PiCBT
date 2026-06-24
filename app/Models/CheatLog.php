<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheatLog extends Model
{
    protected $fillable = [
        'exam_attempt_id', 'jenis', 'detail', 'terjadi_at',
    ];

    protected function casts(): array
    {
        return [
            'detail'     => 'array',
            'terjadi_at' => 'datetime',
        ];
    }

    public static array $jenisLabels = [
        'blur'            => 'Pindah Tab / Jendela',
        'fullscreen_exit' => 'Keluar Layar Penuh',
        'key_ctrl'        => 'Tekan Ctrl',
        'key_copy'        => 'Menyalin (Ctrl+C)',
        'key_paste'       => 'Menempel (Ctrl+V)',
        'key_print'       => 'Print (Ctrl+P)',
        'right_click'     => 'Klik Kanan',
        'dev_tools'       => 'Buka DevTools',
        'multi_window'    => 'Multi Window',
        'other'           => 'Lainnya',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id');
    }

    public function getJenisLabelAttribute(): string
    {
        return static::$jenisLabels[$this->jenis] ?? $this->jenis;
    }
}
