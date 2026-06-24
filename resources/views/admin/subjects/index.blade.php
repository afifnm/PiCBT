@extends('layouts.admin')
@section('title', 'Mata Pelajaran')
@section('page-title', 'Mata Pelajaran')

@section('content')
<div x-data="subjectPage()">

    {{-- Toolbar --}}
    <div class="flex flex-wrap gap-3 mb-5">
        <div class="flex-1 min-w-48">
            <input type="text" x-model="search" @input.debounce.400ms="fetchSubjects()"
                   placeholder="Cari nama atau kode..." class="input-base">
        </div>
        <button @click="openCreate()" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Mata Pelajaran
        </button>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <table class="table-base">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Mata Pelajaran</th>
                    <th class="text-center">Bank Soal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <template x-for="s in subjects" :key="s.id">
                    <tr>
                        <td>
                            <code class="font-mono bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-300
                                         px-2 py-0.5 rounded text-xs font-semibold"
                                  x-text="s.kode"></code>
                        </td>
                        <td class="font-medium" x-text="s.nama"></td>
                        <td class="text-center">
                            <span class="badge badge-slate" x-text="s.question_banks_count + ' bank'"></span>
                        </td>
                        <td>
                            <div class="flex items-center gap-2 justify-end">
                                <button @click="openEdit(s)"
                                        class="text-xs px-3 py-1.5 rounded-lg border border-surface-200 dark:border-surface-700
                                               hover:bg-surface-50 dark:hover:bg-surface-800 transition-colors
                                               text-surface-600 dark:text-surface-300">
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
                <tr x-show="subjects.length === 0 && !loading">
                    <td colspan="4" class="py-14 text-center">
                        <svg class="w-8 h-8 text-surface-200 dark:text-surface-700 mx-auto mb-2"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <p class="text-sm text-surface-400 dark:text-surface-500">Belum ada mata pelajaran.</p>
                    </td>
                </tr>
                <tr x-show="loading">
                    <td colspan="4" class="py-8 text-center">
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

    {{-- MODAL: Create / Edit --}}
    <div x-show="showModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white dark:bg-surface-900 rounded-2xl shadow-soft-lg w-full max-w-sm
                    border border-surface-100 dark:border-surface-800">
            <div class="flex items-center justify-between px-6 py-4 border-b border-surface-100 dark:border-surface-800">
                <h3 class="font-semibold text-surface-800 dark:text-surface-100" x-text="modalTitle"></h3>
                <button @click="showModal = false"
                        class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-200 transition-colors p-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form @submit.prevent="submitForm()" class="px-6 py-5 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">
                        Kode <span class="text-red-500">*</span>
                    </label>
                    <input type="text" x-model="form.kode" class="input-base"
                           placeholder="MTK" maxlength="20">
                    <p x-show="errors.kode" x-text="errors.kode" class="text-xs text-red-500 mt-1"></p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">
                        Nama Mata Pelajaran <span class="text-red-500">*</span>
                    </label>
                    <input type="text" x-model="form.nama" class="input-base"
                           placeholder="Matematika">
                    <p x-show="errors.nama" x-text="errors.nama" class="text-xs text-red-500 mt-1"></p>
                </div>
                <div x-show="formError"
                     class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800
                            rounded-xl px-4 py-3 text-sm text-red-700 dark:text-red-400"
                     x-text="formError"></div>
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="showModal = false" class="btn-ghost flex-1 justify-center">
                        Batal
                    </button>
                    <button type="submit" :disabled="submitting"
                            class="btn-primary flex-1 justify-center disabled:opacity-50">
                        <span x-show="!submitting" x-text="editMode ? 'Simpan Perubahan' : 'Tambah'"></span>
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

    {{-- MODAL: Konfirmasi hapus --}}
    <div x-show="showDelete" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white dark:bg-surface-900 rounded-2xl shadow-soft-lg w-full max-w-sm p-6 text-center
                    border border-surface-100 dark:border-surface-800">
            <div class="w-12 h-12 rounded-2xl bg-red-50 dark:bg-red-950/40 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <h3 class="font-semibold text-surface-800 dark:text-surface-100 mb-2">Hapus Mata Pelajaran?</h3>
            <p class="text-sm text-surface-500 dark:text-surface-400 mb-6">
                "<span class="font-semibold text-surface-700 dark:text-surface-200" x-text="deleteTarget?.nama"></span>"
                akan dihapus permanen.
            </p>
            <div x-show="deleteError"
                 class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800
                        rounded-xl px-4 py-3 text-sm text-red-700 dark:text-red-400 mb-4 text-left"
                 x-text="deleteError"></div>
            <div class="flex gap-3">
                <button @click="showDelete = false; deleteError = ''" class="btn-ghost flex-1 justify-center">
                    Batal
                </button>
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
function subjectPage() {
    return {
        subjects: [],
        loading: true,
        search: '',

        showModal: false,
        modalTitle: '',
        editMode: false,
        editId: null,
        form: { kode: '', nama: '' },
        errors: {},
        formError: '',
        submitting: false,

        showDelete: false,
        deleteTarget: null,
        deleteError: '',

        init() { this.fetchSubjects(); },

        async fetchSubjects() {
            this.loading = true;
            const res = await fetch(`{{ route('admin.subjects.json') }}?search=${encodeURIComponent(this.search)}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
            });
            this.subjects = await res.json();
            this.loading = false;
        },

        openCreate() {
            this.editMode = false; this.editId = null;
            this.form = { kode: '', nama: '' };
            this.errors = {}; this.formError = '';
            this.modalTitle = 'Tambah Mata Pelajaran';
            this.showModal = true;
        },

        openEdit(s) {
            this.editMode = true; this.editId = s.id;
            this.form = { kode: s.kode, nama: s.nama };
            this.errors = {}; this.formError = '';
            this.modalTitle = 'Edit Mata Pelajaran';
            this.showModal = true;
        },

        async submitForm() {
            this.submitting = true; this.errors = {}; this.formError = '';
            const url    = this.editMode ? `{{ url('admin/subjects') }}/${this.editId}` : `{{ route('admin.subjects.store') }}`;
            const method = this.editMode ? 'PUT' : 'POST';
            const res = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
                body: JSON.stringify(this.form),
            });
            const data = await res.json();
            if (res.ok)              { this.showModal = false; this.fetchSubjects(); }
            else if (res.status === 422) { this.errors = data.errors ?? {}; this.formError = data.message ?? ''; }
            else                     { this.formError = data.message ?? 'Terjadi kesalahan.'; }
            this.submitting = false;
        },

        confirmDelete(s) { this.deleteTarget = s; this.deleteError = ''; this.showDelete = true; },

        async doDelete() {
            const res = await fetch(`{{ url('admin/subjects') }}/${this.deleteTarget.id}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
            });
            if (res.ok) {
                this.showDelete = false;
                this.fetchSubjects();
            } else {
                const data = await res.json();
                this.deleteError = data.message ?? 'Tidak dapat dihapus.';
            }
        },
    };
}
function csrf() { return document.querySelector('meta[name="csrf-token"]').content; }
</script>
@endpush
