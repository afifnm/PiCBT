@extends('layouts.admin')
@section('title', 'Rekap — ' . $exam->judul)
@section('page-title')
    <div class="flex items-center gap-2 text-sm font-normal">
        <a href="{{ route('admin.results.index') }}"
           class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-200 transition-colors">Rekap Nilai</a>
        <span class="text-surface-300 dark:text-surface-600">/</span>
        <span class="font-semibold text-surface-800 dark:text-surface-100 truncate">{{ $exam->judul }}</span>
    </div>
@endsection

@section('content')
<div x-data="examResults()">

    {{-- Header info ujian --}}
    <div class="card p-5 mb-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <h2 class="font-bold text-surface-800 dark:text-surface-100 text-lg">{{ $exam->judul }}</h2>
                    <span class="badge {{ $exam->status === 'published' ? 'badge-green' : 'bg-surface-100 text-surface-500 dark:bg-surface-800 dark:text-surface-400' }}">
                        {{ $exam->status === 'published' ? 'Aktif' : 'Selesai' }}
                    </span>
                </div>
                <p class="text-sm text-surface-400 dark:text-surface-500">
                    {{ $exam->questionBank->subject->nama }} &bull;
                    Kelas {{ $exam->target_kelas }} &bull;
                    {{ $exam->examQuestions->count() }} soal &bull;
                    Total bobot {{ $exam->total_bobot }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.results.export.excel', ['exam_id' => $exam->id]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700
                          text-white text-sm font-semibold rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Excel
                </a>
                <a href="{{ route('admin.results.export.pdf', ['exam_id' => $exam->id]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700
                          text-white text-sm font-semibold rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    PDF
                </a>
            </div>
        </div>
    </div>

    {{-- Statistik --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-5">
        @php
            $statItems = [
                ['val' => $stats['total'],       'label' => 'Peserta',     'color' => 'text-primary-600 dark:text-primary-400'],
                ['val' => $stats['selesai'],      'label' => 'Selesai',     'color' => 'text-emerald-600 dark:text-emerald-400'],
                ['val' => $stats['dikeluarkan'],  'label' => 'Dikeluarkan', 'color' => 'text-rose-600 dark:text-rose-400'],
                ['val' => $stats['rata_rata'],    'label' => 'Rata-rata',   'color' => 'text-amber-600 dark:text-amber-400'],
                ['val' => $stats['tertinggi'],    'label' => 'Tertinggi',   'color' => 'text-emerald-600 dark:text-emerald-400'],
                ['val' => $stats['terendah'],     'label' => 'Terendah',    'color' => 'text-rose-600 dark:text-rose-400'],
            ];
        @endphp
        @foreach ($statItems as $s)
            <div class="card px-4 py-3">
                <p class="text-xs text-surface-400 dark:text-surface-500 mb-1">{{ $s['label'] }}</p>
                <p class="text-xl font-bold {{ $s['color'] }}">{{ $s['val'] ?? '—' }}</p>
            </div>
        @endforeach
    </div>

    {{-- Search + tabel --}}
    <div class="card overflow-hidden">
        <div class="card-header">
            <h3 class="font-semibold text-surface-800 dark:text-surface-100 text-sm">
                Daftar Nilai Siswa
                <span class="font-normal text-surface-400 ml-1" x-text="'(' + filtered.length + ' peserta)'"></span>
            </h3>
            <div class="flex items-center gap-2">
                <div class="relative w-56">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-surface-400 pointer-events-none"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                    </svg>
                    <input type="text" x-model="search" placeholder="Cari nama / NIS / kelas..."
                           class="input-base pl-9 !py-1.5 text-xs">
                </div>
                <button @click="resetAll()" :disabled="resettingAll"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg transition
                               border border-rose-200 dark:border-rose-800 text-rose-600 dark:text-rose-400
                               hover:bg-rose-50 dark:hover:bg-rose-950/40 disabled:opacity-50">
                    <template x-if="!resettingAll">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </template>
                    <template x-if="resettingAll">
                        <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                    </template>
                    <span x-text="resettingAll ? 'Mereset...' : 'Reset Semua'"></span>
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="table-base">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIS</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th class="text-right">Skor</th>
                        <th class="text-center">Pelanggaran</th>
                        <th class="text-center">Koreksi</th>
                        <th class="text-center">Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(a, i) in filtered" :key="a.id">
                        <tr>
                            <td class="text-surface-400" x-text="i + 1"></td>
                            <td class="font-mono text-surface-500 dark:text-surface-400" x-text="a.nis"></td>
                            <td class="font-medium text-surface-800 dark:text-surface-100" x-text="a.nama"></td>
                            <td class="text-surface-500 dark:text-surface-400" x-text="a.kelas"></td>
                            <td class="text-right">
                                <span class="font-bold" :class="scoreColor(a.total_skor)"
                                      x-text="a.total_skor !== null ? a.total_skor : '—'"></span>
                                <span class="text-xs text-surface-400">/{{ $stats['total_bobot'] }}</span>
                            </td>
                            <td class="text-center">
                                <span :class="a.jumlah_pelanggaran > 0
                                        ? 'text-rose-600 dark:text-rose-400 font-semibold'
                                        : 'text-surface-300 dark:text-surface-600'"
                                      x-text="a.jumlah_pelanggaran"></span>
                            </td>
                            <td class="text-center">
                                <template x-if="a.perlu_koreksi > 0">
                                    <span class="badge-amber" x-text="a.perlu_koreksi + ' soal'"></span>
                                </template>
                                <template x-if="a.perlu_koreksi === 0">
                                    <span class="text-emerald-500 dark:text-emerald-400 text-xs font-medium">✓ Selesai</span>
                                </template>
                            </td>
                            <td class="text-center">
                                <span class="badge"
                                      :class="{
                                          'badge-green': a.status === 'selesai',
                                          'badge-red':   a.status === 'dikeluarkan',
                                          'badge-amber': a.status === 'berlangsung',
                                      }"
                                      x-text="{ selesai: 'Selesai', dikeluarkan: 'Dikeluarkan', berlangsung: 'Berlangsung' }[a.status] || a.status">
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center gap-1.5">
                                    <a :href="a.detail_url" target="_blank"
                                       class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5
                                              border border-surface-200 dark:border-surface-700 rounded-lg
                                              hover:bg-surface-50 dark:hover:bg-surface-800 transition-colors
                                              text-surface-600 dark:text-surface-300">
                                        Detail
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </a>
                                    <button :disabled="a.resetting"
                                            @click="resetOne(a)"
                                            class="inline-flex items-center gap-1 text-xs px-2.5 py-1.5 rounded-lg transition-colors
                                                   border border-rose-200 dark:border-rose-800 text-rose-500 dark:text-rose-400
                                                   hover:bg-rose-50 dark:hover:bg-rose-950/40 disabled:opacity-50"
                                            title="Reset ujian siswa ini">
                                        <template x-if="!a.resetting">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                        </template>
                                        <template x-if="a.resetting">
                                            <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                                            </svg>
                                        </template>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <tr x-show="attempts.length === 0">
                        <td colspan="9" class="py-16 text-center">
                            <p class="text-sm text-surface-400">Belum ada peserta untuk ujian ini.</p>
                        </td>
                    </tr>
                    <tr x-show="attempts.length > 0 && filtered.length === 0">
                        <td colspan="9" class="py-10 text-center text-sm text-surface-400">
                            Tidak ada peserta yang cocok dengan pencarian.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function examResults() {
    return {
        search: '',
        resettingAll: false,
        totalBobot: {{ $stats['total_bobot'] }},
        attempts: {{ Js::from($attempts->map(fn ($a) => [
            'id'                 => $a->id,
            'nis'                => $a->student->nis,
            'nama'               => $a->student->nama,
            'kelas'              => $a->student->kelas_sekarang,
            'total_skor'         => $a->total_skor,
            'jumlah_pelanggaran' => $a->jumlah_pelanggaran,
            'perlu_koreksi'      => $a->answers->whereNull('dinilai_oleh')->count(),
            'status'             => $a->status,
            'detail_url'         => route('admin.results.detail', $a),
            'reset_url'          => route('admin.results.attempt.reset', $a),
            'resetting'          => false,
        ])) }},

        get filtered() {
            const q = this.search.trim().toLowerCase();
            if (!q) return this.attempts;
            return this.attempts.filter(a =>
                a.nama.toLowerCase().includes(q) ||
                a.nis.toLowerCase().includes(q) ||
                a.kelas.toLowerCase().includes(q)
            );
        },

        scoreColor(skor) {
            if (skor === null) return 'text-surface-400';
            const pct = this.totalBobot ? (skor / this.totalBobot) * 100 : 0;
            if (pct >= 75) return 'text-emerald-600 dark:text-emerald-400';
            if (pct >= 60) return 'text-amber-600 dark:text-amber-400';
            return 'text-rose-600 dark:text-rose-400';
        },

        async resetOne(attempt) {
            if (!confirm(`Reset ujian ${attempt.nama}? Semua jawaban dan log akan dihapus.`)) return;
            attempt.resetting = true;
            try {
                const res = await fetch(attempt.reset_url, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
                });
                if (res.ok) {
                    this.attempts = this.attempts.filter(a => a.id !== attempt.id);
                }
            } finally {
                attempt.resetting = false;
            }
        },

        async resetAll() {
            if (!confirm(`Reset SEMUA ujian (${this.attempts.length} peserta)? Semua jawaban dan log akan dihapus permanen.`)) return;
            this.resettingAll = true;
            try {
                const res = await fetch('{{ route('admin.results.exam.reset-all', $exam) }}', {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
                });
                if (res.ok) {
                    this.attempts = [];
                }
            } finally {
                this.resettingAll = false;
            }
        },
    };
}
function csrf() { return document.querySelector('meta[name="csrf-token"]').content; }
</script>
@endpush
