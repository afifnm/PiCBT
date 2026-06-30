@extends('layouts.admin')
@section('title', "Soal — {$bank->judul}")
@section('page-title')
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.banks.index') }}"
           class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">Bank Soal</a>
        <span class="text-surface-300 dark:text-surface-600">/</span>
        <span>{{ $bank->judul }}</span>
    </div>
@endsection

@section('content')
<div x-data="questionPage({{ $bank->id }})">
    {{-- MODAL: Passcode generator AI --}}
    <template x-teleport="#modal-root">
        <div x-show="showAiPasscode" x-cloak
             @click.self="!aiVerifyingPasscode && (showAiPasscode = false)"
             class="modal-overlay">
            <div @click.stop
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="modal-panel max-w-sm">
                <div class="modal-header">
                    <div>
                        <h3>Buka Generator AI</h3>
                        <p class="text-xs font-normal text-surface-400 dark:text-surface-500 mt-0.5">
                            Masukkan passcode untuk melanjutkan.
                        </p>
                    </div>
                    <button @click="showAiPasscode = false" :disabled="aiVerifyingPasscode"
                            class="modal-close disabled:opacity-40">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="verifyAiPasscode()" class="modal-body space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/40 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5 text-center">
                            Passcode 4 Digit
                        </label>
                        <input x-ref="aiPasscodeInput" type="password" x-model="aiPasscode"
                               inputmode="numeric" pattern="[0-9]*" maxlength="4" autocomplete="off"
                               class="input-base text-center text-2xl tracking-[0.5em] font-mono"
                               placeholder="••••">
                    </div>

                    <div x-show="aiPasscodeError"
                         class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 text-center
                                dark:border-red-800 dark:bg-red-950/30 dark:text-red-400"
                         x-text="aiPasscodeError"></div>

                    <button type="submit" :disabled="aiVerifyingPasscode || aiPasscode.length !== 4"
                            class="btn-primary w-full justify-center disabled:opacity-50">
                        <svg x-show="aiVerifyingPasscode" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        <span x-text="aiVerifyingPasscode ? 'Memeriksa...' : 'Buka Fitur AI'"></span>
                    </button>
                </form>
            </div>
        </div>
    </template>

    {{-- MODAL: Generator soal AI --}}
    <template x-teleport="#modal-root">
        <div x-show="showAiGenerator" x-cloak
             @click.self="!aiGenerating && (showAiGenerator = false)"
             class="modal-overlay modal-top">
            <div @click.stop
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="modal-panel max-w-3xl my-8">
                <div class="modal-header sticky top-0 bg-white dark:bg-surface-900 rounded-t-2xl z-10">
                    <div>
                        <h3>Buat Soal dengan AI</h3>
                        <p class="text-xs font-normal text-surface-400 dark:text-surface-500 mt-0.5">
                            {{ $bank->subject->nama }} &bull; hasil divalidasi dengan format import PiCBT
                        </p>
                    </div>
                    <button @click="showAiGenerator = false" :disabled="aiGenerating" class="modal-close disabled:opacity-40">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="modal-body space-y-5">
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-800
                                dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-300">
                        AI dapat membuat kekeliruan. Periksa pertanyaan, kunci PG, dan rubrik esai sebelum mengimpor.
                    </div>

                    <form @submit.prevent="generateAiQuestions()" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">
                                Topik Materi <span class="text-red-500">*</span>
                            </label>
                            <textarea x-model="aiForm.topik" rows="3" class="input-base resize-y"
                                      placeholder="Contoh: Persamaan kuadrat, akar-akar persamaan, dan diskriminan"></textarea>
                            <p x-show="aiErrors.topik" x-text="aiErrors.topik" class="text-xs text-red-500 mt-1"></p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">Jumlah Soal PG</label>
                                <input type="number" x-model.number="aiForm.jumlah_pg" min="0" max="50" class="input-base">
                                <p x-show="aiErrors.jumlah_pg" x-text="aiErrors.jumlah_pg" class="text-xs text-red-500 mt-1"></p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">Jumlah Soal Esai</label>
                                <input type="number" x-model.number="aiForm.jumlah_esai" min="0" max="20" class="input-base">
                                <p x-show="aiErrors.jumlah_esai" x-text="aiErrors.jumlah_esai" class="text-xs text-red-500 mt-1"></p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">
                                    Kelas <span class="text-red-500">*</span>
                                </label>
                                <input type="text" x-model="aiForm.kelas" maxlength="100" class="input-base"
                                       placeholder="Contoh: X SMK / Fase E">
                                <p x-show="aiErrors.kelas" x-text="aiErrors.kelas" class="text-xs text-red-500 mt-1"></p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">
                                    Tingkat Kesulitan <span class="text-red-500">*</span>
                                </label>
                                <select x-model="aiForm.tingkat_kesulitan" class="input-base">
                                    <option value="mudah">Mudah</option>
                                    <option value="sedang">Sedang</option>
                                    <option value="sulit">Sulit</option>
                                    <option value="campuran">Campuran (30% mudah, 50% sedang, 20% sulit)</option>
                                </select>
                            </div>
                        </div>

                        <div x-show="aiError"
                             class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700
                                    dark:border-red-800 dark:bg-red-950/30 dark:text-red-400"
                             x-text="aiError"></div>

                        <button type="submit" :disabled="aiGenerating" class="btn-primary w-full justify-center disabled:opacity-50">
                            <svg x-show="aiGenerating" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                            <span x-text="aiGenerating ? 'AI sedang menyusun soal...' : 'Generate Draft Soal'"></span>
                        </button>
                    </form>

                    <div x-show="aiText" class="space-y-3 border-t border-surface-100 dark:border-surface-800 pt-5">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <p class="text-sm font-semibold text-surface-700 dark:text-surface-200">Preview format import</p>
                                <p class="text-xs text-emerald-600 dark:text-emerald-400">
                                    Valid: <span x-text="aiSummary.jumlah_pg"></span> PG +
                                    <span x-text="aiSummary.jumlah_esai"></span> esai
                                </p>
                            </div>
                            <button type="button" @click="copyAiText()" class="btn-ghost text-xs">
                                <span x-text="aiCopied ? 'Tersalin' : 'Salin Teks'"></span>
                            </button>
                        </div>
                        <textarea x-model="aiText" rows="15" class="input-base resize-y font-mono text-xs leading-relaxed"></textarea>

                        <div x-show="aiImportError"
                             class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700
                                    dark:border-red-800 dark:bg-red-950/30 dark:text-red-400"
                             x-text="aiImportError"></div>

                        <div class="flex flex-col-reverse sm:flex-row gap-3">
                            <button type="button" @click="showAiGenerator = false" class="btn-ghost flex-1 justify-center">Tutup</button>
                            <button type="button" @click="importAiQuestions()" :disabled="aiImporting"
                                    class="btn-primary flex-1 justify-center disabled:opacity-50">
                                <span x-text="aiImporting ? 'Mengimpor...' : 'Import ke Bank Soal'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- Header info + tombol --}}
    <div class="flex flex-wrap items-center gap-3 mb-5">
        <div class="flex-1">
            <p class="text-sm text-surface-400 dark:text-surface-500">
                {{ $bank->subject->nama }} &bull; {{ $questions->count() }} soal &bull; Total bobot: {{ $bank->total_bobot }}
            </p>
        </div>
        @if($questions->count() > 0)
        <button @click="confirmDeleteAll()"
                class="inline-flex items-center gap-2 px-4 py-2 border border-red-200 dark:border-red-800
                       text-red-600 dark:text-red-400 text-sm font-semibold rounded-xl
                       hover:bg-red-50 dark:hover:bg-red-950/30 transition-all duration-150">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Hapus Semua Soal
        </button>
        @endif
        <button @click="openAiGenerator()"
                class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600
                       text-white text-sm font-semibold rounded-xl transition-all duration-150 shadow-soft">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18l-.813-2.096a4.5 4.5 0 0 0-2.591-2.591L3.5 12.5l2.096-.813a4.5 4.5 0 0 0 2.591-2.591L9 7l.813 2.096a4.5 4.5 0 0 0 2.591 2.591l2.096.813-2.096.813a4.5 4.5 0 0 0-2.591 2.591ZM18.259 8.715 18 9.5l-.259-.785a3.4 3.4 0 0 0-2.456-2.456L14.5 6l.785-.259a3.4 3.4 0 0 0 2.456-2.456L18 2.5l.259.785a3.4 3.4 0 0 0 2.456 2.456L21.5 6l-.785.259a3.4 3.4 0 0 0-2.456 2.456Z"/>
            </svg>
            Buat Soal dengan AI
        </button>
        <button @click="openImport()" class="btn-ghost">
            ⬆ Import TXT
        </button>
        <button @click="openCreate('pilihan_ganda')" class="btn-primary">
            + Soal PG
        </button>
        <button @click="openCreate('esai')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-violet-600 hover:bg-violet-700
                       text-white text-sm font-semibold rounded-xl transition-all duration-150 shadow-soft">
            + Soal Esai
        </button>
    </div>

    {{-- Daftar soal --}}
    <div class="space-y-3">
        @foreach ($questions as $q)
        <div class="card p-5" x-data="{ open: false }">
            <div class="flex items-start gap-3">
                <span class="flex-none w-7 h-7 rounded-full bg-surface-100 dark:bg-surface-800
                             text-surface-600 dark:text-surface-300 text-xs font-bold
                             flex items-center justify-center mt-0.5">
                    {{ $q->urutan }}
                </span>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium
                            {{ $q->tipe === 'pilihan_ganda'
                                ? 'bg-primary-50 text-primary-700 dark:bg-primary-950/50 dark:text-primary-400'
                                : 'bg-violet-50 text-violet-700 dark:bg-violet-950/50 dark:text-violet-400' }}">
                            {{ $q->tipe === 'pilihan_ganda' ? 'PG' : 'Esai' }}
                        </span>
                        <span class="text-xs text-surface-400 dark:text-surface-500">Bobot {{ $q->bobot }}</span>
                    </div>
                    <div class="text-sm text-surface-700 dark:text-surface-200 line-clamp-2 prose prose-sm max-w-none dark:prose-invert" style="overflow-wrap: break-word;">
                        {!! $q->pertanyaan !!}
                    </div>
                </div>
                <div class="flex-none flex items-center gap-2">
                    <button @click="open = !open"
                            class="text-xs text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 px-2 py-1 rounded">
                        <span x-text="open ? '▲' : '▼'"></span>
                    </button>
                    <button @click="openEdit({{ $q->id }})"
                            class="text-xs px-3 py-1.5 border border-surface-200 dark:border-surface-700
                                   rounded-lg hover:bg-surface-50 dark:hover:bg-surface-800 transition-colors
                                   text-surface-600 dark:text-surface-300">Edit</button>
                    <button @click="confirmDelete({{ $q->id }}, {{ Js::from(strip_tags($q->pertanyaan)) }})"
                            class="text-xs px-3 py-1.5 border border-red-200 dark:border-red-900
                                   text-red-600 dark:text-red-400 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors">Hapus</button>
                </div>
            </div>

            {{-- Detail expand --}}
            <div x-show="open" x-collapse class="mt-4 ml-10">
                @if ($q->tipe === 'pilihan_ganda')
                    <div class="space-y-1.5">
                        @foreach ($q->options as $opt)
                            <div class="flex items-start gap-2 text-sm
                                {{ $opt->is_correct
                                    ? 'text-emerald-700 dark:text-emerald-400 font-semibold'
                                    : 'text-surface-600 dark:text-surface-400' }}">
                                <span class="w-5 h-5 rounded-full text-xs flex items-center justify-center border flex-none mt-0.5
                                    {{ $opt->is_correct
                                        ? 'border-emerald-400 bg-emerald-100 dark:bg-emerald-900/50'
                                        : 'border-surface-200 dark:border-surface-700' }}">
                                    {{ $opt->label }}
                                </span>
                                <div class="flex-1 prose prose-sm max-w-none dark:prose-invert" style="overflow-wrap:break-word;">
                                    {!! $opt->teks_opsi !!}
                                </div>
                                @if ($opt->is_correct)
                                    <span class="text-xs text-emerald-500 flex-none">(kunci)</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-sm text-surface-500 dark:text-surface-400
                                bg-surface-50 dark:bg-surface-800/60 rounded-xl p-3
                                border border-surface-100 dark:border-surface-700">
                        <p class="text-xs font-semibold text-surface-400 dark:text-surface-500 mb-1">Rubrik / Jawaban Acuan AI:</p>
                        <div class="prose prose-sm max-w-none dark:prose-invert">
                            {!! $q->kunci_jawaban ?? '—' !!}
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @endforeach

        @if ($questions->isEmpty())
            <div class="text-center py-12 text-surface-400 dark:text-surface-500 text-sm
                        bg-white dark:bg-surface-900 rounded-2xl border border-surface-100 dark:border-surface-800">
                Belum ada soal. Klik "+ Soal PG" atau "+ Soal Esai" untuk mulai.
            </div>
        @endif
    </div>

    {{-- MODAL: Buat / Edit Soal --}}
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

                <form @submit.prevent="submitQuestion()" class="modal-body space-y-5">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-semibold text-surface-500 dark:text-surface-400">Tipe:</span>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold"
                              :class="form.tipe === 'pilihan_ganda'
                                  ? 'bg-primary-50 text-primary-700 dark:bg-primary-950/50 dark:text-primary-400'
                                  : 'bg-violet-50 text-violet-700 dark:bg-violet-950/50 dark:text-violet-400'"
                              x-text="form.tipe === 'pilihan_ganda' ? 'Pilihan Ganda' : 'Esai'"></span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">
                            Pertanyaan <span class="text-red-500">*</span>
                        </label>
                        <div x-data="{ content: form.pertanyaan }" x-modelable="content" x-model="form.pertanyaan">
                            <div x-init="
                                const q = new Quill($el, {
                                    theme: 'snow',
                                    placeholder: 'Tulis pertanyaan di sini...',
                                    modules: { toolbar: [ ['bold', 'italic', 'underline'], [{ 'list': 'ordered'}, { 'list': 'bullet' }], ['code-block'] ] }
                                });
                                q.root.innerHTML = content || '';
                                q.on('text-change', () => { content = q.root.innerHTML; });
                                $watch('content', (val) => { if (val !== q.root.innerHTML) q.root.innerHTML = val || ''; });
                            " class="bg-white dark:bg-surface-900 rounded-b-xl border-surface-200 dark:border-surface-700 min-h-[200px]"></div>
                        </div>
                        <p x-show="errors.pertanyaan" x-text="errors.pertanyaan" class="text-xs text-red-500 mt-1"></p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">
                                Bobot <span class="text-red-500">*</span>
                            </label>
                            <input type="number" x-model="form.bobot" min="0.5" step="0.5" class="input-base">
                            <p x-show="errors.bobot" x-text="errors.bobot" class="text-xs text-red-500 mt-1"></p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">Urutan</label>
                            <input type="number" x-model="form.urutan" min="1" class="input-base">
                        </div>
                    </div>

                    {{-- Opsi PG --}}
                    <div x-show="form.tipe === 'pilihan_ganda'">
                        <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-2">
                            Opsi Jawaban <span class="text-red-500">*</span>
                            <span class="text-surface-400 font-normal ml-1">(tandai opsi yang benar)</span>
                        </label>
                        <div class="space-y-2">
                            <template x-for="(opt, i) in form.options" :key="opt.label">
                                <div class="flex items-start gap-2">
                                    <input type="radio" name="correct_option"
                                           :value="opt.label"
                                           :checked="opt.is_correct"
                                           @change="setCorrect(opt.label)"
                                           class="accent-primary-600 flex-none mt-2.5">
                                    <span class="flex-none w-6 text-xs font-bold text-surface-500 dark:text-surface-400 mt-2"
                                          x-text="opt.label + '.'"></span>
                                    <div x-data="{ content: opt.teks_opsi }" x-modelable="content" x-model="opt.teks_opsi" class="flex-1 w-full overflow-hidden">
                                        <div x-init="
                                            const q = new Quill($el, {
                                                theme: 'snow',
                                                placeholder: `Teks opsi ${opt.label}...`,
                                                modules: { toolbar: [ ['bold', 'italic', 'underline'], ['code-block'] ] }
                                            });
                                            q.root.innerHTML = content || '';
                                            q.on('text-change', () => { content = q.root.innerHTML; });
                                            $watch('content', (val) => { if (val !== q.root.innerHTML) q.root.innerHTML = val || ''; });
                                        " class="bg-white dark:bg-surface-900 rounded-b-xl border-surface-200 dark:border-surface-700 min-h-[100px]"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <p x-show="errors.options" x-text="errors.options" class="text-xs text-red-500 mt-1"></p>
                    </div>

                    {{-- Kunci / Rubrik --}}
                    <div>
                        <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5"
                               x-text="form.tipe === 'pilihan_ganda' ? 'Kunci Jawaban (auto dari pilihan di atas)' : 'Rubrik / Jawaban Acuan AI'"></label>
                        
                        {{-- Tampilan saat Pilihan Ganda (readonly) --}}
                        <textarea x-show="form.tipe === 'pilihan_ganda'" x-model="form.kunci_jawaban" rows="3" readonly
                                  class="input-base resize-y text-sm opacity-50 cursor-not-allowed font-mono w-full"
                                  placeholder="Terisi otomatis">
                        </textarea>

                        {{-- Tampilan saat Esai (Quill) --}}
                        <div x-show="form.tipe === 'esai'" x-data="{ content: form.kunci_jawaban }" x-modelable="content" x-model="form.kunci_jawaban" class="w-full">
                            <div x-init="
                                const q = new Quill($el, {
                                    theme: 'snow',
                                    placeholder: 'Tulis rubrik atau jawaban acuan...',
                                    modules: { toolbar: [ ['bold', 'italic', 'underline'], [{ 'list': 'ordered'}, { 'list': 'bullet' }] ] }
                                });
                                q.root.innerHTML = content || '';
                                q.on('text-change', () => { content = q.root.innerHTML; });
                                $watch('content', (val) => { if (val !== q.root.innerHTML) q.root.innerHTML = val || ''; });
                            " class="bg-white dark:bg-surface-900 rounded-b-xl border-surface-200 dark:border-surface-700 min-h-[150px]"></div>
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
                                x-text="submitting ? 'Menyimpan...' : (editMode ? 'Simpan Perubahan' : 'Tambah Soal')">
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- MODAL: Konfirmasi hapus soal --}}
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
                <h3 class="font-semibold text-surface-800 dark:text-surface-100 mb-2">Hapus Soal?</h3>
                <p class="text-sm text-surface-500 dark:text-surface-400 mb-6 line-clamp-2" x-text="deleteLabel"></p>
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
    </template>

    {{-- MODAL: Konfirmasi hapus SEMUA soal --}}
    <template x-teleport="#modal-root">
        <div x-show="showDeleteAll" x-cloak
             @click.self="showDeleteAll = false"
             class="modal-overlay">
            <div @click.stop
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="modal-panel max-w-sm p-6 text-center">
                <div class="w-12 h-12 rounded-2xl bg-red-50 dark:bg-red-950/40 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-surface-800 dark:text-surface-100 mb-2">Hapus Semua Soal?</h3>
                <p class="text-sm text-surface-500 dark:text-surface-400 mb-6">
                    Semua <strong>{{ $questions->count() }}</strong> soal dalam bank ini akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.
                </p>
                <div class="flex gap-3">
                    <button @click="showDeleteAll = false" class="btn-ghost flex-1 justify-center">Batal</button>
                    <button @click="doDeleteAll()" :disabled="deletingAll"
                            class="flex-1 inline-flex items-center justify-center px-4 py-2
                                   bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition-all disabled:opacity-50">
                        <span x-text="deletingAll ? 'Menghapus...' : 'Ya, Hapus Semua'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- MODAL: Import soal dari TXT --}}
    <template x-teleport="#modal-root">
        <div x-show="showImport" x-cloak
             @click.self="showImport = false"
             class="modal-overlay modal-top">
            <div @click.stop
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="modal-panel max-w-2xl my-8">
                <div class="modal-header sticky top-0 bg-white dark:bg-surface-900 rounded-t-2xl z-10">
                    <h3>Import Soal dari TXT</h3>
                    <button @click="showImport = false" class="modal-close">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitImport()" class="modal-body space-y-5">
                    {{-- Panduan singkat --}}
                    <div class="bg-surface-50 dark:bg-surface-800/60 rounded-xl p-4 border border-surface-100 dark:border-surface-700">
                        <p class="text-xs font-semibold text-surface-500 dark:text-surface-400 mb-2">Format singkat:</p>
                        <ul class="text-xs text-surface-500 dark:text-surface-400 space-y-1 list-disc list-inside">
                            <li>Pisahkan tiap soal dengan <b>baris kosong</b>.</li>
                            <li><code>TIPE:</code> <code>pg</code> atau <code>esai</code> &bull; <code>BOBOT:</code> angka &bull; <code>SOAL:</code> pertanyaan.</li>
                            <li>Opsi PG: <code>A. teks</code>, tandai kunci dengan <code>*</code> di akhir.</li>
                            <li>Esai: tambahkan <code>RUBRIK:</code> sebagai acuan AI.</li>
                        </ul>
                        <div class="flex flex-wrap gap-3 mt-3 text-xs font-semibold">
                            <a href="{{ route('admin.banks.questions.template') }}"
                               class="text-primary-600 dark:text-primary-400 hover:underline">⬇ Download template</a>
                            <a href="{{ route('admin.banks.questions.guide') }}" target="_blank"
                               class="text-primary-600 dark:text-primary-400 hover:underline">📖 Lihat panduan lengkap</a>
                        </div>
                    </div>

                    {{-- Upload file --}}
                    <div>
                        <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">
                            Unggah file .txt
                        </label>
                        <input type="file" accept=".txt,text/plain"
                               @change="importFile = $event.target.files[0] ?? null"
                               class="block w-full text-sm text-surface-600 dark:text-surface-300
                                      file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                                      file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700
                                      dark:file:bg-primary-950/50 dark:file:text-primary-400
                                      hover:file:bg-primary-100 dark:hover:file:bg-primary-900/50 cursor-pointer">
                    </div>

                    {{-- Atau paste --}}
                    <div>
                        <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">
                            Atau tempel teks soal
                        </label>
                        <textarea x-model="importText" rows="8"
                                  class="input-base resize-y font-mono text-xs"
                                  placeholder="TIPE: pg&#10;BOBOT: 10&#10;SOAL: ...&#10;A. ...&#10;B. ...*"></textarea>
                    </div>

                    {{-- Hasil --}}
                    <template x-if="importResult">
                        <div class="rounded-xl p-4 text-sm border"
                             :class="importResult.imported > 0
                                 ? 'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400'
                                 : 'bg-amber-50 dark:bg-amber-950/30 border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-400'">
                            <p class="font-semibold">
                                <span x-text="importResult.imported"></span> soal berhasil diimpor,
                                <span x-text="importResult.skipped"></span> dilewati.
                            </p>
                            <ul x-show="importResult.errors && importResult.errors.length"
                                class="mt-2 list-disc list-inside text-xs space-y-0.5">
                                <template x-for="err in importResult.errors" :key="err">
                                    <li x-text="err"></li>
                                </template>
                            </ul>
                        </div>
                    </template>

                    <div x-show="importError"
                         class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800
                                rounded-xl px-4 py-3 text-sm text-red-700 dark:text-red-400"
                         x-text="importError"></div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="showImport = false" class="btn-ghost flex-1 justify-center">Tutup</button>
                        <button type="submit" :disabled="importing"
                                class="btn-primary flex-1 justify-center disabled:opacity-50"
                                x-text="importing ? 'Mengimpor...' : 'Import Soal'">
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
    function questionPage(bankId) {
        const LABELS = ['A', 'B', 'C', 'D', 'E'];
        const blankOptions = () => LABELS.map(l => ({ label: l, teks_opsi: '', is_correct: l === 'A' }));

    return {
        bankId,
        showModal: false, modalTitle: '', editMode: false, editId: null,
        form: { tipe: 'pilihan_ganda', pertanyaan: '', bobot: 10, urutan: 1, kunci_jawaban: '', options: blankOptions() },
        errors: {}, formError: '', submitting: false,
        showDelete: false, deleteId: null, deleteLabel: '',
        showDeleteAll: false, deletingAll: false,
        showImport: false, importFile: null, importText: '', importResult: null, importError: '', importing: false,
        showAiPasscode: false, aiPasscode: '', aiPasscodeError: '', aiVerifyingPasscode: false, aiUnlocked: false,
        showAiGenerator: false, aiGenerating: false, aiImporting: false,
        aiForm: { topik: '', jumlah_pg: 10, jumlah_esai: 0, kelas: '', tingkat_kesulitan: 'campuran' },
        aiErrors: {}, aiError: '', aiText: '', aiSummary: {}, aiImportError: '', aiCopied: false,

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

            if (res.ok)              { this.showModal = false; window.location.reload(); }
            else if (res.status === 422) { this.errors = data.errors ?? {}; this.formError = data.message ?? ''; }
            else                     { this.formError = data.message ?? 'Terjadi kesalahan.'; }
            this.submitting = false;
        },

        openImport() {
            this.importFile = null; this.importText = ''; this.importResult = null;
            this.importError = ''; this.importing = false; this.showImport = true;
        },

        async submitImport() {
            this.importing = true; this.importResult = null; this.importError = '';

            const fd = new FormData();
            if (this.importFile)        fd.append('file', this.importFile);
            if (this.importText.trim()) fd.append('teks', this.importText);

            const res  = await fetch(`{{ url('admin/banks') }}/${this.bankId}/questions/import`, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
                body: fd,
            });
            const data = await res.json();

            if (res.ok) {
                this.importResult = data;
                if (data.imported > 0) { setTimeout(() => window.location.reload(), 1200); }
            } else {
                this.importError = data.message ?? 'Terjadi kesalahan saat mengimpor.';
            }
            this.importing = false;
        },

        openAiGenerator() {
            if (!this.aiUnlocked) {
                this.aiPasscode = ''; this.aiPasscodeError = ''; this.showAiPasscode = true;
                this.$nextTick(() => this.$refs.aiPasscodeInput?.focus());
                return;
            }

            this.resetAndShowAiGenerator();
        },

        async verifyAiPasscode() {
            this.aiVerifyingPasscode = true; this.aiPasscodeError = '';

            try {
                const res = await fetch(`{{ route('admin.banks.questions.ai-unlock') }}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
                    body: JSON.stringify({ passcode: this.aiPasscode }),
                });
                const data = await res.json();

                if (res.ok && data.unlocked) {
                    this.aiUnlocked = true;
                    this.showAiPasscode = false;
                    this.resetAndShowAiGenerator();
                } else {
                    this.aiPasscode = '';
                    this.aiPasscodeError = data.errors?.passcode?.[0] ?? data.message ?? 'Passcode tidak sesuai.';
                    this.$nextTick(() => this.$refs.aiPasscodeInput?.focus());
                }
            } catch (error) {
                this.aiPasscodeError = 'Koneksi terputus saat memeriksa passcode.';
            } finally {
                this.aiVerifyingPasscode = false;
            }
        },

        resetAndShowAiGenerator() {
            this.aiForm = { topik: '', jumlah_pg: 10, jumlah_esai: 0, kelas: '', tingkat_kesulitan: 'campuran' };
            this.aiErrors = {}; this.aiError = ''; this.aiText = ''; this.aiSummary = {};
            this.aiImportError = ''; this.aiCopied = false; this.showAiGenerator = true;
        },

        async generateAiQuestions() {
            this.aiGenerating = true; this.aiErrors = {}; this.aiError = '';
            this.aiText = ''; this.aiSummary = {}; this.aiImportError = '';

            try {
                const res = await fetch(`{{ url('admin/banks') }}/${this.bankId}/questions/ai-generate`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
                    body: JSON.stringify(this.aiForm),
                });
                const data = await res.json();

                if (res.ok) {
                    this.aiText = data.teks;
                    this.aiSummary = data;
                } else if (res.status === 422 && data.errors) {
                    Object.entries(data.errors).forEach(([key, messages]) => this.aiErrors[key] = messages[0]);
                    this.aiError = data.message ?? 'Periksa kembali parameter soal.';
                } else if (res.status === 403) {
                    this.aiUnlocked = false;
                    this.showAiGenerator = false;
                    this.aiPasscodeError = data.message ?? 'Masukkan kembali passcode.';
                    this.showAiPasscode = true;
                } else {
                    this.aiError = data.message ?? 'Gagal membuat soal dengan AI.';
                }
            } catch (error) {
                this.aiError = 'Koneksi terputus saat membuat soal. Silakan coba lagi.';
            } finally {
                this.aiGenerating = false;
            }
        },

        async importAiQuestions() {
            this.aiImporting = true; this.aiImportError = '';
            const fd = new FormData();
            fd.append('teks', this.aiText);

            try {
                const res = await fetch(`{{ url('admin/banks') }}/${this.bankId}/questions/import`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
                    body: fd,
                });
                const data = await res.json();

                if (res.ok && data.imported > 0 && data.skipped === 0) {
                    window.location.reload();
                } else if (res.ok) {
                    this.aiImportError = `${data.imported} soal diimpor, ${data.skipped} dilewati. ${data.errors?.join(' ') ?? ''}`;
                } else {
                    this.aiImportError = data.message ?? 'Draft tidak dapat diimpor.';
                }
            } catch (error) {
                this.aiImportError = 'Koneksi terputus saat mengimpor soal.';
            } finally {
                this.aiImporting = false;
            }
        },

        async copyAiText() {
            try {
                await navigator.clipboard.writeText(this.aiText);
                this.aiCopied = true;
                setTimeout(() => this.aiCopied = false, 1500);
            } catch (error) {
                this.aiImportError = 'Browser tidak mengizinkan penyalinan otomatis.';
            }
        },

        confirmDelete(id, label) { this.deleteId = id; this.deleteLabel = label; this.showDelete = true; },
        async doDelete() {
            await fetch(`{{ url('admin/questions') }}/${this.deleteId}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
            });
            this.showDelete = false; window.location.reload();
        },

        confirmDeleteAll() { this.showDeleteAll = true; this.deletingAll = false; },
        async doDeleteAll() {
            this.deletingAll = true;
            await fetch(`{{ url('admin/banks') }}/${this.bankId}/questions`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
            });
            this.showDeleteAll = false; window.location.reload();
        },
    };
}
function csrf() { return document.querySelector('meta[name="csrf-token"]').content; }
</script>
@endpush
