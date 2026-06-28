@extends('layouts.admin')
@section('title', 'Rekap Nilai')
@section('page-title', 'Rekap Nilai')

@section('content')
<div x-data="resultsPage()" x-init="init()">

    {{-- Tabel daftar ujian --}}
    <div class="card overflow-hidden mb-5">
        <div class="card-header flex-wrap gap-3">
            <div>
                <h3 class="font-semibold text-surface-800 dark:text-surface-100 text-sm">Daftar Ujian</h3>
                <span class="text-xs text-surface-400" x-text="filtered.length + ' ujian'"></span>
            </div>
            <div class="relative w-full sm:w-64">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-surface-400 pointer-events-none"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                </svg>
                <input type="text" x-model="searchExam" placeholder="Cari ujian / mata pelajaran / kelas..."
                       class="input-base pl-8 !py-1.5 !text-xs w-full">
            </div>
        </div>
        {{-- Mobile: card list --}}
        <div class="sm:hidden divide-y divide-surface-100 dark:divide-surface-800">
            <template x-for="(e, i) in filtered" :key="e.id">
                <a :href="e.url"
                   class="flex items-center gap-3 px-4 py-3 hover:bg-surface-50 dark:hover:bg-surface-800/60 transition-colors">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-surface-800 dark:text-surface-100 truncate" x-text="e.judul"></p>
                        <p class="text-xs text-surface-400 mt-0.5" x-text="`${e.subject} · Kelas ${e.kelas} · ${e.peserta} peserta`"></p>
                    </div>
                    <div class="flex flex-col items-end gap-1 flex-none">
                        <span class="badge"
                              :class="e.status === 'published' ? 'badge-green' : 'bg-surface-100 text-surface-500 dark:bg-surface-800 dark:text-surface-400'"
                              x-text="e.status === 'published' ? 'Aktif' : 'Selesai'"></span>
                        <template x-if="e.perlu_koreksi > 0">
                            <span class="badge-amber text-xs" x-text="e.perlu_koreksi + ' koreksi'"></span>
                        </template>
                    </div>
                    <svg class="w-4 h-4 text-surface-300 dark:text-surface-600 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </template>
            <div x-show="exams.length === 0" class="py-16 text-center text-sm text-surface-400 dark:text-surface-500">
                Belum ada ujian yang dipublikasikan atau ditutup.
            </div>
            <div x-show="exams.length > 0 && filtered.length === 0" class="py-10 text-center text-sm text-surface-400">
                Tidak ada ujian yang cocok dengan pencarian.
            </div>
        </div>

        {{-- Desktop: table --}}
        <div class="hidden sm:block overflow-x-auto">
            <table class="table-base">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Judul Ujian</th>
                        <th>Mata Pelajaran</th>
                        <th>Kelas</th>
                        <th class="text-center">Peserta</th>
                        <th class="text-center">Perlu Koreksi</th>
                        <th class="text-center">Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(e, i) in filtered" :key="e.id">
                        <tr>
                            <td class="text-surface-400" x-text="i + 1"></td>
                            <td class="font-medium text-surface-800 dark:text-surface-100" x-text="e.judul"></td>
                            <td class="text-surface-500 dark:text-surface-400" x-text="e.subject"></td>
                            <td class="text-surface-500 dark:text-surface-400" x-text="e.kelas"></td>
                            <td class="text-center font-semibold text-primary-600 dark:text-primary-400" x-text="e.peserta"></td>
                            <td class="text-center">
                                <template x-if="e.perlu_koreksi > 0">
                                    <span class="badge-amber" x-text="e.perlu_koreksi + ' soal'"></span>
                                </template>
                                <template x-if="e.perlu_koreksi === 0">
                                    <span class="text-emerald-500 text-xs font-medium">✓ Selesai</span>
                                </template>
                            </td>
                            <td class="text-center">
                                <span class="badge"
                                      :class="e.status === 'published' ? 'badge-green' : 'bg-surface-100 text-surface-500 dark:bg-surface-800 dark:text-surface-400'"
                                      x-text="e.status === 'published' ? 'Aktif' : 'Selesai'">
                                </span>
                            </td>
                            <td>
                                <a :href="e.url"
                                   class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-lg transition-colors
                                          border border-surface-200 dark:border-surface-700 hover:bg-surface-50 dark:hover:bg-surface-800
                                          text-surface-600 dark:text-surface-300">
                                    Lihat
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="exams.length === 0">
                        <td colspan="8" class="py-16 text-center">
                            <p class="text-sm text-surface-400 dark:text-surface-500">Belum ada ujian yang dipublikasikan atau ditutup.</p>
                        </td>
                    </tr>
                    <tr x-show="exams.length > 0 && filtered.length === 0">
                        <td colspan="8" class="py-10 text-center text-sm text-surface-400">
                            Tidak ada ujian yang cocok dengan pencarian.
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
function resultsPage() {
    return {
        searchExam: '',
        exams: {{ Js::from($exams->map(fn ($e) => [
            'id'            => $e->id,
            'judul'         => $e->judul,
            'subject'       => $e->questionBank->subject->nama,
            'kelas'         => $e->target_kelas,
            'peserta'       => $e->peserta_count,
            'perlu_koreksi' => $e->perlu_koreksi_count,
            'status'        => $e->status,
            'url'           => route('admin.results.exam', $e),
        ])) }},

        get filtered() {
            const q = this.searchExam.trim().toLowerCase();
            if (!q) return this.exams;
            return this.exams.filter(e =>
                e.judul.toLowerCase().includes(q) ||
                e.subject.toLowerCase().includes(q) ||
                e.kelas.toLowerCase().includes(q)
            );
        },

        init() {},
    };
}
</script>
@endpush
