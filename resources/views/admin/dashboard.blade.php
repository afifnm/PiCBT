@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- Stat cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
    @php
        $cards = [
            [
                'label' => 'Total Siswa',
                'value' => $totalSiswa,
                'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>',
                'bg'    => 'bg-primary-50 dark:bg-primary-950/40',
                'icon_color' => 'text-primary-500',
                'value_color'=> 'text-primary-700 dark:text-primary-300',
            ],
            [
                'label' => 'Ujian Aktif',
                'value' => $ujianAktif,
                'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>',
                'bg'    => 'bg-emerald-50 dark:bg-emerald-950/40',
                'icon_color' => 'text-emerald-500',
                'value_color'=> 'text-emerald-700 dark:text-emerald-300',
            ],
            [
                'label' => 'Sedang Ujian',
                'value' => $sedangUjian,
                'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                'bg'    => 'bg-amber-50 dark:bg-amber-950/40',
                'icon_color' => 'text-amber-500',
                'value_color'=> 'text-amber-700 dark:text-amber-300',
            ],
            [
                'label' => 'Perlu Dikoreksi',
                'value' => $perluKoreksi,
                'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>',
                'bg'    => 'bg-rose-50 dark:bg-rose-950/40',
                'icon_color' => 'text-rose-500',
                'value_color'=> 'text-rose-700 dark:text-rose-300',
            ],
        ];
    @endphp

    @foreach ($cards as $card)
        <div class="card p-5 hover:shadow-soft-md transition-shadow duration-200">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-surface-400 dark:text-surface-500 font-medium uppercase tracking-wide mb-2">
                        {{ $card['label'] }}
                    </p>
                    <p class="text-3xl font-bold {{ $card['value_color'] }}">{{ $card['value'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-none {{ $card['bg'] }}">
                    <svg class="w-5 h-5 {{ $card['icon_color'] }}" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        {!! $card['icon'] !!}
                    </svg>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- AI Token Usage Card --}}
<div class="card p-5 mb-6">
    <div class="flex items-center gap-2 mb-4">
        <div class="w-7 h-7 rounded-lg bg-violet-50 dark:bg-violet-950/40 flex items-center justify-center flex-none">
            <svg class="w-4 h-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z"/>
            </svg>
        </div>
        <h2 class="font-semibold text-surface-800 dark:text-surface-100 text-sm">Penggunaan AI (Gemini)</h2>
        <span class="text-xs text-surface-400 dark:text-surface-500 ml-auto">Kumulatif sejak pertama digunakan</span>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div>
            <p class="text-xs text-surface-400 dark:text-surface-500 mb-1">Token Input</p>
            <p class="text-xl font-bold text-violet-600 dark:text-violet-400">
                {{ number_format($aiTokensInput) }}
            </p>
            <p class="text-xs text-surface-400 dark:text-surface-500 mt-0.5">prompt tokens</p>
        </div>
        <div>
            <p class="text-xs text-surface-400 dark:text-surface-500 mb-1">Token Output</p>
            <p class="text-xl font-bold text-violet-600 dark:text-violet-400">
                {{ number_format($aiTokensOutput) }}
            </p>
            <p class="text-xs text-surface-400 dark:text-surface-500 mt-0.5">response tokens</p>
        </div>
        <div>
            <p class="text-xs text-surface-400 dark:text-surface-500 mb-1">Estimasi Biaya</p>
            <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400">
                ${{ number_format($aiCostUsd, 4) }}
            </p>
            <p class="text-xs text-surface-400 dark:text-surface-500 mt-0.5">USD</p>
        </div>
        <div>
            <p class="text-xs text-surface-400 dark:text-surface-500 mb-1">Estimasi Biaya</p>
            <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400">
                Rp {{ number_format($aiCostIdr, 0, ',', '.') }}
            </p>
            <p class="text-xs text-surface-400 dark:text-surface-500 mt-0.5">IDR (~Rp16.300/$)</p>
        </div>
    </div>

    @if ($aiTokensInput === 0 && $aiTokensOutput === 0)
        <p class="text-xs text-surface-400 dark:text-surface-500 mt-3 pt-3 border-t border-surface-100 dark:border-surface-800">
            Belum ada penggunaan AI yang tercatat. Token akan terakumulasi setelah Koreksi AI digunakan.
        </p>
    @else
        <p class="text-xs text-surface-300 dark:text-surface-600 mt-3 pt-3 border-t border-surface-100 dark:border-surface-800">
            Harga referensi: Gemini 2.5 Flash — Input $0.30/1M token, Output $2.50/1M token.
        </p>
    @endif
</div>

{{-- Analytics Skor Ujian --}}
@if ($analytics->isNotEmpty())
<div class="card p-5 mb-6">
    <div class="flex items-center gap-2 mb-4">
        <div class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 flex items-center justify-center flex-none">
            <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <h2 class="font-semibold text-surface-800 dark:text-surface-100 text-sm">Analitik Skor — Ujian Selesai</h2>
        <span class="text-xs text-surface-400 dark:text-surface-500 ml-auto">5 ujian terbaru</span>
    </div>

    <div class="space-y-4">
        @foreach ($analytics as $item)
        <div>
            <div class="flex items-center justify-between mb-1.5 gap-3 flex-wrap">
                <p class="text-sm font-medium text-surface-700 dark:text-surface-200 truncate">{{ $item['judul'] }}</p>
                <div class="flex items-center gap-3 text-xs text-surface-400 dark:text-surface-500 flex-none">
                    <span>{{ $item['peserta'] }} peserta</span>
                    <span>Rata-rata: <strong class="text-surface-700 dark:text-surface-200">{{ $item['avg'] }}</strong></span>
                    <span>Tertinggi: <strong class="text-emerald-600 dark:text-emerald-400">{{ $item['max'] }}</strong></span>
                </div>
            </div>
            {{-- Distribusi skor (5 bucket: 0-19, 20-39, 40-59, 60-79, 80-100) --}}
            @php $maxBucket = max(1, max($item['buckets'])); @endphp
            <div class="flex items-end gap-1 h-10">
                @foreach ($item['buckets'] as $bi => $count)
                    @php
                        $labels = ['0–19','20–39','40–59','60–79','80–100'];
                        $pct    = round(($count / $maxBucket) * 100);
                        $colors = ['bg-red-400','bg-orange-400','bg-amber-400','bg-lime-400','bg-emerald-400'];
                    @endphp
                    <div class="flex-1 flex flex-col items-center gap-0.5" title="{{ $labels[$bi] }}: {{ $count }} siswa">
                        <div class="{{ $colors[$bi] }} rounded-t w-full transition-all"
                             style="height: {{ max(4, $pct) }}%"></div>
                        <span class="text-[9px] text-surface-400 dark:text-surface-600">{{ $labels[$bi] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @unless ($loop->last)
            <hr class="border-surface-100 dark:border-surface-800">
        @endunless
        @endforeach
    </div>
</div>
@endif

{{-- Main grid --}}
<div class="grid lg:grid-cols-2 gap-5">

    {{-- Ujian Berlangsung --}}
    <div class="card">
        <div class="card-header">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                <h2 class="font-semibold text-surface-800 dark:text-surface-100 text-sm">Ujian Berlangsung</h2>
            </div>
            <a href="{{ route('admin.exams.index') }}"
               class="text-xs text-primary-500 hover:text-primary-700 dark:hover:text-primary-300 font-medium transition-colors">
                Lihat semua →
            </a>
        </div>
        <div class="divide-y divide-surface-50 dark:divide-surface-800">
            @forelse ($activeExams as $exam)
                <div class="px-5 py-3.5 flex items-center gap-3 hover:bg-surface-50/50 dark:hover:bg-surface-800/30 transition-colors">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 flex items-center justify-center flex-none">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-sm text-surface-800 dark:text-surface-100 truncate">{{ $exam->judul }}</p>
                        <p class="text-xs text-surface-400 dark:text-surface-500 mt-0.5">
                            Kelas {{ $exam->target_kelas }} &bull;
                            Token: <code class="font-mono bg-surface-100 dark:bg-surface-800 px-1.5 py-0.5 rounded text-surface-600 dark:text-surface-300">{{ $exam->token }}</code> &bull;
                            {{ $exam->attempts()->where('status', 'berlangsung')->count() }} peserta aktif
                        </p>
                    </div>
                    <span class="badge-green flex-none">Aktif</span>
                </div>
            @empty
                <div class="px-5 py-10 text-center">
                    <svg class="w-8 h-8 text-surface-200 dark:text-surface-700 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-sm text-surface-400 dark:text-surface-500">Tidak ada ujian berlangsung</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Pelanggaran Terbaru --}}
    <div class="card">
        <div class="card-header">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <h2 class="font-semibold text-surface-800 dark:text-surface-100 text-sm">Pelanggaran Terbaru</h2>
            </div>
        </div>
        <div class="divide-y divide-surface-50 dark:divide-surface-800">
            @forelse ($recentCheats as $log)
                <div class="px-5 py-3.5 flex items-start gap-3 hover:bg-surface-50/50 dark:hover:bg-surface-800/30 transition-colors">
                    <div class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-950/40 flex items-center justify-center flex-none mt-0.5">
                        <svg class="w-4 h-4 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-surface-800 dark:text-surface-100">{{ $log->attempt->student->nama }}</p>
                        <p class="text-xs text-surface-400 dark:text-surface-500 mt-0.5">
                            {{ $log->jenis_label }} &bull; {{ $log->attempt->exam->judul }}
                        </p>
                        <p class="text-xs text-surface-300 dark:text-surface-600 mt-0.5">{{ $log->terjadi_at->diffForHumans() }}</p>
                    </div>
                </div>
            @empty
                <div class="px-5 py-10 text-center">
                    <svg class="w-8 h-8 text-surface-200 dark:text-surface-700 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-surface-400 dark:text-surface-500">Tidak ada pelanggaran tercatat</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@endsection
