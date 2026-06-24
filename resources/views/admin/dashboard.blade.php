@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- Stat cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
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
