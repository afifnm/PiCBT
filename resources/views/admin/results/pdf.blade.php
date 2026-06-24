<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1e293b; }
    .header { text-align: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #2563eb; }
    .header h1 { font-size: 16px; font-weight: bold; color: #2563eb; }
    .header p { color: #64748b; margin-top: 3px; }
    .info { display: flex; gap: 30px; margin-bottom: 16px; font-size: 10px; color: #475569; }
    .info strong { color: #1e293b; }
    table { width: 100%; border-collapse: collapse; }
    thead tr { background: #2563eb; color: white; }
    thead th { padding: 7px 8px; text-align: left; font-size: 10px; }
    tbody tr:nth-child(even) { background: #f8fafc; }
    tbody td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; }
    .skor-tinggi { color: #16a34a; font-weight: bold; }
    .skor-sedang { color: #d97706; font-weight: bold; }
    .skor-rendah { color: #dc2626; font-weight: bold; }
    .badge { display: inline-block; padding: 1px 6px; border-radius: 999px; font-size: 9px; }
    .badge-ok  { background: #dcfce7; color: #15803d; }
    .badge-out { background: #fee2e2; color: #b91c1c; }
    .footer { margin-top: 20px; text-align: right; font-size: 9px; color: #94a3b8; }
</style>
</head>
<body>
    <div class="header">
        <h1>Rekap Nilai Ujian</h1>
        <p>{{ $exam->judul }} &bull; {{ $exam->questionBank->subject->nama }}</p>
        <p>Kelas {{ $exam->target_kelas }} &bull; Dicetak: {{ now()->isoFormat('D MMMM Y, HH:mm') }}</p>
    </div>

    @php
        $total   = $attempts->count();
        $selesai = $attempts->where('status', 'selesai')->count();
        $avg     = $attempts->whereNotNull('total_skor')->avg('total_skor');
        $max     = $attempts->whereNotNull('total_skor')->max('total_skor');
        $bobot   = $exam->total_bobot;
    @endphp

    <div class="info">
        <span>Total Peserta: <strong>{{ $total }}</strong></span>
        <span>Selesai: <strong>{{ $selesai }}</strong></span>
        <span>Rata-rata: <strong>{{ number_format($avg ?? 0, 1) }}</strong></span>
        <span>Tertinggi: <strong>{{ $max ?? '—' }}</strong></span>
        <span>Total Bobot: <strong>{{ $bobot }}</strong></span>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NIS</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Skor</th>
                <th>%</th>
                <th>Pelanggaran</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attempts->sortByDesc('total_skor') as $i => $a)
                @php
                    $pct   = $bobot > 0 ? round(($a->total_skor / $bobot) * 100, 1) : 0;
                    $cls   = $pct >= 75 ? 'skor-tinggi' : ($pct >= 60 ? 'skor-sedang' : 'skor-rendah');
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $a->student->nis }}</td>
                    <td>{{ $a->student->nama }}</td>
                    <td>{{ $a->student->kelas_sekarang }}</td>
                    <td class="{{ $cls }}">{{ $a->total_skor ?? '—' }}</td>
                    <td class="{{ $cls }}">{{ $a->total_skor !== null ? $pct . '%' : '—' }}</td>
                    <td>{{ $a->jumlah_pelanggaran > 0 ? '⚠ ' . $a->jumlah_pelanggaran : '—' }}</td>
                    <td>
                        <span class="badge {{ $a->status === 'selesai' ? 'badge-ok' : 'badge-out' }}">
                            {{ $a->status === 'selesai' ? 'Selesai' : 'Dikeluarkan' }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">Dokumen ini digenerate otomatis oleh PiCBT &bull; {{ now()->isoFormat('D MMMM Y') }}</div>
</body>
</html>
