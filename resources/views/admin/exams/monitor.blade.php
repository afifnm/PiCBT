@extends('layouts.admin')
@section('title', "Monitor — {$exam->judul}")
@section('page-title')
    <div class="flex items-center gap-2 text-sm font-normal">
        <a href="{{ route('admin.exams.index') }}"
           class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-200 transition-colors">Ujian</a>
        <span class="text-surface-300 dark:text-surface-600">/</span>
        <span class="font-semibold text-surface-800 dark:text-surface-100">Monitor: {{ $exam->judul }}</span>
    </div>
@endsection

@section('content')
<div x-data="monitorPage({{ $exam->id }})" x-init="init()">

    {{-- Stat cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
        <div class="card px-4 py-3">
            <p class="text-xs text-surface-400 dark:text-surface-500 mb-1">Berlangsung</p>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400" x-text="counts.berlangsung"></p>
        </div>
        <div class="card px-4 py-3">
            <p class="text-xs text-surface-400 dark:text-surface-500 mb-1">Selesai</p>
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400" x-text="counts.selesai"></p>
        </div>
        <div class="card px-4 py-3">
            <p class="text-xs text-surface-400 dark:text-surface-500 mb-1">Dikeluarkan</p>
            <p class="text-2xl font-bold text-rose-600 dark:text-rose-400" x-text="counts.dikeluarkan"></p>
        </div>
        <div class="card px-4 py-3">
            <p class="text-xs text-surface-400 dark:text-surface-500 mb-1">Total Pelanggaran</p>
            <p class="text-2xl font-bold text-surface-800 dark:text-surface-100" x-text="counts.total_pelanggaran"></p>
        </div>
    </div>

    {{-- Filter + auto-refresh --}}
    <div class="flex items-center gap-3 mb-4">
        <select x-model="filterStatus" @change="applyFilter()" class="input-base !w-auto">
            <option value="">Semua Status</option>
            <option value="berlangsung">Berlangsung</option>
            <option value="selesai">Selesai</option>
            <option value="dikeluarkan">Dikeluarkan</option>
        </select>
        <div class="flex items-center gap-2 ml-auto text-xs text-surface-400 dark:text-surface-500">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            Auto-refresh 15 detik &bull; terakhir: <span x-text="lastRefresh"></span>
        </div>
    </div>

    {{-- Tabel peserta --}}
    <div class="card overflow-hidden">
        <table class="table-base">
            <thead>
                <tr>
                    <th>Siswa</th>
                    <th>Status</th>
                    <th class="text-right">Sisa Waktu</th>
                    <th class="text-center">Pelanggaran</th>
                    <th class="text-right">Skor</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <template x-for="a in filtered" :key="a.id">
                    <tr :class="{ 'bg-rose-50/50 dark:bg-rose-950/10': a.jumlah_pelanggaran >= {{ $exam->max_pelanggaran ?? 999 }} && a.status === 'berlangsung' }">
                        <td>
                            <p class="font-medium" x-text="a.student_nama"></p>
                            <p class="text-xs text-surface-400 dark:text-surface-500 font-mono" x-text="a.student_nis"></p>
                        </td>
                        <td>
                            <span class="badge"
                                  :class="{
                                    'badge-amber': a.status === 'berlangsung',
                                    'badge-green': a.status === 'selesai',
                                    'badge-red':   a.status === 'dikeluarkan',
                                  }"
                                  x-text="{ berlangsung: 'Berlangsung', selesai: 'Selesai', dikeluarkan: 'Dikeluarkan' }[a.status] || a.status">
                            </span>
                        </td>
                        <td class="text-right">
                            <span x-show="a.status === 'berlangsung'"
                                  :class="a.sisa_detik <= 300 ? 'text-rose-600 font-bold dark:text-rose-400' : 'text-surface-600 dark:text-surface-300'"
                                  x-text="formatTime(a.sisa_detik)">
                            </span>
                            <span x-show="a.status !== 'berlangsung'" class="text-surface-300 dark:text-surface-600">—</span>
                        </td>
                        <td class="text-center">
                            <span :class="{
                                    'text-rose-600 font-bold dark:text-rose-400': a.jumlah_pelanggaran >= {{ $exam->max_pelanggaran ?? 999 }},
                                    'text-amber-600 dark:text-amber-400': a.jumlah_pelanggaran > 0 && a.jumlah_pelanggaran < {{ $exam->max_pelanggaran ?? 999 }},
                                    'text-surface-300 dark:text-surface-600': a.jumlah_pelanggaran === 0,
                                  }"
                                  x-text="a.jumlah_pelanggaran">
                            </span>
                            <span x-show="{{ $exam->max_pelanggaran ?? 0 }} > 0"
                                  class="text-xs text-surface-300 dark:text-surface-600"
                                  x-text="'/' + {{ $exam->max_pelanggaran ?? '∞' }}"></span>
                        </td>
                        <td class="text-right font-semibold text-surface-700 dark:text-surface-200"
                            x-text="a.total_skor !== null ? a.total_skor : '—'">
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
                <tr x-show="filtered.length === 0">
                    <td colspan="6" class="py-12 text-center">
                        <p class="text-sm text-surface-400">Belum ada peserta.</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Grid bawah: Feed pelanggaran + Progress soal --}}
    <div class="grid lg:grid-cols-2 gap-5 mt-5">

        {{-- Feed pelanggaran --}}
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <h3 class="font-semibold text-surface-800 dark:text-surface-100 text-sm">Pelanggaran Terbaru</h3>
                </div>
                <button @click="toggleSound()"
                        :title="soundOn ? 'Matikan notifikasi suara' : 'Aktifkan notifikasi suara'"
                        class="text-xs px-2 py-1 rounded-lg border transition-colors"
                        :class="soundOn
                            ? 'border-emerald-300 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:border-emerald-700 dark:text-emerald-400'
                            : 'border-surface-200 dark:border-surface-700 text-surface-400'">
                    <span x-text="soundOn ? '🔔 Suara Aktif' : '🔕 Suara Mati'"></span>
                </button>
            </div>
            <div class="divide-y divide-surface-50 dark:divide-surface-800 max-h-72 overflow-y-auto scrollbar-thin">
                <template x-for="log in recentCheats" :key="log.id">
                    <div class="px-5 py-3 flex items-start gap-3"
                         :class="log._new ? 'bg-rose-50/60 dark:bg-rose-950/20' : ''">
                        <div class="w-6 h-6 rounded-lg bg-rose-50 dark:bg-rose-950/40 flex items-center justify-center flex-none mt-0.5">
                            <svg class="w-3 h-3 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <span class="font-medium text-surface-800 dark:text-surface-100 text-sm" x-text="log.student_nama"></span>
                            <span class="text-surface-400 mx-1">—</span>
                            <span class="text-surface-600 dark:text-surface-300 text-sm" x-text="log.jenis_label"></span>
                        </div>
                        <span class="text-xs text-surface-400 dark:text-surface-500 flex-none" x-text="log.terjadi_at"></span>
                    </div>
                </template>
                <div x-show="recentCheats.length === 0" class="px-5 py-8 text-center">
                    <p class="text-sm text-surface-400">Tidak ada pelanggaran.</p>
                </div>
            </div>
        </div>

        {{-- Progress per soal --}}
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <h3 class="font-semibold text-surface-800 dark:text-surface-100 text-sm">Progress per Soal</h3>
                </div>
                <span class="text-xs text-surface-400 dark:text-surface-500">
                    Peserta aktif: <span class="font-semibold text-surface-600 dark:text-surface-300" x-text="counts.berlangsung"></span>
                </span>
            </div>
            <div class="px-5 py-4 max-h-72 overflow-y-auto scrollbar-thin">
                <template x-if="soalProgress.length === 0">
                    <p class="text-sm text-surface-400 text-center py-4">Belum ada data.</p>
                </template>
                <div class="space-y-2">
                    <template x-for="soal in soalProgress" :key="soal.urutan">
                        <div class="flex items-center gap-3">
                            <span class="flex-none text-xs font-mono text-surface-400 dark:text-surface-500 w-8 text-right"
                                  x-text="'#' + soal.urutan"></span>
                            <div class="flex-1 bg-surface-100 dark:bg-surface-800 rounded-full h-2.5 overflow-hidden">
                                <div class="h-2.5 rounded-full transition-all duration-500"
                                     :class="progressColor(soal.jawab_count)"
                                     :style="`width: ${totalBerlangsung > 0 ? Math.min(100, (soal.jawab_count / totalBerlangsung) * 100) : 0}%`">
                                </div>
                            </div>
                            <span class="flex-none text-xs text-surface-500 dark:text-surface-400 w-10 text-right"
                                  x-text="soal.jawab_count + '/' + totalBerlangsung"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
function monitorPage(examId) {
    return {
        attempts: [],
        filtered: [],
        recentCheats: [],
        soalProgress: [],
        totalQuestions: 0,
        filterStatus: '',
        lastRefresh: '—',
        counts: { berlangsung: 0, selesai: 0, dikeluarkan: 0, total_pelanggaran: 0 },
        soundOn: true,
        _interval: null,
        _lastCheatId: null,
        _audioCtx: null,

        get totalBerlangsung() {
            return this.counts.berlangsung || 1;
        },

        init() {
            this.fetch();
            this._interval = setInterval(() => this.fetch(), 15_000);
        },

        async fetch() {
            const res = await fetch(`{{ route('admin.exams.monitor.json', $exam->id) }}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
            });
            if (!res.ok) return;
            const data = await res.json();

            // Deteksi pelanggaran baru
            const prevIds = new Set(this.recentCheats.map(c => c.id));
            this.recentCheats = (data.recentCheats || []).map(c => ({
                ...c,
                _new: !prevIds.has(c.id),
            }));
            const hasNew = this.recentCheats.some(c => c._new);
            if (hasNew && this._lastCheatId !== null) {
                this._playAlert();
            }
            if (this.recentCheats.length > 0) {
                this._lastCheatId = this.recentCheats[0].id;
            }

            this.attempts     = data.attempts;
            this.counts       = data.counts;
            this.soalProgress = data.soalProgress || [];
            this.totalQuestions = data.totalQuestions || 0;
            this.lastRefresh  = new Date().toLocaleTimeString('id-ID');
            this.applyFilter();

            // Reset _new flag setelah 5 detik
            setTimeout(() => {
                this.recentCheats = this.recentCheats.map(c => ({ ...c, _new: false }));
            }, 5000);
        },

        applyFilter() {
            this.filtered = this.filterStatus
                ? this.attempts.filter(a => a.status === this.filterStatus)
                : this.attempts;
        },

        formatTime(seconds) {
            if (seconds <= 0) return '00:00';
            const m = Math.floor(seconds / 60);
            const s = seconds % 60;
            return `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
        },

        progressColor(count) {
            const pct = this.counts.berlangsung > 0 ? count / this.counts.berlangsung : 0;
            if (pct >= 0.8) return 'bg-emerald-400';
            if (pct >= 0.5) return 'bg-amber-400';
            return 'bg-indigo-400';
        },

        toggleSound() {
            this.soundOn = !this.soundOn;
        },

        _playAlert() {
            if (!this.soundOn) return;
            try {
                if (!this._audioCtx) {
                    this._audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                }
                const ctx = this._audioCtx;
                [0, 0.12, 0.24].forEach((delay) => {
                    const osc  = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.frequency.value = 880;
                    osc.type = 'sine';
                    gain.gain.setValueAtTime(0.25, ctx.currentTime + delay);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + delay + 0.1);
                    osc.start(ctx.currentTime + delay);
                    osc.stop(ctx.currentTime + delay + 0.1);
                });
            } catch (e) {}
        },
    };
}
function csrf() { return document.querySelector('meta[name="csrf-token"]').content; }
</script>
@endpush
