<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $fillable = [
        'nis', 'nama', 'tahun_masuk', 'jurusan', 'kelas_awal', 'password',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password'     => 'hashed',
            'tahun_masuk'  => 'integer',
        ];
    }

    // -----------------------------------------------------------------------
    // Accessor: kelas aktif berdasarkan tahun masuk & tahun ajaran berjalan
    // Tahun ajaran berganti setiap Juli.
    // -----------------------------------------------------------------------
    public function getKelasSekarangAttribute(): string
    {
        $now         = now();
        $tahunAjaran = $now->month >= 7 ? $now->year : $now->year - 1;
        $selisih     = $tahunAjaran - $this->tahun_masuk;

        return match (true) {
            $selisih <= 0  => 'X',
            $selisih === 1 => 'XI',
            $selisih === 2 => 'XII',
            default        => 'Alumni',
        };
    }

    public function getNamaKelasAttribute(): string
    {
        $kelas = $this->kelas_sekarang;
        return $this->jurusan ? "{$kelas} {$this->jurusan}" : $kelas;
    }

    protected $appends = ['kelas_sekarang', 'nama_kelas'];

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------
    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function activeAttempt(int $examId): ?ExamAttempt
    {
        return $this->attempts()
            ->where('exam_id', $examId)
            ->where('is_void', false)
            ->first();
    }
}
