@extends('layouts.admin')
@section('title', "Detail — {$attempt->student->nama}")
@section('page-title')
    <div class="flex items-center gap-2 text-sm font-normal">
        <a href="{{ route('admin.results.index') }}"
           class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-200 transition-colors">Rekap Nilai</a>
        <span class="text-surface-300 dark:text-surface-600">/</span>
        <span class="font-semibold text-surface-800 dark:text-surface-100">{{ $attempt->student->nama }}</span>
    </div>
@endsection

@section('content')
<div x-data="detailPage()">

    {{-- Header info siswa --}}
    <div class="grid sm:grid-cols-3 gap-4 mb-6">
        <div class="card p-5 sm:col-span-2">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center font-bold text-lg flex-none"
                     style="background: linear-gradient(135deg,#7c6af6,#a78bfa); color: white;">
                    {{ strtoupper(substr($attempt->student->nama, 0, 1)) }}
                </div>
                <div>
                    <h2 class="font-bold text-surface-800 dark:text-surface-100 text-lg">{{ $attempt->student->nama }}</h2>
                    <p class="text-surface-400 dark:text-surface-500 text-sm">
                        NIS: {{ $attempt->student->nis }} &bull;
                        Kelas {{ $attempt->student->kelas_sekarang }} &bull;
                        {{ $attempt->student->jurusan }}
                    </p>
                    <p class="text-surface-400 dark:text-surface-500 text-sm mt-0.5">
                        Ujian: <strong class="text-surface-700 dark:text-surface-200">{{ $attempt->exam->judul }}</strong>
                    </p>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-3 mt-5 pt-5 border-t border-surface-100 dark:border-surface-800">
                <div class="text-center">
                    <p class="text-xs text-surface-400 dark:text-surface-500 mb-1">Total Skor</p>
                    <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $attempt->total_skor ?? '—' }}</p>
                    <p class="text-xs text-surface-400 dark:text-surface-500">dari {{ $attempt->exam->total_bobot }}</p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-surface-400 dark:text-surface-500 mb-1">Pelanggaran</p>
                    <p class="text-2xl font-bold {{ $attempt->jumlah_pelanggaran > 0 ? 'text-rose-500' : 'text-emerald-500' }}">
                        {{ $attempt->jumlah_pelanggaran }}
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-surface-400 dark:text-surface-500 mb-1">Status</p>
                    <p class="text-sm font-bold mt-1.5
                        {{ $attempt->status === 'selesai' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                        {{ match($attempt->status) {
                            'selesai' => 'Selesai',
                            'dikeluarkan' => 'Dikeluarkan',
                            default => $attempt->status,
                        } }}
                    </p>
                </div>
            </div>
        </div>

        <div class="card p-5">
            <p class="text-xs font-semibold text-surface-400 dark:text-surface-500 uppercase tracking-wide mb-3">Log Anti-Cheat</p>
            @if ($attempt->cheatLogs->isEmpty())
                <div class="flex items-center gap-2 text-sm text-emerald-600 dark:text-emerald-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Tidak ada pelanggaran.
                </div>
            @else
                <div class="space-y-2 max-h-48 overflow-y-auto scrollbar-thin">
                    @foreach ($attempt->cheatLogs as $log)
                        <div class="flex items-start gap-2 text-xs">
                            <svg class="w-3 h-3 text-rose-400 mt-0.5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div>
                                <p class="font-medium text-surface-700 dark:text-surface-200">{{ $log->jenis_label }}</p>
                                <p class="text-surface-400 dark:text-surface-500">{{ $log->terjadi_at->format('H:i:s') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Daftar jawaban --}}
    <div class="space-y-4">
        @foreach ($attempt->exam->examQuestions as $eq)
            @php
                $q   = $eq->question;
                $ans = $answersByQuestion[$q->id] ?? null;
                $num = $eq->urutan;
            @endphp

            <div class="card p-5">
                <div class="flex items-start gap-3 mb-4">
                    <span class="flex-none w-7 h-7 rounded-full bg-surface-100 dark:bg-surface-800
                                 text-surface-600 dark:text-surface-300 text-xs font-bold
                                 flex items-center justify-center">
                        {{ $num }}
                    </span>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="badge {{ $q->tipe === 'pilihan_ganda' ? 'badge-blue' : 'bg-violet-50 text-violet-700 dark:bg-violet-950/50 dark:text-violet-400' }}">
                                {{ $q->tipe === 'pilihan_ganda' ? 'PG' : 'Esai' }}
                            </span>
                            <span class="text-xs text-surface-400 dark:text-surface-500">Bobot {{ $eq->bobot_snapshot }}</span>
                        </div>
                        <div class="prose prose-sm max-w-none dark:prose-invert text-surface-800 dark:text-surface-100">
                            {!! $q->pertanyaan !!}
                        </div>
                    </div>
                </div>

                @if ($q->tipe === 'pilihan_ganda')
                    <div class="ml-10 space-y-1.5">
                        @foreach ($q->options as $opt)
                            <div class="flex items-center gap-2 text-sm px-3 py-2 rounded-xl border
                                {{
                                    $opt->is_correct
                                        ? 'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800'
                                        : ($ans?->jawaban_pg === $opt->label && ! $opt->is_correct
                                            ? 'bg-rose-50 dark:bg-rose-950/30 border-rose-200 dark:border-rose-800'
                                            : 'bg-surface-50 dark:bg-surface-800/40 border-transparent')
                                }}">
                                <span class="w-5 h-5 rounded-full text-xs flex items-center justify-center border flex-none
                                    {{ $opt->is_correct
                                        ? 'border-emerald-400 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-400'
                                        : 'border-surface-200 dark:border-surface-700 text-surface-500 dark:text-surface-400' }}">
                                    {{ $opt->label }}
                                </span>
                                <span class="{{ $opt->is_correct ? 'font-semibold text-emerald-700 dark:text-emerald-400' : 'text-surface-600 dark:text-surface-300' }}">
                                    {{ $opt->teks_opsi }}
                                </span>
                                @if ($ans?->jawaban_pg === $opt->label)
                                    <span class="ml-auto text-xs {{ $opt->is_correct ? 'text-emerald-500' : 'text-rose-500' }}">
                                        ← Jawaban siswa
                                    </span>
                                @endif
                                @if ($opt->is_correct && $ans?->jawaban_pg !== $opt->label)
                                    <span class="ml-auto text-xs text-emerald-500">✓ Kunci</span>
                                @endif
                            </div>
                        @endforeach

                        <div class="mt-3 flex items-center gap-3">
                            <span class="text-xs text-surface-400">Skor:</span>
                            <span class="font-bold {{ ($ans?->skor ?? 0) > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500 dark:text-rose-400' }}">
                                {{ $ans?->skor ?? 0 }} / {{ $eq->bobot_snapshot }}
                            </span>
                        </div>
                    </div>

                @else
                    <div class="ml-10"
                         x-data="essayScore({{ $ans?->id ?? 'null' }}, {{ $ans?->skor ?? 'null' }}, '{{ addslashes($ans?->ai_feedback ?? '') }}', '{{ $ans?->dinilai_oleh ?? '' }}')">
                        <div class="bg-surface-50 dark:bg-surface-800/40 rounded-xl p-4 mb-3 border border-surface-100 dark:border-surface-700">
                            <p class="text-xs font-semibold text-surface-400 dark:text-surface-500 mb-1.5">Jawaban Siswa:</p>
                            <p class="text-sm text-surface-700 dark:text-surface-200 whitespace-pre-wrap">
                                {{ $ans?->jawaban_esai ?? '(tidak dijawab)' }}
                            </p>
                        </div>

                        <div x-show="aiFeedback"
                             class="bg-primary-50 dark:bg-primary-950/30 border border-primary-100 dark:border-primary-800
                                    rounded-xl p-3 mb-3 text-xs">
                            <p class="font-semibold text-primary-600 dark:text-primary-400 mb-1">Feedback AI:</p>
                            <p class="text-surface-600 dark:text-surface-300" x-text="aiFeedback"></p>
                        </div>

                        <div class="flex items-end gap-3 flex-wrap">
                            <div>
                                <p class="text-xs text-surface-400 dark:text-surface-500 mb-1.5">
                                    Skor (<span x-text="dinilaiOleh || 'belum dinilai'"></span>):
                                </p>
                                <div class="flex items-center gap-2">
                                    <input type="number" x-model="skorInput"
                                           min="0" max="{{ $eq->bobot_snapshot }}" step="0.5"
                                           class="input-base !w-20">
                                    <span class="text-surface-400 text-sm">/ {{ $eq->bobot_snapshot }}</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-48">
                                <p class="text-xs text-surface-400 dark:text-surface-500 mb-1.5">Catatan koreksi:</p>
                                <input type="text" x-model="feedbackInput" placeholder="Opsional..."
                                       class="input-base">
                            </div>
                            <button @click="saveSkor()" :disabled="saving || answerId === null"
                                    class="btn-primary disabled:opacity-50">
                                <span x-show="!saving">Simpan</span>
                                <span x-show="saving" class="flex items-center gap-1.5">
                                    <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                                    </svg>
                                    Menyimpan
                                </span>
                            </button>
                        </div>
                        <p x-show="saved" class="text-xs text-emerald-600 dark:text-emerald-400 mt-1.5 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            Skor disimpan
                        </p>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

</div>
@endsection

@push('scripts')
<script>
function essayScore(answerId, initialSkor, initialFeedback, initialDinilai) {
    return {
        answerId,
        skorInput: initialSkor ?? 0,
        feedbackInput: '',
        aiFeedback: initialFeedback,
        dinilaiOleh: initialDinilai,
        saving: false,
        saved: false,

        async saveSkor() {
            if (this.answerId === null) return;
            this.saving = true; this.saved = false;
            const res = await fetch(`{{ url('admin/results/answer') }}/${this.answerId}/score`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
                body: JSON.stringify({ skor: this.skorInput, feedback: this.feedbackInput }),
            });
            if (res.ok) {
                const data = await res.json();
                this.dinilaiOleh = data.dinilai_oleh;
                this.saved = true;
                setTimeout(() => this.saved = false, 3000);
            }
            this.saving = false;
        },
    };
}
function detailPage() { return {}; }
function csrf() { return document.querySelector('meta[name="csrf-token"]').content; }
</script>
@endpush
