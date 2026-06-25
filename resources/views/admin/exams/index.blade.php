@extends('layouts.admin')
@section('title', 'Kelola Ujian')
@section('page-title', 'Kelola Ujian')

@section('content')
<div x-data="examPage()">

    {{-- Toolbar --}}
    <div class="flex flex-wrap gap-3 mb-5">
        <div class="flex-1 min-w-48">
            <input type="text" x-model="search" @input.debounce.400ms="fetchExams()"
                   placeholder="Cari judul ujian..." class="input-base">
        </div>
        <select x-model="filterStatus" @change="fetchExams()" class="input-base !w-auto">
            <option value="">Semua Status</option>
            <option value="draft">Draft</option>
            <option value="published">Dipublikasi</option>
            <option value="closed">Ditutup</option>
        </select>
        <button @click="openCreate()" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Ujian
        </button>
    </div>

    {{-- Tabel ujian --}}
    <div class="card overflow-hidden">
        <table class="table-base">
            <thead>
                <tr>
                    <th class="w-10">No</th>
                    <th>Ujian</th>
                    <th>Token</th>
                    <th>Kelas</th>
                    <th>Durasi</th>
                    <th>Jendela Waktu</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(e, i) in exams" :key="e.id">
                    <tr>
                        <td class="text-surface-600 dark:text-surface-500 text-center text-xs tabular-nums" x-text="i + 1"></td>
                        <td>
                            <p class="font-medium" x-text="e.judul"></p>
                            <p class="text-xs text-surface-400 dark:text-surface-500 mt-0.5"
                               x-text="`${e.jumlah_soal} soal · Total bobot ${e.total_bobot}`"></p>
                        </td>
                        <td>
                            <code class="font-mono bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-300
                                         px-2 py-0.5 rounded text-xs"
                                  x-text="e.token"></code>
                        </td>
                        <td class="font-semibold text-surface-700 dark:text-surface-200"
                            x-text="`Kelas ${e.target_kelas}`"></td>
                        <td class="text-surface-500 dark:text-surface-400"
                            x-text="`${e.durasi_menit} mnt`"></td>
                        <td class="text-xs text-surface-400 dark:text-surface-500">
                            <span x-text="e.mulai_pada ? formatDt(e.mulai_pada) : '—'"></span>
                            <span x-show="e.mulai_pada"> s/d </span>
                            <span x-text="e.selesai_pada ? formatDt(e.selesai_pada) : '—'"></span>
                        </td>
                        <td>
                            <span class="badge"
                                  :class="{
                                    'badge-slate':  e.status === 'draft',
                                    'badge-green':  e.status === 'published',
                                    'badge-red':    e.status === 'closed',
                                  }"
                                  x-text="{ draft: 'Draft', published: 'Aktif', closed: 'Ditutup' }[e.status]">
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center gap-1.5 justify-end flex-wrap">
                                <a :href="`{{ url('admin/exams') }}/${e.id}/monitor`"
                                   class="text-xs px-3 py-1.5 border border-surface-200 dark:border-surface-700
                                          rounded-lg hover:bg-surface-50 dark:hover:bg-surface-800 transition-colors
                                          text-surface-600 dark:text-surface-300">
                                    Monitor
                                </a>
                                <button @click="openEdit(e)"
                                        class="text-xs px-3 py-1.5 border border-surface-200 dark:border-surface-700
                                               rounded-lg hover:bg-surface-50 dark:hover:bg-surface-800 transition-colors
                                               text-surface-600 dark:text-surface-300">
                                    Edit
                                </button>
                                <button @click="changeStatus(e)"
                                        class="text-xs px-3 py-1.5 rounded-lg font-semibold transition-colors"
                                        :class="e.status === 'draft'
                                            ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100'
                                            : 'bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-300 hover:bg-surface-200 dark:hover:bg-surface-700'"
                                        x-text="e.status === 'draft' ? 'Publikasi' : (e.status === 'published' ? 'Tutup' : 'Buka Draft')">
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="exams.length === 0 && !loading">
                    <td colspan="8" class="py-12 text-center">
                        <svg class="w-8 h-8 text-surface-200 dark:text-surface-700 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p class="text-sm text-surface-400">Belum ada ujian.</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- MODAL: Buat / Edit Ujian --}}
    <template x-teleport="#modal-root">
    <div x-show="showModal" x-cloak
         @click.self="showModal = false"
         class="modal-overlay modal-top">
        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="modal-panel max-w-2xl my-8">
            <div class="modal-header sticky top-0 bg-white dark:bg-surface-900 rounded-t-2xl z-10">
                <h3 x-text="modalTitle"></h3>
                <button @click="showModal = false" class="modal-close">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form @submit.prevent="submitExam()" class="modal-body space-y-5">
                <div>
                    <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">
                        Judul Ujian <span class="text-red-500">*</span>
                    </label>
                    <input type="text" x-model="form.judul" class="input-base">
                    <p x-show="errors.judul" x-text="errors.judul" class="text-xs text-red-500 mt-1"></p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">
                            Bank Soal <span class="text-red-500">*</span>
                        </label>
                        <x-searchable-select
                            model="form.question_bank_id"
                            placeholder="— Pilih Bank Soal —"
                            search-placeholder="Cari bank soal..."
                            :options="$banks->map(fn ($b) => ['value' => $b->id, 'label' => $b->judul.' ('.$b->subject->nama.')'])->values()" />
                        <p x-show="errors.question_bank_id" x-text="errors.question_bank_id" class="text-xs text-red-500 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">
                            Target Kelas <span class="text-red-500">*</span>
                        </label>
                        <select x-model="form.target_kelas" class="input-base">
                            <option value="">— Pilih —</option>
                            <option value="X">Kelas X</option>
                            <option value="XI">Kelas XI</option>
                            <option value="XII">Kelas XII</option>
                        </select>
                        <p x-show="errors.target_kelas" x-text="errors.target_kelas" class="text-xs text-red-500 mt-1"></p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">
                            Durasi (menit) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" x-model="form.durasi_menit" min="1" class="input-base">
                        <p x-show="errors.durasi_menit" x-text="errors.durasi_menit" class="text-xs text-red-500 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">
                            Filter Tahun Masuk
                        </label>
                        <input type="number" x-model="form.target_tahun_masuk"
                               placeholder="Kosongkan = semua angkatan" min="2000" class="input-base">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">Mulai Ujian</label>
                        <input type="datetime-local" x-model="form.mulai_pada" class="input-base">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">Selesai Ujian</label>
                        <input type="datetime-local" x-model="form.selesai_pada" class="input-base">
                    </div>
                </div>

                {{-- Opsi ujian --}}
                <div class="bg-surface-50 dark:bg-surface-800/60 rounded-xl p-4 space-y-3 border border-surface-100 dark:border-surface-700">
                    <p class="text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase tracking-wide">Opsi Ujian</p>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2 text-sm text-surface-700 dark:text-surface-200 cursor-pointer">
                            <input type="checkbox" x-model="form.acak_soal" class="accent-primary-600 rounded">
                            Acak urutan soal
                        </label>
                        <label class="flex items-center gap-2 text-sm text-surface-700 dark:text-surface-200 cursor-pointer">
                            <input type="checkbox" x-model="form.acak_opsi" class="accent-primary-600 rounded">
                            Acak opsi PG
                        </label>
                        <label class="flex items-center gap-2 text-sm text-surface-700 dark:text-surface-200 cursor-pointer">
                            <input type="checkbox" x-model="form.auto_keluar" class="accent-red-500 rounded">
                            Auto-keluar saat batas pelanggaran
                        </label>
                        <label class="flex items-center gap-2 text-sm text-surface-700 dark:text-surface-200 cursor-pointer">
                            <input type="checkbox" x-model="form.tampilkan_peringatan" class="accent-primary-600 rounded">
                            Tampilkan peringatan kecurangan
                        </label>
                    </div>
                    <p class="text-xs text-surface-400 dark:text-surface-500 -mt-1" x-show="!form.tampilkan_peringatan">
                        Pelanggaran tetap direkam diam-diam untuk admin, tetapi siswa tidak menerima peringatan.
                    </p>
                    <div x-show="form.auto_keluar">
                        <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">
                            Maks. Pelanggaran <span class="text-red-500">*</span>
                        </label>
                        <input type="number" x-model="form.max_pelanggaran" min="1" max="99"
                               class="input-base !w-28">
                        <p class="text-xs text-surface-400 dark:text-surface-500 mt-1">
                            Siswa dikeluarkan setelah melanggar sebanyak ini.
                        </p>
                    </div>
                </div>

                {{-- Token --}}
                <div x-show="editMode">
                    <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">Token Ujian</label>
                    <div class="flex items-center gap-3">
                        <code class="font-mono bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-300
                                     px-3 py-2 rounded-xl text-sm tracking-widest border border-surface-200 dark:border-surface-700"
                              x-text="form.token"></code>
                        <span class="text-xs text-surface-400 dark:text-surface-500">Token tidak dapat diubah setelah ujian dibuat.</span>
                    </div>
                </div>

                <div x-show="formError"
                     class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800
                            rounded-xl px-4 py-3 text-sm text-red-700 dark:text-red-400"
                     x-text="formError"></div>

                <div class="flex gap-3 pt-2">
                    <button type="button" @click="showModal = false" class="btn-ghost flex-1 justify-center">Batal</button>
                    <button type="submit" :disabled="submitting"
                            class="btn-primary flex-1 justify-center disabled:opacity-50"
                            x-text="submitting ? 'Menyimpan...' : (editMode ? 'Simpan' : 'Buat Ujian & Generate Token')">
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>

</div>
@endsection

@push('scripts')
<script>
function examPage() {
    const blankForm = () => ({
        judul: '', question_bank_id: '', target_kelas: '', target_tahun_masuk: '',
        durasi_menit: 60, mulai_pada: '', selesai_pada: '',
        acak_soal: false, acak_opsi: false, auto_keluar: false,
        tampilkan_peringatan: true,
        max_pelanggaran: 3, token: '',
    });

    return {
        exams: [], loading: true, search: '', filterStatus: '',
        showModal: false, modalTitle: '', editMode: false, editId: null,
        form: blankForm(), errors: {}, formError: '', submitting: false,

        async fetchExams() {
            this.loading = true;
            const p = new URLSearchParams({ search: this.search, status: this.filterStatus });
            const res = await fetch(`{{ route('admin.exams.json') }}?${p}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
            });
            this.exams = await res.json();
            this.loading = false;
        },

        openCreate() {
            this.editMode = false; this.editId = null;
            this.form = blankForm(); this.errors = {}; this.formError = '';
            this.modalTitle = 'Buat Ujian Baru'; this.showModal = true;
        },

        openEdit(e) {
            this.editMode = true; this.editId = e.id;
            this.form = {
                judul: e.judul, question_bank_id: String(e.question_bank_id),
                target_kelas: e.target_kelas, target_tahun_masuk: e.target_tahun_masuk ?? '',
                durasi_menit: e.durasi_menit,
                mulai_pada:   e.mulai_pada  ? e.mulai_pada.slice(0, 16)  : '',
                selesai_pada: e.selesai_pada ? e.selesai_pada.slice(0, 16) : '',
                acak_soal: e.acak_soal, acak_opsi: e.acak_opsi,
                auto_keluar: e.auto_keluar, max_pelanggaran: e.max_pelanggaran ?? 3,
                tampilkan_peringatan: e.tampilkan_peringatan ?? true,
                token: e.token,
            };
            this.errors = {}; this.formError = '';
            this.modalTitle = 'Edit Ujian'; this.showModal = true;
        },

        async submitExam() {
            this.submitting = true; this.errors = {}; this.formError = '';
            const url = this.editMode ? `{{ url('admin/exams') }}/${this.editId}` : `{{ route('admin.exams.store') }}`;
            const method = this.editMode ? 'PUT' : 'POST';
            const res = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
                body: JSON.stringify(this.form),
            });
            const data = await res.json();
            if (res.ok)              { this.showModal = false; this.fetchExams(); }
            else if (res.status === 422) { this.errors = data.errors ?? {}; this.formError = data.message ?? ''; }
            else                     { this.formError = data.message ?? 'Terjadi kesalahan.'; }
            this.submitting = false;
        },

        async changeStatus(e) {
            const next  = { draft: 'published', published: 'closed', closed: 'draft' }[e.status];
            const label = { draft: 'Publikasikan', published: 'Tutup', closed: 'Kembalikan ke Draft' }[e.status];
            if (!confirm(`${label} ujian "${e.judul}"?`)) return;
            await fetch(`{{ url('admin/exams') }}/${e.id}/status`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
                body: JSON.stringify({ status: next }),
            });
            this.fetchExams();
        },

        formatDt(dt) {
            return new Date(dt).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' });
        },

        init() { this.fetchExams(); },
    };
}
function csrf() { return document.querySelector('meta[name="csrf-token"]').content; }
</script>
@endpush
