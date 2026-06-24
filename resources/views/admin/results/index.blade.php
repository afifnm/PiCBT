@extends('layouts.admin')
@section('title', 'Rekap Nilai')
@section('page-title', 'Rekap Nilai')

@section('content')
<div x-data="resultsPage()" x-init="init()">

    {{-- Filter --}}
    <div class="flex flex-wrap gap-3 mb-5">
        <select x-model="selectedExam" @change="loadResults()"
                class="input-base flex-1 min-w-56">
            <option value="">— Pilih Ujian —</option>
            @foreach ($exams as $e)
                <option value="{{ $e->id }}">{{ $e->judul }} ({{ $e->questionBank->subject->nama }})</option>
            @endforeach
        </select>
        <button @click="exportExcel()" :disabled="!selectedExam"
                class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700
                       text-white text-sm font-semibold rounded-xl transition disabled:opacity-40">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Excel
        </button>
        <button @click="exportPdf()" :disabled="!selectedExam"
                class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700
                       text-white text-sm font-semibold rounded-xl transition disabled:opacity-40">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            PDF
        </button>
    </div>

    {{-- Statistik --}}
    <div x-show="stats" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-5">
        @php
            $statItems = [
                ['key' => 'total',       'label' => 'Peserta',     'color' => 'text-primary-600 dark:text-primary-400'],
                ['key' => 'selesai',     'label' => 'Selesai',     'color' => 'text-emerald-600 dark:text-emerald-400'],
                ['key' => 'dikeluarkan', 'label' => 'Dikeluarkan', 'color' => 'text-rose-600 dark:text-rose-400'],
                ['key' => 'rata_rata',   'label' => 'Rata-rata',   'color' => 'text-amber-600 dark:text-amber-400'],
                ['key' => 'tertinggi',   'label' => 'Tertinggi',   'color' => 'text-emerald-600 dark:text-emerald-400'],
                ['key' => 'terendah',    'label' => 'Terendah',    'color' => 'text-rose-600 dark:text-rose-400'],
            ];
        @endphp
        @foreach ($statItems as $s)
            <div class="card px-4 py-3">
                <p class="text-xs text-surface-400 dark:text-surface-500 mb-1">{{ $s['label'] }}</p>
                <p class="text-xl font-bold {{ $s['color'] }}" x-text="stats?.{{ $s['key'] }} ?? '—'"></p>
            </div>
        @endforeach
    </div>

    {{-- Tabel rekap --}}
    <div x-show="selectedExam" class="card overflow-hidden">
        <div class="card-header">
            <h3 class="font-semibold text-surface-800 dark:text-surface-100 text-sm" x-text="examTitle"></h3>
            <span class="text-xs text-surface-400" x-text="attempts.length + ' peserta'"></span>
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
                    <template x-for="(a, i) in attempts" :key="a.id">
                        <tr>
                            <td class="text-surface-400" x-text="i + 1"></td>
                            <td class="font-mono text-surface-500 dark:text-surface-400" x-text="a.nis"></td>
                            <td class="font-medium" x-text="a.nama"></td>
                            <td class="text-surface-500 dark:text-surface-400" x-text="a.kelas"></td>
                            <td class="text-right">
                                <span class="font-bold" :class="scoreClass(a.total_skor)"
                                      x-text="a.total_skor !== null ? a.total_skor : '—'"></span>
                                <span class="text-xs text-surface-400" x-text="'/' + (stats?.total_bobot ?? '')"></span>
                            </td>
                            <td class="text-center">
                                <span :class="a.jumlah_pelanggaran > 0 ? 'text-rose-600 font-semibold' : 'text-surface-300 dark:text-surface-600'"
                                      x-text="a.jumlah_pelanggaran"></span>
                            </td>
                            <td class="text-center">
                                <span x-show="a.perlu_koreksi > 0" class="badge-amber"
                                      x-text="`${a.perlu_koreksi} soal`"></span>
                                <span x-show="a.perlu_koreksi === 0" class="text-emerald-500 text-xs">✓ Selesai</span>
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
                                <a :href="`{{ url('admin/results/attempt') }}/${a.id}`"
                                   class="text-xs px-3 py-1.5 border border-surface-200 dark:border-surface-700
                                          rounded-lg hover:bg-surface-50 dark:hover:bg-surface-800 transition-colors
                                          text-surface-600 dark:text-surface-300">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="attempts.length === 0 && !loading">
                        <td colspan="9" class="py-12 text-center">
                            <p class="text-sm text-surface-400">Belum ada peserta untuk ujian ini.</p>
                        </td>
                    </tr>
                    <tr x-show="loading">
                        <td colspan="9" class="py-8 text-center">
                            <div class="flex items-center justify-center gap-2 text-surface-400">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                                </svg>
                                <span class="text-sm">Memuat data...</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="!selectedExam" class="text-center py-20">
        <svg class="w-12 h-12 text-surface-200 dark:text-surface-700 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        <p class="text-sm text-surface-400 dark:text-surface-500">Pilih ujian di atas untuk melihat rekap nilai.</p>
    </div>

</div>
@endsection

@push('scripts')
<script>
function resultsPage() {
    return {
        selectedExam: '', examTitle: '',
        attempts: [], stats: null, loading: false,

        init() {
            const p = new URLSearchParams(location.search);
            if (p.get('exam_id')) { this.selectedExam = p.get('exam_id'); this.loadResults(); }
        },

        async loadResults() {
            if (!this.selectedExam) return;
            this.loading = true;
            const res = await fetch(`{{ route('admin.results.json') }}?exam_id=${this.selectedExam}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
            });
            const data = await res.json();
            this.attempts  = data.attempts;
            this.stats     = data.stats;
            this.examTitle = data.exam.judul;
            this.loading   = false;
        },

        scoreClass(skor) {
            if (skor === null) return 'text-surface-400';
            const pct = this.stats?.total_bobot ? (skor / this.stats.total_bobot) * 100 : 0;
            if (pct >= 75) return 'text-emerald-600 dark:text-emerald-400';
            if (pct >= 60) return 'text-amber-600 dark:text-amber-400';
            return 'text-rose-600 dark:text-rose-400';
        },

        exportExcel() { if (this.selectedExam) window.location.href = `{{ route('admin.results.export.excel') }}?exam_id=${this.selectedExam}`; },
        exportPdf()   { if (this.selectedExam) window.location.href = `{{ route('admin.results.export.pdf') }}?exam_id=${this.selectedExam}`; },
    };
}
function csrf() { return document.querySelector('meta[name="csrf-token"]').content; }
</script>
@endpush
