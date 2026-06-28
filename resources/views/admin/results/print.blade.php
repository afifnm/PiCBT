<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rekap Nilai — {{ $exam->judul }}</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }

    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        font-size: 13px;
        color: #1e293b;
        background: #f8fafc;
    }

    /* ── Toolbar (hilang saat print) ── */
    .toolbar {
        position: fixed;
        top: 0; left: 0; right: 0;
        background: #1e293b;
        color: #f1f5f9;
        padding: 10px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        z-index: 100;
        box-shadow: 0 2px 8px rgba(0,0,0,.3);
    }
    .toolbar-title {
        font-weight: 600;
        font-size: 13px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .toolbar-actions { display: flex; gap: 8px; flex-shrink: 0; }
    .btn-print {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 16px;
        background: #2563eb; color: #fff;
        border: none; border-radius: 8px;
        font-size: 12px; font-weight: 600;
        cursor: pointer; transition: background .15s;
    }
    .btn-print:hover { background: #1d4ed8; }
    .btn-close {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 14px;
        background: transparent; color: #94a3b8;
        border: 1px solid #334155; border-radius: 8px;
        font-size: 12px; font-weight: 500;
        cursor: pointer; transition: background .15s, color .15s;
        text-decoration: none;
    }
    .btn-close:hover { background: #334155; color: #f1f5f9; }

    /* ── Dokumen ── */
    .page {
        max-width: 900px;
        margin: 64px auto 40px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 1px 4px rgba(0,0,0,.08), 0 4px 16px rgba(0,0,0,.06);
        overflow: hidden;
    }

    /* Header biru */
    .doc-header {
        background: linear-gradient(135deg, #1d4ed8, #2563eb);
        color: #fff;
        padding: 24px 28px 20px;
    }
    .doc-header h1 {
        font-size: 18px;
        font-weight: 700;
        letter-spacing: -.01em;
        margin-bottom: 6px;
    }
    .doc-header .meta {
        font-size: 12px;
        opacity: .8;
        display: flex;
        flex-wrap: wrap;
        gap: 6px 16px;
    }
    .doc-header .meta span::before {
        content: '• ';
        opacity: .5;
    }
    .doc-header .meta span:first-child::before { content: ''; }

    /* Statistik */
    .stats-bar {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        border-bottom: 1px solid #e2e8f0;
    }
    .stat-item {
        padding: 14px 18px;
        border-right: 1px solid #e2e8f0;
        text-align: center;
    }
    .stat-item:last-child { border-right: none; }
    .stat-val {
        font-size: 22px;
        font-weight: 700;
        color: #1e40af;
        line-height: 1.1;
    }
    .stat-label {
        font-size: 10px;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-top: 2px;
    }

    /* Tabel */
    .table-wrap { padding: 0; }
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }
    thead tr {
        background: #f1f5f9;
        color: #475569;
    }
    thead th {
        padding: 9px 12px;
        text-align: left;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }
    thead th.right { text-align: right; }
    thead th.center { text-align: center; }
    tbody tr { border-bottom: 1px solid #f1f5f9; }
    tbody tr:hover { background: #fafafa; }
    tbody td { padding: 8px 12px; vertical-align: middle; }
    tbody td.right { text-align: right; }
    tbody td.center { text-align: center; }
    .no-col { color: #94a3b8; font-size: 11px; width: 36px; }
    .nis-col { font-family: 'Courier New', monospace; color: #64748b; font-size: 11px; }
    .nama-col { font-weight: 500; color: #1e293b; }
    .kelas-col { color: #64748b; font-size: 11px; }

    /* Skor */
    .skor-tinggi { color: #16a34a; font-weight: 700; }
    .skor-sedang { color: #d97706; font-weight: 700; }
    .skor-rendah { color: #dc2626; font-weight: 700; }
    .skor-null   { color: #94a3b8; }

    /* Badge */
    .badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 600;
    }
    .badge-selesai    { background: #dcfce7; color: #15803d; }
    .badge-dikeluarkan { background: #fee2e2; color: #b91c1c; }

    /* Pelanggaran */
    .pelanggaran-ok  { color: #94a3b8; }
    .pelanggaran-bad { color: #dc2626; font-weight: 600; }

    /* Footer */
    .doc-footer {
        padding: 14px 20px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 10px;
        color: #94a3b8;
    }

    /* ── Print styles ── */
    @media print {
        body { background: #fff; }
        .toolbar { display: none !important; }
        .page {
            margin: 0;
            box-shadow: none;
            border-radius: 0;
            max-width: 100%;
        }
        thead tr { background: #dbeafe !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .doc-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        tbody tr { page-break-inside: avoid; }
    }

    @page { margin: 12mm 10mm; }
</style>
</head>
<body>

{{-- ── Toolbar (hilang saat print) ── --}}
<div class="toolbar">
    <span class="toolbar-title">Rekap Nilai — {{ $exam->judul }}</span>
    <div class="toolbar-actions">
        <button class="btn-print" onclick="window.print()">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
            </svg>
            Cetak
        </button>
        <a class="btn-close" href="javascript:window.close()">Tutup</a>
    </div>
</div>

@php
    $total   = $attempts->count();
    $selesai = $attempts->where('status', 'selesai')->count();
    $avg     = $attempts->whereNotNull('total_skor')->avg('total_skor');
    $max     = $attempts->whereNotNull('total_skor')->max('total_skor');
    $bobot   = $exam->total_bobot ?? $attempts->max('total_skor') ?? 100;
@endphp

{{-- ── Dokumen ── --}}
<div class="page">

    {{-- Header --}}
    <div class="doc-header">
        <h1>Rekap Nilai Ujian</h1>
        <div class="meta">
            <span>{{ $exam->judul }}</span>
            <span>{{ $exam->questionBank->subject->nama }}</span>
            <span>Kelas {{ $exam->target_kelas }}</span>
            <span>Total Bobot: {{ $bobot }}</span>
            <span>Dicetak: {{ now()->isoFormat('D MMM Y, HH:mm') }}</span>
        </div>
    </div>

    {{-- Statistik --}}
    <div class="stats-bar">
        <div class="stat-item">
            <div class="stat-val">{{ $total }}</div>
            <div class="stat-label">Peserta</div>
        </div>
        <div class="stat-item">
            <div class="stat-val" style="color:#16a34a">{{ $selesai }}</div>
            <div class="stat-label">Selesai</div>
        </div>
        <div class="stat-item">
            <div class="stat-val" style="color:#dc2626">{{ $total - $selesai }}</div>
            <div class="stat-label">Dikeluarkan</div>
        </div>
        <div class="stat-item">
            <div class="stat-val" style="color:#d97706">{{ number_format($avg ?? 0, 1) }}</div>
            <div class="stat-label">Rata-rata</div>
        </div>
        <div class="stat-item">
            <div class="stat-val">{{ $max ?? '—' }}</div>
            <div class="stat-label">Tertinggi</div>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th class="no-col">No</th>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th class="right">Skor</th>
                    <th class="right">%</th>
                    <th class="center">Pelanggaran</th>
                    <th class="center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attempts as $i => $a)
                    @php
                        $pct = $bobot > 0 && $a->total_skor !== null
                            ? round(($a->total_skor / $bobot) * 100, 1)
                            : null;
                        $cls = $pct === null ? 'skor-null'
                             : ($pct >= 75 ? 'skor-tinggi' : ($pct >= 60 ? 'skor-sedang' : 'skor-rendah'));
                    @endphp
                    <tr>
                        <td class="no-col">{{ $i + 1 }}</td>
                        <td class="nis-col">{{ $a->student->nis }}</td>
                        <td class="nama-col">{{ $a->student->nama }}</td>
                        <td class="kelas-col">{{ $a->student->kelas_sekarang }}</td>
                        <td class="right {{ $cls }}">{{ $a->total_skor ?? '—' }}</td>
                        <td class="right {{ $cls }}">{{ $pct !== null ? $pct . '%' : '—' }}</td>
                        <td class="center {{ $a->jumlah_pelanggaran > 0 ? 'pelanggaran-bad' : 'pelanggaran-ok' }}">
                            {{ $a->jumlah_pelanggaran > 0 ? '⚠ ' . $a->jumlah_pelanggaran : '—' }}
                        </td>
                        <td class="center">
                            <span class="badge {{ $a->status === 'selesai' ? 'badge-selesai' : 'badge-dikeluarkan' }}">
                                {{ $a->status === 'selesai' ? 'Selesai' : 'Dikeluarkan' }}
                            </span>
                        </td>
                    </tr>
                @endforeach

                @if ($attempts->isEmpty())
                    <tr>
                        <td colspan="8" style="text-align:center;padding:32px;color:#94a3b8">
                            Belum ada peserta untuk ujian ini.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- Footer --}}
    <div class="doc-footer">
        <span>{{ $appName }} — Sistem Computer Based Test</span>
        <span>{{ now()->isoFormat('D MMMM YYYY') }}</span>
    </div>

</div>

</body>
</html>
