@extends('layouts.admin')
@section('title', 'Bank Soal')
@section('page-title', 'Bank Soal')

@section('content')
<div x-data="bankPage()" x-init="init()">

    {{-- Toolbar --}}
    <div class="flex flex-wrap gap-3 mb-5 items-center">
        <div class="flex-1 min-w-0">
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

    {{-- Table --}}
    <div class="card overflow-hidden" x-data="{ detailSheet: null }">

        {{-- Mobile: card list --}}
        <div class="sm:hidden divide-y divide-surface-100 dark:divide-surface-800">
            <template x-for="(bank, i) in banks" :key="bank.id">
                <button @click="detailSheet = bank"
                        class="w-full flex items-center gap-3 px-4 py-3 hover:bg-surface-50 dark:hover:bg-surface-800/60 transition-colors text-left">
                    <div class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 flex items-center justify-center flex-none">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-surface-800 dark:text-surface-100 truncate" x-text="bank.judul"></p>
                        <p class="text-xs text-surface-400 mt-0.5" x-text="`${bank.subject?.nama ?? '—'} · ${bank.questions_count} soal`"></p>
                    </div>
                    <svg class="w-4 h-4 text-surface-300 dark:text-surface-600 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </template>
            <div x-show="banks.length === 0 && !loading" class="py-16 text-center">
                <p class="text-sm text-surface-400">Belum ada bank soal.</p>
                <button @click="openCreate()" class="mt-3 btn-primary text-xs">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Buat Bank Soal Pertama
                </button>
            </div>
            <div x-show="loading" class="py-10 flex items-center justify-center gap-2 text-surface-400">
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
                    <th class="w-10 text-center">No</th>
                    <th>Judul Bank Soal</th>
                    <th>Mata Pelajaran</th>
                    <th class="text-center">Jumlah Soal</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(bank, i) in banks" :key="bank.id">
                    <tr>
                        <td class="text-center text-xs text-surface-500 dark:text-surface-400 tabular-nums font-mono" x-text="i + 1"></td>
                        <td>
                            <p class="font-semibold text-surface-800 dark:text-surface-100" x-text="bank.judul"></p>
                            <p class="text-xs text-surface-400 dark:text-surface-500 mt-0.5 line-clamp-1" x-text="bank.deskripsi || '—'"></p>
                        </td>
                        <td>
                            <span class="badge badge-slate" x-text="bank.subject?.nama ?? '—'"></span>
                        </td>
                        <td class="text-center">
                            <span class="inline-flex items-center gap-1 text-sm font-semibold"
                                  :class="bank.questions_count > 0 ? 'text-primary-600 dark:text-primary-400' : 'text-surface-400 dark:text-surface-500'">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span x-text="bank.questions_count + ' soal'"></span>
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center justify-center gap-2 flex-wrap">
                                <a :href="`{{ url('admin/banks') }}/${bank.id}/questions`"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold
                                          bg-primary-50 dark:bg-primary-950/40 text-primary-700 dark:text-primary-300
                                          border border-primary-200 dark:border-primary-800
                                          hover:bg-primary-100 dark:hover:bg-primary-900/60 transition-colors">
                                    Kelola Soal
                                </a>
                                <button @click="openEdit(bank)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold
                                               border border-surface-200 dark:border-surface-700
                                               text-surface-600 dark:text-surface-300
                                               hover:bg-surface-50 dark:hover:bg-surface-800 transition-colors">
                                    Edit
                                </button>
                                <button @click="confirmDelete(bank)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold
                                               border border-red-200 dark:border-red-900 text-red-600 dark:text-red-400
                                               hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="banks.length === 0 && !loading">
                    <td colspan="5" class="py-16 text-center">
                        <svg class="w-10 h-10 text-surface-200 dark:text-surface-700 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-sm text-surface-400 dark:text-surface-500">Belum ada bank soal.</p>
                        <button @click="openCreate()" class="mt-3 btn-primary text-xs">Buat Bank Soal Pertama</button>
                    </td>
                </tr>
                <tr x-show="loading">
                    <td colspan="5" class="py-10 text-center">
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

        {{-- Summary footer --}}
        <div class="px-5 py-3 border-t border-surface-100 dark:border-surface-800 flex items-center justify-between"
             x-show="banks.length > 0">
            <span class="text-sm text-surface-400 dark:text-surface-500">
                Total <strong x-text="banks.length" class="text-surface-600 dark:text-surface-300"></strong> bank soal
            </span>
            <span class="text-xs text-surface-400 dark:text-surface-500">
                <span x-text="banks.reduce((s,b) => s + b.questions_count, 0)"></span> soal keseluruhan
            </span>
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
            <div class="w-full bg-white dark:bg-surface-900 rounded-t-3xl shadow-xl"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full">
                <div class="flex justify-center pt-3 pb-1">
                    <div class="w-10 h-1 bg-surface-200 dark:bg-surface-700 rounded-full"></div>
                </div>
                <div class="flex items-center gap-3 px-5 py-3 border-b border-surface-100 dark:border-surface-800">
                    <div class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 flex items-center justify-center flex-none">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-surface-800 dark:text-surface-100 truncate" x-text="detailSheet?.judul"></p>
                        <p class="text-xs text-surface-400" x-text="detailSheet?.subject?.nama ?? '—'"></p>
                    </div>
                    <button @click="detailSheet = null" class="p-2 rounded-xl hover:bg-surface-100 dark:hover:bg-surface-800 transition-colors">
                        <svg class="w-5 h-5 text-surface-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="px-5 py-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-surface-400">Jumlah Soal</span>
                        <span class="text-sm font-semibold text-primary-600 dark:text-primary-400" x-text="(detailSheet?.questions_count ?? 0) + ' soal'"></span>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <span class="text-sm text-surface-400">Deskripsi</span>
                        <span class="text-sm text-surface-700 dark:text-surface-200 text-right" x-text="detailSheet?.deskripsi || '—'"></span>
                    </div>
                </div>
                <div class="px-5 pb-6 flex flex-col gap-2.5">
                    <a :href="detailSheet ? `{{ url('admin/banks') }}/${detailSheet.id}/questions` : '#'"
                       class="w-full py-3 rounded-xl text-sm font-semibold text-center
                              bg-primary-600 hover:bg-primary-700 text-white transition-colors">
                        Kelola Soal
                    </a>
                    <div class="flex gap-2.5">
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
        </div>
        </template>
    </div>

    {{-- MODAL: Create/Edit Bank Soal --}}
    <template x-teleport="#modal-root">
    <div x-show="showModal" x-cloak
         @click.self="showModal = false"
         class="modal-overlay">
        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="modal-panel max-w-lg">
            <div class="modal-header">
                <h3 x-text="modalTitle"></h3>
                <button @click="showModal = false" class="modal-close">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form @submit.prevent="submitForm()" class="modal-body space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">
                        Mata Pelajaran <span class="text-red-500">*</span>
                    </label>
                    <x-searchable-select
                        model="form.subject_id"
                        placeholder="— Pilih Mapel —"
                        search-placeholder="Cari mapel..."
                        :options="$subjects->map(fn ($s) => ['value' => $s->id, 'label' => $s->nama.' ('.$s->kode.']'])->values()" />
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
                            class="btn-primary flex-1 justify-center disabled:opacity-50">
                        <svg x-show="submitting" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        <span x-text="submitting ? 'Menyimpan...' : (editMode ? 'Simpan Perubahan' : 'Buat Bank Soal')"></span>
                    </button>
                </div>
            </form>
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
            <h3 class="font-semibold text-surface-800 dark:text-surface-100 mb-2">Hapus Bank Soal?</h3>
            <p class="text-sm text-surface-500 dark:text-surface-400 mb-6">
                "<span x-text="deleteTarget?.judul"></span>" dan semua soal di dalamnya akan dihapus permanen.
            </p>

            <div class="mb-6 bg-surface-50 dark:bg-surface-800/50 rounded-xl p-3 text-left border border-surface-200 dark:border-surface-700">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" x-model="forceDelete" class="mt-1 w-4 h-4 text-red-600 rounded border-surface-300 dark:border-surface-600 focus:ring-red-500 dark:bg-surface-900">
                    <span class="text-xs text-surface-600 dark:text-surface-400">
                        <strong class="text-red-600 dark:text-red-400 font-semibold block mb-0.5">Paksa Hapus Semua Relasi</strong>
                        Centang ini jika Anda juga ingin menghapus seluruh Jadwal Ujian dan Jawaban Siswa yang terkait.
                    </span>
                </label>
            </div>

            <div x-show="deleteError"
                 class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800
                        rounded-xl px-4 py-3 text-sm text-red-700 dark:text-red-400 mb-6"
                 x-text="deleteError"></div>
            <div class="flex gap-3">
                <button @click="showDelete = false" class="btn-ghost flex-1 justify-center">Batal</button>
                <button @click="doDelete()"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2
                               bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
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
function bankPage() {
    return {
        banks: [], loading: true, search: '',
        showModal: false, modalTitle: '', editMode: false, editId: null,
        form: { subject_id: '', judul: '', deskripsi: '' },
        errors: {}, formError: '', submitting: false,
        showDelete: false, deleteTarget: null, deleteError: '', forceDelete: false,

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
            if (res.ok)                  { this.showModal = false; this.fetchBanks(); }
            else if (res.status === 422) { this.errors = data.errors ?? {}; this.formError = data.message ?? ''; }
            else                         { this.formError = data.message ?? 'Terjadi kesalahan.'; }
            this.submitting = false;
        },

        confirmDelete(b) { this.deleteTarget = b; this.deleteError = ''; this.forceDelete = false; this.showDelete = true; },

        async doDelete() {
            this.deleteError = '';
            const res = await fetch(`{{ url('admin/banks') }}/${this.deleteTarget.id}?force=${this.forceDelete ? 1 : 0}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
            });
            if (res.ok) {
                this.showDelete = false; this.fetchBanks();
            } else {
                const data = await res.json().catch(() => ({}));
                this.deleteError = data.message || 'Gagal menghapus bank soal.';
            }
        },

        init() { this.fetchBanks(); },
    };
}
function csrf() { return document.querySelector('meta[name="csrf-token"]').content; }
</script>
@endpush
