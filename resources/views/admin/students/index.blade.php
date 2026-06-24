@extends('layouts.admin')
@section('title', 'Master Siswa')
@section('page-title', 'Master Siswa')

@section('content')
<div x-data="studentPage()" x-init="init()">

    {{-- Toolbar --}}
    <div class="flex flex-wrap gap-3 mb-5">
        <div class="flex-1 min-w-48">
            <input type="text" x-model="search" @input.debounce.400ms="fetchStudents()"
                   placeholder="Cari nama / NIS..."
                   class="input-base">
        </div>
        <select x-model="filterKelas" @change="fetchStudents()"
                class="input-base !w-auto">
            <option value="">Semua Kelas</option>
            <option value="X">Kelas X</option>
            <option value="XI">Kelas XI</option>
            <option value="XII">Kelas XII</option>
        </select>
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
            Import Excel
        </button>
        <a href="{{ route('admin.students.template') }}" class="btn-ghost">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Template
        </a>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <table class="table-base">
            <thead>
                <tr>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th>Jurusan</th>
                    <th>Tahun Masuk</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <template x-for="s in students" :key="s.id">
                    <tr>
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
                                  x-text="s.kelas_sekarang"></span>
                        </td>
                        <td class="text-surface-500 dark:text-surface-400" x-text="s.jurusan || '—'"></td>
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
    <div x-show="showModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white dark:bg-surface-900 rounded-2xl shadow-soft-lg w-full max-w-md border border-surface-100 dark:border-surface-800">
            <div class="flex items-center justify-between px-6 py-4 border-b border-surface-100 dark:border-surface-800">
                <h3 class="font-semibold text-surface-800 dark:text-surface-100" x-text="modalTitle"></h3>
                <button @click="closeModal()"
                        class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-200 transition-colors p-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form @submit.prevent="submitForm()" class="px-6 py-5 space-y-4">
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

    {{-- MODAL: Import Excel --}}
    <div x-show="showImport" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white dark:bg-surface-900 rounded-2xl shadow-soft-lg w-full max-w-md border border-surface-100 dark:border-surface-800">
            <div class="flex items-center justify-between px-6 py-4 border-b border-surface-100 dark:border-surface-800">
                <h3 class="font-semibold text-surface-800 dark:text-surface-100">Import Siswa dari Excel</h3>
                <button @click="showImport = false"
                        class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-200 transition-colors p-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="px-6 py-5">
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

    {{-- MODAL: Konfirmasi hapus --}}
    <div x-show="showDelete" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white dark:bg-surface-900 rounded-2xl shadow-soft-lg w-full max-w-sm p-6 text-center border border-surface-100 dark:border-surface-800">
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

</div>
@endsection

@push('scripts')
<script>
function studentPage() {
    return {
        students: [],
        meta: {},
        loading: true,
        search: '',
        filterKelas: '',
        currentPage: 1,

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

        init() { this.fetchStudents(); },

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
            if (res.ok) { this.closeModal(); this.fetchStudents(this.currentPage); }
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
            if (res.ok) { this.showImport = false; this.fetchStudents(); alert(`Import selesai: ${data.success} berhasil, ${data.failed} gagal.`); }
            else { this.importError = data.message ?? 'Import gagal.'; }
            this.importing = false;
        },

        confirmDelete(s) { this.deleteTarget = s; this.showDelete = true; },

        async doDelete() {
            const res = await fetch(`{{ url('admin/students') }}/${this.deleteTarget.id}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
            });
            if (res.ok) { this.showDelete = false; this.fetchStudents(this.currentPage); }
        },
    };
}

function csrf() { return document.querySelector('meta[name="csrf-token"]').content; }
</script>
@endpush
