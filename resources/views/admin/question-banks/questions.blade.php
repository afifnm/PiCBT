@extends('layouts.admin')
@section('title', "Soal — {$bank->judul}")
@section('page-title')
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.banks.index') }}" class="text-slate-400 hover:text-slate-600">Bank Soal</a>
        <span class="text-slate-300">/</span>
        <span>{{ $bank->judul }}</span>
    </div>
@endsection

@section('content')
<div x-data="questionPage({{ $bank->id }})">

    {{-- Header info + tombol --}}
    <div class="flex flex-wrap items-center gap-3 mb-5">
        <div class="flex-1">
            <p class="text-sm text-slate-400">{{ $bank->subject->nama }} &bull; {{ $questions->count() }} soal &bull; Total bobot: {{ $bank->total_bobot }}</p>
        </div>
        <button @click="openCreate('pilihan_ganda')"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition">
            + Soal PG
        </button>
        <button @click="openCreate('esai')"
                class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-xl transition">
            + Soal Esai
        </button>
    </div>

    {{-- Daftar soal --}}
    <div class="space-y-3">
        @foreach ($questions as $q)
        <div class="bg-white rounded-2xl border border-slate-200 p-5"
             x-data="{ open: false }">
            <div class="flex items-start gap-3">
                <span class="flex-none w-7 h-7 rounded-full bg-slate-100 text-slate-600 text-xs font-bold flex items-center justify-center mt-0.5">
                    {{ $q->urutan }}
                </span>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium
                            {{ $q->tipe === 'pilihan_ganda' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                            {{ $q->tipe === 'pilihan_ganda' ? 'PG' : 'Esai' }}
                        </span>
                        <span class="text-xs text-slate-400">Bobot {{ $q->bobot }}</span>
                    </div>
                    <div class="text-sm text-slate-700 line-clamp-2 prose prose-sm max-w-none">
                        {!! $q->pertanyaan !!}
                    </div>
                </div>
                <div class="flex-none flex items-center gap-2">
                    <button @click="open = !open" class="text-xs text-slate-400 hover:text-slate-600 px-2 py-1 rounded">
                        <span x-text="open ? '▲' : '▼'"></span>
                    </button>
                    <button @click="openEdit({{ $q->id }})"
                            class="text-xs px-3 py-1.5 border border-slate-200 rounded-lg hover:bg-slate-50 transition">Edit</button>
                    <button @click="confirmDelete({{ $q->id }}, '{{ addslashes(strip_tags($q->pertanyaan)) }}')"
                            class="text-xs px-3 py-1.5 border border-red-200 text-red-600 rounded-lg hover:bg-red-50 transition">Hapus</button>
                </div>
            </div>

            {{-- Detail expand: opsi PG / rubrik esai --}}
            <div x-show="open" x-collapse class="mt-4 ml-10">
                @if ($q->tipe === 'pilihan_ganda')
                    <div class="space-y-1.5">
                        @foreach ($q->options as $opt)
                            <div class="flex items-center gap-2 text-sm {{ $opt->is_correct ? 'text-green-700 font-semibold' : 'text-slate-600' }}">
                                <span class="w-5 h-5 rounded-full text-xs flex items-center justify-center border {{ $opt->is_correct ? 'border-green-400 bg-green-100' : 'border-slate-200' }}">
                                    {{ $opt->label }}
                                </span>
                                {{ $opt->teks_opsi }}
                                @if ($opt->is_correct)
                                    <span class="text-xs text-green-500">(kunci)</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-sm text-slate-500 bg-slate-50 rounded-xl p-3">
                        <p class="text-xs font-semibold text-slate-400 mb-1">Rubrik / Jawaban Acuan AI:</p>
                        {{ $q->kunci_jawaban ?? '—' }}
                    </div>
                @endif
            </div>
        </div>
        @endforeach

        @if ($questions->isEmpty())
            <div class="text-center py-12 text-slate-400 text-sm bg-white rounded-2xl border border-slate-200">
                Belum ada soal. Klik "+ Soal PG" atau "+ Soal Esai" untuk mulai.
            </div>
        @endif
    </div>

    {{-- ============================================================ --}}
    {{-- MODAL: Buat / Edit Soal --}}
    {{-- ============================================================ --}}
    <div x-show="showModal" x-cloak
         class="fixed inset-0 z-50 flex items-start justify-center bg-black/50 p-4 overflow-y-auto">
        <div @click.stop class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl my-8">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 sticky top-0 bg-white rounded-t-2xl z-10">
                <h3 class="font-semibold text-slate-800" x-text="modalTitle"></h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 text-xl">✕</button>
            </div>

            <form @submit.prevent="submitQuestion()" class="px-6 py-5 space-y-5">
                {{-- Tipe (read-only saat create, tidak bisa ganti) --}}
                <div class="flex items-center gap-3">
                    <span class="text-xs font-semibold text-slate-500">Tipe:</span>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold"
                          :class="form.tipe === 'pilihan_ganda' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'"
                          x-text="form.tipe === 'pilihan_ganda' ? 'Pilihan Ganda' : 'Esai'"></span>
                </div>

                {{-- Pertanyaan --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Pertanyaan <span class="text-red-500">*</span></label>
                    <textarea x-model="form.pertanyaan" rows="4"
                              class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-blue-400 resize-y"
                              placeholder="Tulis pertanyaan di sini..."></textarea>
                    <p x-show="errors.pertanyaan" x-text="errors.pertanyaan" class="text-xs text-red-500 mt-1"></p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Bobot <span class="text-red-500">*</span></label>
                        <input type="number" x-model="form.bobot" min="0.5" step="0.5"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-blue-400">
                        <p x-show="errors.bobot" x-text="errors.bobot" class="text-xs text-red-500 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Urutan</label>
                        <input type="number" x-model="form.urutan" min="1"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-blue-400">
                    </div>
                </div>

                {{-- Opsi PG --}}
                <div x-show="form.tipe === 'pilihan_ganda'">
                    <label class="block text-xs font-semibold text-slate-500 mb-2">
                        Opsi Jawaban <span class="text-red-500">*</span>
                        <span class="text-slate-400 font-normal ml-1">(tandai opsi yang benar)</span>
                    </label>
                    <div class="space-y-2">
                        <template x-for="(opt, i) in form.options" :key="opt.label">
                            <div class="flex items-center gap-2">
                                <input type="radio" name="correct_option"
                                       :value="opt.label"
                                       :checked="opt.is_correct"
                                       @change="setCorrect(opt.label)"
                                       class="accent-blue-600 flex-none">
                                <span class="flex-none w-6 text-xs font-bold text-slate-500" x-text="opt.label + '.'"></span>
                                <input type="text" x-model="opt.teks_opsi"
                                       class="flex-1 px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-blue-400"
                                       :placeholder="`Opsi ${opt.label}`">
                            </div>
                        </template>
                    </div>
                    <p x-show="errors.options" x-text="errors.options" class="text-xs text-red-500 mt-1"></p>
                </div>

                {{-- Kunci / Rubrik --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1"
                           x-text="form.tipe === 'pilihan_ganda' ? 'Kunci Jawaban (auto dari pilihan di atas)' : 'Rubrik / Jawaban Acuan AI'"></label>
                    <textarea x-model="form.kunci_jawaban" rows="3"
                              :readonly="form.tipe === 'pilihan_ganda'"
                              class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-blue-400 resize-none"
                              :class="form.tipe === 'pilihan_ganda' ? 'bg-slate-50 text-slate-400' : ''"
                              :placeholder="form.tipe === 'pilihan_ganda' ? 'Terisi otomatis' : 'Tulis rubrik atau jawaban acuan untuk AI...'">
                    </textarea>
                </div>

                <div x-show="formError" class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700" x-text="formError"></div>

                <div class="flex gap-3 pt-2">
                    <button type="button" @click="showModal = false"
                            class="flex-1 py-2 border border-slate-200 rounded-xl text-sm hover:bg-slate-50 transition">Batal</button>
                    <button type="submit" :disabled="submitting"
                            class="flex-1 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition disabled:opacity-50"
                            x-text="submitting ? 'Menyimpan...' : (editMode ? 'Simpan Perubahan' : 'Tambah Soal')">
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: Konfirmasi hapus --}}
    <div x-show="showDelete" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
            <div class="text-4xl mb-3">🗑</div>
            <h3 class="font-semibold text-slate-800 mb-2">Hapus Soal?</h3>
            <p class="text-sm text-slate-500 mb-5 line-clamp-2" x-text="deleteLabel"></p>
            <div class="flex gap-3">
                <button @click="showDelete = false"
                        class="flex-1 py-2 border border-slate-200 rounded-xl text-sm hover:bg-slate-50 transition">Batal</button>
                <button @click="doDelete()"
                        class="flex-1 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition">Hapus</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function questionPage(bankId) {
    const LABELS = ['A', 'B', 'C', 'D', 'E'];
    const blankOptions = () => LABELS.map(l => ({ label: l, teks_opsi: '', is_correct: l === 'A' }));

    return {
        bankId,
        showModal: false, modalTitle: '', editMode: false, editId: null,
        form: { tipe: 'pilihan_ganda', pertanyaan: '', bobot: 10, urutan: 1, kunci_jawaban: '', options: blankOptions() },
        errors: {}, formError: '', submitting: false,
        showDelete: false, deleteId: null, deleteLabel: '',

        openCreate(tipe) {
            this.editMode = false; this.editId = null;
            this.form     = { tipe, pertanyaan: '', bobot: 10, urutan: {{ $questions->count() + 1 }}, kunci_jawaban: '', options: blankOptions() };
            this.errors   = {}; this.formError = '';
            this.modalTitle = tipe === 'pilihan_ganda' ? 'Tambah Soal Pilihan Ganda' : 'Tambah Soal Esai';
            this.showModal  = true;
        },

        async openEdit(id) {
            const res  = await fetch(`{{ url('admin/questions') }}/${id}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
            });
            const data = await res.json();
            this.editMode = true; this.editId = id;
            this.form = {
                tipe:          data.tipe,
                pertanyaan:    data.pertanyaan,
                bobot:         data.bobot,
                urutan:        data.urutan,
                kunci_jawaban: data.kunci_jawaban ?? '',
                options:       data.tipe === 'pilihan_ganda'
                               ? data.options.map(o => ({ label: o.label, teks_opsi: o.teks_opsi, is_correct: o.is_correct }))
                               : blankOptions(),
            };
            this.errors = {}; this.formError = '';
            this.modalTitle = 'Edit Soal';
            this.showModal  = true;
        },

        setCorrect(label) {
            this.form.options.forEach(o => o.is_correct = o.label === label);
            this.form.kunci_jawaban = label;
        },

        async submitQuestion() {
            this.submitting = true; this.errors = {}; this.formError = '';

            // Auto-set kunci for PG
            if (this.form.tipe === 'pilihan_ganda') {
                const correct = this.form.options.find(o => o.is_correct);
                this.form.kunci_jawaban = correct?.label ?? 'A';
            }

            const url    = this.editMode
                ? `{{ url('admin/questions') }}/${this.editId}`
                : `{{ url('admin/banks') }}/${this.bankId}/questions`;
            const method = this.editMode ? 'PUT' : 'POST';

            const res  = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
                body: JSON.stringify({ ...this.form, question_bank_id: this.bankId }),
            });
            const data = await res.json();

            if (res.ok)             { this.showModal = false; window.location.reload(); }
            else if (res.status === 422) { this.errors = data.errors ?? {}; this.formError = data.message ?? ''; }
            else                    { this.formError = data.message ?? 'Terjadi kesalahan.'; }
            this.submitting = false;
        },

        confirmDelete(id, label) { this.deleteId = id; this.deleteLabel = label; this.showDelete = true; },
        async doDelete() {
            await fetch(`{{ url('admin/questions') }}/${this.deleteId}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
            });
            this.showDelete = false; window.location.reload();
        },
    };
}
function csrf() { return document.querySelector('meta[name="csrf-token"]').content; }
</script>
@endpush
