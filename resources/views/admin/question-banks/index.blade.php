@extends('layouts.admin')
@section('title', 'Bank Soal')
@section('page-title', 'Bank Soal')

@section('content')
<div x-data="bankPage()">

    {{-- Toolbar --}}
    <div class="flex flex-wrap gap-3 mb-5">
        <div class="flex-1 min-w-48">
            <input type="text" x-model="search" @input.debounce.400ms="fetchBanks()"
                   placeholder="Cari judul bank soal..." class="input-base">
        </div>
        <button @click="openCreate()" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Bank Soal Baru
        </button>
    </div>

    {{-- Grid kartu bank soal --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <template x-for="bank in banks" :key="bank.id">
            <div class="card p-5 flex flex-col gap-3 hover:shadow-soft-md transition-shadow duration-200">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-surface-800 dark:text-surface-100 truncate" x-text="bank.judul"></p>
                        <p class="text-xs text-surface-400 dark:text-surface-500 mt-0.5" x-text="bank.subject?.nama"></p>
                    </div>
                    <span class="flex-none badge badge-slate" x-text="`${bank.questions_count} soal`"></span>
                </div>
                <p class="text-sm text-surface-500 dark:text-surface-400 line-clamp-2"
                   x-text="bank.deskripsi || '—'"></p>
                <div class="flex items-center gap-2 mt-auto pt-3 border-t border-surface-100 dark:border-surface-800">
                    <a :href="`{{ url('admin/banks') }}/${bank.id}/questions`"
                       class="flex-1 text-center py-1.5 text-xs font-semibold border border-primary-200 dark:border-primary-800
                              text-primary-600 dark:text-primary-400 rounded-lg hover:bg-primary-50 dark:hover:bg-primary-950/30 transition-colors">
                        Kelola Soal
                    </a>
                    <button @click="openEdit(bank)"
                            class="py-1.5 px-3 text-xs border border-surface-200 dark:border-surface-700
                                   rounded-lg hover:bg-surface-50 dark:hover:bg-surface-800 transition-colors
                                   text-surface-600 dark:text-surface-300">
                        Edit
                    </button>
                    <button @click="confirmDelete(bank)"
                            class="py-1.5 px-3 text-xs border border-red-200 dark:border-red-900
                                   text-red-600 dark:text-red-400 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors">
                        Hapus
                    </button>
                </div>
            </div>
        </template>
        <div x-show="banks.length === 0 && !loading"
             class="sm:col-span-2 lg:col-span-3 text-center py-16">
            <svg class="w-10 h-10 text-surface-200 dark:text-surface-700 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm text-surface-400 dark:text-surface-500">Belum ada bank soal.</p>
        </div>
    </div>

    {{-- MODAL: Create/Edit Bank Soal --}}
    <div x-show="showModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white dark:bg-surface-900 rounded-2xl shadow-soft-lg w-full max-w-md border border-surface-100 dark:border-surface-800">
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
                        Mata Pelajaran <span class="text-red-500">*</span>
                    </label>
                    <select x-model="form.subject_id" class="input-base">
                        <option value="">— Pilih Mapel —</option>
                        @foreach ($subjects as $s)
                            <option value="{{ $s->id }}">{{ $s->nama }} ({{ $s->kode }})</option>
                        @endforeach
                    </select>
                    <p x-show="errors.subject_id" x-text="errors.subject_id" class="text-xs text-red-500 mt-1"></p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">
                        Judul Bank Soal <span class="text-red-500">*</span>
                    </label>
                    <input type="text" x-model="form.judul" class="input-base" placeholder="Matematika Dasar Kelas X">
                    <p x-show="errors.judul" x-text="errors.judul" class="text-xs text-red-500 mt-1"></p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">Deskripsi</label>
                    <textarea x-model="form.deskripsi" rows="3" class="input-base resize-none"
                              placeholder="Deskripsi singkat..."></textarea>
                </div>
                <div x-show="formError"
                     class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800
                            rounded-xl px-4 py-3 text-sm text-red-700 dark:text-red-400"
                     x-text="formError"></div>
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="showModal = false" class="btn-ghost flex-1 justify-center">Batal</button>
                    <button type="submit" :disabled="submitting"
                            class="btn-primary flex-1 justify-center disabled:opacity-50"
                            x-text="submitting ? 'Menyimpan...' : (editMode ? 'Simpan Perubahan' : 'Buat Bank Soal')">
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
             class="bg-white dark:bg-surface-900 rounded-2xl shadow-soft-lg w-full max-w-sm p-6 text-center border border-surface-100 dark:border-surface-800">
            <div class="w-12 h-12 rounded-2xl bg-red-50 dark:bg-red-950/40 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <h3 class="font-semibold text-surface-800 dark:text-surface-100 mb-2">Hapus Bank Soal?</h3>
            <p class="text-sm text-surface-500 dark:text-surface-400 mb-6">
                "<span x-text="deleteTarget?.judul"></span>" dan semua soal di dalamnya akan dihapus permanen.
            </p>
            <div class="flex gap-3">
                <button @click="showDelete = false" class="btn-ghost flex-1 justify-center">Batal</button>
                <button @click="doDelete()"
                        class="flex-1 inline-flex items-center justify-center px-4 py-2
                               bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition-all">
                    Hapus
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function bankPage() {
    return {
        banks: [], loading: true, search: '',
        showModal: false, modalTitle: '', editMode: false, editId: null,
        form: { subject_id: '', judul: '', deskripsi: '' },
        errors: {}, formError: '', submitting: false,
        showDelete: false, deleteTarget: null,

        async fetchBanks() {
            this.loading = true;
            const res = await fetch(`{{ route('admin.banks.json') }}?search=${this.search}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
            });
            this.banks = await res.json();
            this.loading = false;
        },

        openCreate() {
            this.editMode = false; this.editId = null;
            this.form = { subject_id: '', judul: '', deskripsi: '' };
            this.errors = {}; this.formError = '';
            this.modalTitle = 'Bank Soal Baru'; this.showModal = true;
        },

        openEdit(b) {
            this.editMode = true; this.editId = b.id;
            this.form = { subject_id: String(b.subject_id), judul: b.judul, deskripsi: b.deskripsi ?? '' };
            this.errors = {}; this.formError = '';
            this.modalTitle = 'Edit Bank Soal'; this.showModal = true;
        },

        async submitForm() {
            this.submitting = true; this.errors = {}; this.formError = '';
            const url = this.editMode ? `{{ url('admin/banks') }}/${this.editId}` : `{{ route('admin.banks.store') }}`;
            const method = this.editMode ? 'PUT' : 'POST';
            const res = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
                body: JSON.stringify(this.form),
            });
            const data = await res.json();
            if (res.ok)              { this.showModal = false; this.fetchBanks(); }
            else if (res.status === 422) { this.errors = data.errors ?? {}; this.formError = data.message ?? ''; }
            else                     { this.formError = data.message ?? 'Terjadi kesalahan.'; }
            this.submitting = false;
        },

        confirmDelete(b) { this.deleteTarget = b; this.showDelete = true; },
        async doDelete() {
            await fetch(`{{ url('admin/banks') }}/${this.deleteTarget.id}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
            });
            this.showDelete = false; this.fetchBanks();
        },

        init() { this.fetchBanks(); },
    };
}
function csrf() { return document.querySelector('meta[name="csrf-token"]').content; }
</script>
@endpush
