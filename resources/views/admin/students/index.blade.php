@extends('layouts.admin')
@section('title', 'Master Siswa')
@section('page-title', 'Master Siswa')

@section('content')
<div x-data="studentPage()" x-init="init()">

    {{-- Toolbar --}}
    <div class="flex flex-wrap gap-3 mb-5 items-center">
        <button x-show="viewMode === 'table'" @click="backToClasses()" class="btn-ghost" style="padding-left: 0.5rem">
            <svg class="w-5 h-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </button>

        <div x-show="viewMode === 'table'" class="flex-1 min-w-0">
            <input type="text" x-model="search" @input.debounce.400ms="fetchStudents()"
                   placeholder="Cari nama / NIS..."
                   class="input-base">
        </div>
        
        <select x-show="viewMode === 'table'" x-model="filterKelas" @change="fetchStudents()"
                class="input-base !w-auto">
            <option value="">Semua Kelas</option>
            <template x-for="c in classesList" :key="c.nama_kelas">
                <option :value="c.nama_kelas" x-text="c.nama_kelas"></option>
            </template>
        </select>

        <div class="flex gap-2" :class="viewMode === 'cards' ? 'ml-auto' : ''">
            <button @click="openCreate()" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Siswa
            </button>
            <button @click="openImport()" class="btn-ghost">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Import
            </button>
            <a href="{{ route('admin.students.template') }}" class="btn-ghost" title="Template Excel">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>
        </div>
    </div>

    {{-- Cards View --}}
    <div x-show="viewMode === 'cards'" x-cloak>
        <div x-show="loadingClasses" class="py-12 text-center">
            <div class="flex items-center justify-center gap-2 text-surface-400">
                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                <span>Memuat kelas...</span>
            </div>
        </div>
        <div x-show="!loadingClasses" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <template x-for="c in classesList" :key="c.nama_kelas">
                <div @click="openClass(c.nama_kelas)" 
                     class="card p-5 cursor-pointer border border-surface-200 dark:border-surface-800 hover:border-primary-500 dark:hover:border-primary-500 hover:shadow-md hover:shadow-primary-500/10 transition-all flex flex-col items-center justify-center text-center group">
                    <div class="w-14 h-14 rounded-2xl bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-surface-800 dark:text-surface-100" x-text="c.nama_kelas"></h3>
                    <p class="text-sm font-medium text-surface-500 mt-1"><span x-text="c.count"></span> Siswa</p>
                </div>
            </template>
            <div x-show="classesList.length === 0" class="col-span-full py-12 text-center text-surface-500 bg-surface-50 dark:bg-surface-800/50 rounded-2xl border border-dashed border-surface-200 dark:border-surface-700">
                Belum ada data siswa / kelas.
            </div>
        </div>
    </div>

    {{-- Table View --}}
    <div class="card overflow-hidden" x-show="viewMode === 'table'" x-cloak
         x-data="{ detailSheet: null }">

        {{-- Mobile: card list --}}
        <div class="sm:hidden divide-y divide-surface-100 dark:divide-surface-800">
            <template x-for="(s, i) in students" :key="s.id">
                <button @click="detailSheet = s"
                        class="w-full flex items-center gap-3 px-4 py-3 hover:bg-surface-50 dark:hover:bg-surface-800/60 transition-colors text-left">
                    <div class="w-9 h-9 rounded-xl bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold text-sm flex-none">
                        <span x-text="s.nama.charAt(0).toUpperCase()"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-surface-800 dark:text-surface-100 truncate" x-text="s.nama"></p>
                        <p class="text-xs text-surface-400 mt-0.5" x-text="`${s.nis} · ${s.nama_kelas}`"></p>
                    </div>
                    <svg class="w-4 h-4 text-surface-300 dark:text-surface-600 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </template>
            <div x-show="students.length === 0 && !loading" class="py-12 text-center">
                <p class="text-sm text-surface-400">Tidak ada data siswa.</p>
            </div>
            <div x-show="loading" class="py-8 flex items-center justify-center gap-2 text-surface-400">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                <span class="text-sm">Memuat data...</span>
            </div>
        </div>

        {{-- Desktop: table --}}
        <div class="hidden sm:block overflow-x-auto">
        <table class="table-base">
            <thead>
                <tr>
                    <th class="w-10">No</th>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th>Tahun Masuk</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(s, i) in students" :key="s.id">
                    <tr>
                        <td class="text-surface-600 dark:text-surface-500 text-center text-xs tabular-nums" x-text="(meta.from || 1) + i"></td>
                        <td class="font-mono text-surface-500 dark:text-surface-400" x-text="s.nis"></td>
                        <td class="font-medium" x-text="s.nama"></td>
                        <td>
                            <span class="badge"
                                  :class="{
                                    'badge-blue':  s.kelas_sekarang === 'X',
                                    'bg-violet-50 text-violet-700 dark:bg-violet-950/50 dark:text-violet-400': s.kelas_sekarang === 'XI',
                                    'badge-green': s.kelas_sekarang === 'XII',
                                    'badge-slate': s.kelas_sekarang === 'Alumni',
                                  }"
                                  x-text="s.nama_kelas"></span>
                        </td>
                        <td class="text-surface-500 dark:text-surface-400" x-text="s.tahun_masuk"></td>
                        <td>
                            <div class="flex items-center gap-2 justify-end">
                                <button @click="openEdit(s)"
                                        class="text-xs px-3 py-1.5 rounded-lg border border-surface-200 dark:border-surface-700
                                               hover:bg-surface-50 dark:hover:bg-surface-800 transition-colors text-surface-600 dark:text-surface-300">
                                    Edit
                                </button>
                                <button @click="confirmDelete(s)"
                                        class="text-xs px-3 py-1.5 rounded-lg border border-red-200 dark:border-red-900
                                               text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="students.length === 0 && !loading">
                    <td colspan="6" class="py-12 text-center">
                        <svg class="w-8 h-8 text-surface-200 dark:text-surface-700 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <p class="text-sm text-surface-400">Tidak ada data siswa.</p>
                    </td>
                </tr>
                <tr x-show="loading">
                    <td colspan="6" class="py-8 text-center">
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

        {{-- Mobile detail sheet --}}
        <template x-teleport="body">
        <div x-show="detailSheet" x-cloak
             @click.self="detailSheet = null"
             class="fixed inset-0 z-50 flex items-end sm:hidden bg-black/40 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="w-full bg-white dark:bg-surface-900 rounded-t-3xl shadow-xl pb-safe"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full">
                {{-- Handle --}}
                <div class="flex justify-center pt-3 pb-1">
                    <div class="w-10 h-1 bg-surface-200 dark:bg-surface-700 rounded-full"></div>
                </div>
                {{-- Header --}}
                <div class="flex items-center gap-3 px-5 py-3 border-b border-surface-100 dark:border-surface-800">
                    <div class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold flex-none"
                         x-text="detailSheet?.nama?.charAt(0)?.toUpperCase()"></div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-surface-800 dark:text-surface-100 truncate" x-text="detailSheet?.nama"></p>
                        <p class="text-xs text-surface-400" x-text="detailSheet?.nis"></p>
                    </div>
                    <button @click="detailSheet = null" class="p-2 rounded-xl hover:bg-surface-100 dark:hover:bg-surface-800 transition-colors">
                        <svg class="w-5 h-5 text-surface-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                {{-- Detail rows --}}
                <div class="px-5 py-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-surface-400">Kelas</span>
                        <span class="text-sm font-semibold text-surface-700 dark:text-surface-200" x-text="detailSheet?.nama_kelas"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-surface-400">Tahun Masuk</span>
                        <span class="text-sm font-semibold text-surface-700 dark:text-surface-200" x-text="detailSheet?.tahun_masuk"></span>
                    </div>
                </div>
                {{-- Actions --}}
                <div class="px-5 pb-6 flex gap-3">
                    <button @click="openEdit(detailSheet); detailSheet = null"
                            class="flex-1 py-3 rounded-xl border border-surface-200 dark:border-surface-700 text-sm font-semibold
                                   text-surface-700 dark:text-surface-200 hover:bg-surface-50 dark:hover:bg-surface-800 transition-colors">
                        Edit
                    </button>
                    <button @click="confirmDelete(detailSheet); detailSheet = null"
                            class="flex-1 py-3 rounded-xl border border-red-200 dark:border-red-900 text-sm font-semibold
                                   text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
        </template>

        {{-- Pagination --}}
        <div class="px-5 py-3 border-t border-surface-100 dark:border-surface-800
                    flex items-center justify-between text-sm text-surface-400 dark:text-surface-500"
             x-show="meta.last_page > 1">
            <span x-text="`Menampilkan ${meta.from}–${meta.to} dari ${meta.total} siswa`"></span>
            <div class="flex gap-1">
                <button @click="goPage(meta.current_page - 1)" :disabled="meta.current_page === 1"
                        class="px-3 py-1 rounded-lg border border-surface-200 dark:border-surface-700
                               disabled:opacity-40 hover:bg-surface-50 dark:hover:bg-surface-800 transition-colors">←</button>
                <button @click="goPage(meta.current_page + 1)" :disabled="meta.current_page === meta.last_page"
                        class="px-3 py-1 rounded-lg border border-surface-200 dark:border-surface-700
                               disabled:opacity-40 hover:bg-surface-50 dark:hover:bg-surface-800 transition-colors">→</button>
            </div>
        </div>
    </div>

    {{-- MODAL: Create / Edit --}}
    <template x-teleport="#modal-root">
    <div x-show="showModal" x-cloak
         @click.self="closeModal()"
         class="modal-overlay">
        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="modal-panel max-w-lg">
            <div class="modal-header">
                <h3 x-text="modalTitle"></h3>
                <button @click="closeModal()" class="modal-close">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form @submit.prevent="submitForm()" class="modal-body space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">
                        NIS <span class="text-red-500">*</span>
                    </label>
                    <input type="text" x-model="form.nis" :readonly="editMode"
                           class="input-base" placeholder="2025001">
                    <p x-show="errors.nis" x-text="errors.nis" class="text-xs text-red-500 mt-1"></p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" x-model="form.nama" class="input-base" placeholder="Ahmad Fauzi">
                    <p x-show="errors.nama" x-text="errors.nama" class="text-xs text-red-500 mt-1"></p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">
                            Tahun Masuk <span class="text-red-500">*</span>
                        </label>
                        <input type="number" x-model="form.tahun_masuk" min="2000" :max="new Date().getFullYear()"
                               class="input-base" placeholder="2025">
                        <p x-show="errors.tahun_masuk" x-text="errors.tahun_masuk" class="text-xs text-red-500 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">Jurusan</label>
                        <input type="text" x-model="form.jurusan" class="input-base" placeholder="TKJ">
                    </div>
                </div>
                <div x-show="!editMode">
                    <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">Password (opsional)</label>
                    <input type="password" x-model="form.password"
                           class="input-base" placeholder="Kosongkan untuk default (NIS)">
                </div>
                <div x-show="formError"
                     class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800
                            rounded-xl px-4 py-3 text-sm text-red-700 dark:text-red-400"
                     x-text="formError"></div>
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="closeModal()" class="btn-ghost flex-1 justify-center">
                        Batal
                    </button>
                    <button type="submit" :disabled="submitting" class="btn-primary flex-1 justify-center disabled:opacity-50">
                        <span x-show="!submitting" x-text="editMode ? 'Simpan Perubahan' : 'Tambah Siswa'"></span>
                        <span x-show="submitting" class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                            Menyimpan...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>

    {{-- MODAL: Import Excel --}}
    <template x-teleport="#modal-root">
    <div x-show="showImport" x-cloak
         @click.self="showImport = false"
         class="modal-overlay">
        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="modal-panel max-w-lg">
            <div class="modal-header">
                <h3>Import Siswa dari Excel</h3>
                <button @click="showImport = false" class="modal-close">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="border-2 border-dashed border-surface-200 dark:border-surface-700
                            rounded-xl p-6 text-center mb-4 hover:border-primary-300 dark:hover:border-primary-700 transition-colors">
                    <input type="file" id="importFile" accept=".xlsx,.xls,.csv"
                           @change="handleFileSelect" class="hidden">
                    <label for="importFile" class="cursor-pointer block">
                        <svg class="w-8 h-8 text-surface-300 dark:text-surface-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="text-sm text-surface-600 dark:text-surface-300 mb-1">Klik untuk pilih file Excel</p>
                        <p class="text-xs text-surface-400">.xlsx, .xls, .csv</p>
                        <p x-show="importFile" x-text="importFile?.name"
                           class="mt-2 text-sm font-medium text-primary-600 dark:text-primary-400"></p>
                    </label>
                </div>
                <p class="text-xs text-surface-400 dark:text-surface-500 mb-4">
                    Kolom wajib: <code class="bg-surface-100 dark:bg-surface-800 px-1 rounded">nis</code>,
                    <code class="bg-surface-100 dark:bg-surface-800 px-1 rounded">nama</code>,
                    <code class="bg-surface-100 dark:bg-surface-800 px-1 rounded">tahun_masuk</code>.
                    Opsional: jurusan, kelas.
                </p>
                <div x-show="importError"
                     class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800
                            rounded-xl px-4 py-3 text-sm text-red-700 dark:text-red-400 mb-4"
                     x-text="importError"></div>
                <div class="flex gap-3">
                    <button @click="showImport = false" class="btn-ghost flex-1 justify-center">Batal</button>
                    <button @click="submitImport()" :disabled="!importFile || importing"
                            class="btn-primary flex-1 justify-center disabled:opacity-50">
                        <span x-show="!importing">Upload & Import</span>
                        <span x-show="importing" class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                            Mengimpor...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    </template>

    {{-- MODAL: Konfirmasi hapus --}}
    <template x-teleport="#modal-root">
    <div x-show="showDelete" x-cloak
         @click.self="showDelete = false"
         class="modal-overlay">
        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="modal-panel max-w-sm p-6 text-center">
            <div class="w-12 h-12 rounded-2xl bg-red-50 dark:bg-red-950/40 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <h3 class="font-semibold text-surface-800 dark:text-surface-100 mb-2">Hapus Siswa?</h3>
            <p class="text-sm text-surface-500 dark:text-surface-400 mb-6">
                <strong x-text="deleteTarget?.nama"></strong> akan dipindah ke arsip.
            </p>
            <div class="flex gap-3">
                <button @click="showDelete = false" class="btn-ghost flex-1 justify-center">Batal</button>
                <button @click="doDelete()"
                        class="flex-1 inline-flex items-center justify-center px-4 py-2
                               bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition-all">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>
    </template>

</div>
@endsection

@push('scripts')
<script>
function studentPage() {
    return {
        students: [],
        meta: {},
        loading: false,
        search: '',
        filterKelas: '',
        currentPage: 1,

        viewMode: 'cards',
        classesList: [],
        loadingClasses: true,

        showModal: false,
        modalTitle: '',
        editMode: false,
        editId: null,
        form: { nis: '', nama: '', tahun_masuk: '', jurusan: '', password: '' },
        errors: {},
        formError: '',
        submitting: false,

        showImport: false,
        importFile: null,
        importing: false,
        importError: '',

        showDelete: false,
        deleteTarget: null,

        init() { this.fetchClasses(); },

        async fetchClasses() {
            this.loadingClasses = true;
            const res = await fetch(`{{ route('admin.students.classes-json') }}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
            });
            const data = await res.json();
            this.classesList = data.data;
            this.loadingClasses = false;
        },

        openClass(nama_kelas) {
            this.filterKelas = nama_kelas;
            this.viewMode = 'table';
            this.fetchStudents(1);
        },

        backToClasses() {
            this.viewMode = 'cards';
            this.filterKelas = '';
            this.search = '';
            this.fetchClasses();
        },

        async fetchStudents(page = 1) {
            this.loading = true;
            this.currentPage = page;
            const params = new URLSearchParams({ page, search: this.search, kelas: this.filterKelas });
            const res = await fetch(`{{ route('admin.students.json') }}?${params}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
            });
            const data = await res.json();
            this.students = data.data;
            this.meta = data.meta;
            this.loading = false;
        },

        goPage(page) {
            if (page >= 1 && page <= this.meta.last_page) this.fetchStudents(page);
        },

        openCreate() {
            this.editMode = false; this.editId = null;
            this.form = { nis: '', nama: '', tahun_masuk: new Date().getFullYear(), jurusan: '', password: '' };
            this.errors = {}; this.formError = '';
            this.modalTitle = 'Tambah Siswa'; this.showModal = true;
        },

        openEdit(s) {
            this.editMode = true; this.editId = s.id;
            this.form = { nis: s.nis, nama: s.nama, tahun_masuk: s.tahun_masuk, jurusan: s.jurusan ?? '', password: '' };
            this.errors = {}; this.formError = '';
            this.modalTitle = 'Edit Siswa'; this.showModal = true;
        },

        closeModal() { this.showModal = false; },

        async submitForm() {
            this.submitting = true; this.errors = {}; this.formError = '';
            const url = this.editMode ? `{{ url('admin/students') }}/${this.editId}` : `{{ route('admin.students.store') }}`;
            const method = this.editMode ? 'PUT' : 'POST';
            const res = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
                body: JSON.stringify(this.form),
            });
            const data = await res.json();
            if (res.ok) { 
                this.closeModal(); 
                if (this.viewMode === 'table') {
                    this.fetchStudents(this.currentPage); 
                } else {
                    this.fetchClasses();
                }
            }
            else if (res.status === 422) { this.errors = data.errors ?? {}; this.formError = data.message ?? ''; }
            else { this.formError = data.message ?? 'Terjadi kesalahan.'; }
            this.submitting = false;
        },

        openImport() { this.showImport = true; this.importFile = null; this.importError = ''; },
        handleFileSelect(e) { this.importFile = e.target.files[0]; },

        async submitImport() {
            if (!this.importFile) return;
            this.importing = true; this.importError = '';
            const fd = new FormData();
            fd.append('file', this.importFile);
            fd.append('_token', csrf());
            const res = await fetch(`{{ route('admin.students.import') }}`, { method: 'POST', body: fd });
            const data = await res.json();
            if (res.ok) { 
                this.showImport = false; 
                if (this.viewMode === 'table') {
                    this.fetchStudents();
                } else {
                    this.fetchClasses();
                }
                alert(`Import selesai: ${data.success} berhasil, ${data.failed} gagal.`); 
            }
            else { this.importError = data.message ?? 'Import gagal.'; }
            this.importing = false;
        },

        confirmDelete(s) { this.deleteTarget = s; this.showDelete = true; },

        async doDelete() {
            const res = await fetch(`{{ url('admin/students') }}/${this.deleteTarget.id}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
            });
            if (res.ok) { 
                this.showDelete = false; 
                if (this.viewMode === 'table') {
                    this.fetchStudents(this.currentPage); 
                } else {
                    this.fetchClasses();
                }
            }
        },
    };
}

function csrf() { return document.querySelector('meta[name="csrf-token"]').content; }
</script>
@endpush
