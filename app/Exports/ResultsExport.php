<?php

namespace App\Exports;

use App\Models\Exam;
use App\Models\ExamAttempt;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ResultsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    private Exam $exam;

    public function __construct(int $examId)
    {
        $this->exam = Exam::with('questionBank.subject')->findOrFail($examId);
    }

    public function collection()
    {
        return ExamAttempt::where('exam_id', $this->exam->id)
            ->where('is_void', false)
            ->with('student')
            ->orderBy('total_skor', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No', 'NIS', 'Nama Siswa', 'Kelas', 'Jurusan',
            'Status', 'Total Skor', 'Jumlah Pelanggaran',
            'Mulai', 'Selesai', 'Durasi (menit)',
        ];
    }

    public function map($attempt): array
    {
        static $no = 0;
        $no++;

        $durasi = ($attempt->mulai_at && $attempt->selesai_at)
            ? $attempt->mulai_at->diffInMinutes($attempt->selesai_at)
            : null;

        return [
            $no,
            $attempt->student->nis,
            $attempt->student->nama,
            $attempt->student->kelas_sekarang,
            $attempt->student->jurusan ?? '—',
            match($attempt->status) {
                'selesai'     => 'Selesai',
                'dikeluarkan' => 'Dikeluarkan',
                'berlangsung' => 'Berlangsung',
                default       => $attempt->status,
            },
            $attempt->total_skor ?? '—',
            $attempt->jumlah_pelanggaran,
            $attempt->mulai_at?->format('d/m/Y H:i') ?? '—',
            $attempt->selesai_at?->format('d/m/Y H:i') ?? '—',
            $durasi ?? '—',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
            ],
        ];
    }

    public function title(): string
    {
        return str($this->exam->judul)->limit(31);
    }
}
