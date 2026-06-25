{{--
    Halaman pengerjaan ujian siswa.
    Data yang dibutuhkan dari controller:
      $attempt   : ExamAttempt (with exam.examQuestions.question.options, student)
      $questions : Collection<ExamQuestion> sudah diurutkan
      $answers   : keyed by question_id → AttemptAnswer|null
      $sisaDetik : int  (batas_waktu_at->diffInSeconds(now()))
--}}
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $attempt->exam->judul }} — PiCBT</title>
    <link rel="shortcut icon" href="/logo.webp" type="image/webp">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Disable user selection globally during exam */
        body { -webkit-user-select: none; user-select: none; }
        /* Allow text selection inside answer textarea */
        textarea { -webkit-user-select: text; user-select: text; }
        /* Fullscreen background */
        :-webkit-full-screen body, :fullscreen body { background: #f8f8fc; }
        .dark :-webkit-full-screen body, .dark :fullscreen body { background: #14142c; }
    </style>
</head>
<body class="h-full exam-bg font-sans text-surface-800 dark:text-surface-100 overflow-hidden"
      x-data="examApp({{ $attempt->id }}, {{ $sisaDetik }}, {{ $questions->count() }})"
      x-init="init()"
      @keydown.window="handleKey($event)"
      @visibilitychange.document="handleVisibility()"
      @blur.window="handleBlur()"
      @contextmenu.window.prevent="logCheat('right_click')"
      @copy.window.prevent="logCheat('key_copy')"
      @paste.window.prevent="logCheat('key_paste')"
      @cut.window.prevent="logCheat('key_copy')">

    {{-- Animated aurora blobs (decorative background) --}}
    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
        <div class="exam-blob bg-primary-400/30 dark:bg-primary-600/25 w-[28rem] h-[28rem] -top-32 -left-24" style="animation-delay:0s"></div>
        <div class="exam-blob bg-indigo-400/25 dark:bg-indigo-600/20 w-[24rem] h-[24rem] top-1/3 -right-24" style="animation-delay:-6s"></div>
        <div class="exam-blob bg-violet-300/25 dark:bg-violet-700/20 w-[22rem] h-[22rem] -bottom-28 left-1/4" style="animation-delay:-12s"></div>
    </div>

    {{-- ================================================================ --}}
    {{-- OVERLAY: Belum mulai / sudah selesai --}}
    {{-- ================================================================ --}}
    <div x-show="phase === 'start'" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 z-50 flex items-center justify-center bg-surface-950/70 backdrop-blur-md p-4">
        <div class="exam-pop relative bg-white/95 dark:bg-surface-900/95 backdrop-blur-xl rounded-3xl shadow-soft-lg border border-white/40 dark:border-surface-700/50 p-8 max-w-md w-full text-center overflow-hidden">
            {{-- top glow --}}
            <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-64 h-64 bg-primary-500/30 blur-3xl rounded-full" style="animation: examGlowPulse 4s ease-in-out infinite"></div>

            <div class="relative">
                <div class="exam-float inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-gradient-to-br from-primary-500 to-indigo-600 text-white text-4xl shadow-soft-md mb-5">📋</div>
                <h1 class="text-2xl font-bold mb-2">{{ $attempt->exam->judul }}</h1>
                <p class="text-surface-500 dark:text-surface-400 mb-1">Siswa: <strong class="text-surface-700 dark:text-surface-200">{{ $attempt->student->nama }}</strong></p>

                <div class="flex items-center justify-center gap-3 my-5">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-primary-50 dark:bg-primary-950/50 text-primary-700 dark:text-primary-300 text-sm font-semibold">
                        📝 {{ $questions->count() }} soal
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 text-sm font-semibold">
                        ⏱ {{ $attempt->exam->durasi_menit }} menit
                    </span>
                </div>

                <ul class="text-left text-sm text-surface-600 dark:text-surface-300 bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200/60 dark:border-amber-800/40 rounded-2xl p-4 mb-6 space-y-2">
                    <li class="flex gap-2"><span>⚠</span> Ujian berjalan dalam mode <strong>layar penuh</strong>.</li>
                    <li class="flex gap-2"><span>⚠</span> Dilarang berpindah tab, menyalin, atau mencetak.</li>
                    <li class="flex gap-2"><span>⚠</span> Pelanggaran akan direkam dan dilaporkan ke pengawas.</li>
                    <li class="flex gap-2"><span>⚠</span> Waktu berjalan dari server — pastikan koneksi stabil.</li>
                </ul>
                <button @click="startExam()"
                        class="group w-full py-3.5 bg-gradient-to-r from-primary-600 to-indigo-600 hover:from-primary-500 hover:to-indigo-500 text-white font-semibold rounded-2xl shadow-soft-md hover:shadow-soft-lg transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0">
                    <span class="inline-flex items-center gap-2">
                        Mulai Ujian
                        <span class="transition-transform group-hover:translate-x-1">→</span>
                    </span>
                </button>
            </div>
        </div>
    </div>

    {{-- OVERLAY: Dikeluarkan / Waktu habis --}}
    <div x-show="phase === 'done'" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 z-50 flex items-center justify-center bg-surface-950/70 backdrop-blur-md p-4">
        <div class="exam-pop relative bg-white/95 dark:bg-surface-900/95 backdrop-blur-xl rounded-3xl shadow-soft-lg border border-white/40 dark:border-surface-700/50 p-8 max-w-sm w-full text-center overflow-hidden">
            <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-56 h-56 bg-primary-500/25 blur-3xl rounded-full" style="animation: examGlowPulse 4s ease-in-out infinite"></div>
            <div class="relative">
                <div class="exam-float text-6xl mb-4" x-text="doneIcon"></div>
                <h2 class="text-xl font-bold mb-2" x-text="doneTitle"></h2>
                <p class="text-surface-500 dark:text-surface-400 text-sm mb-6" x-text="doneMessage"></p>
                <a href="{{ route('student.dashboard') }}"
                   class="group inline-flex items-center gap-2 py-3 px-7 bg-gradient-to-r from-primary-600 to-indigo-600 hover:from-primary-500 hover:to-indigo-500 text-white font-semibold rounded-2xl shadow-soft-md transition-all duration-200 hover:-translate-y-0.5">
                    <span class="transition-transform group-hover:-translate-x-1">←</span>
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>

    {{-- OVERLAY: Warning pelanggaran --}}
    <div x-show="showWarning" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 z-40 flex items-center justify-center bg-red-950/50 backdrop-blur-md p-4">
        <div class="exam-shake relative bg-white/95 dark:bg-surface-900/95 backdrop-blur-xl rounded-3xl shadow-soft-lg border-2 border-red-300/60 dark:border-red-800/50 p-6 max-w-sm w-full text-center overflow-hidden">
            <div class="absolute -top-20 left-1/2 -translate-x-1/2 w-48 h-48 bg-red-500/30 blur-3xl rounded-full"></div>
            <div class="relative">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-red-100 dark:bg-red-950/60 text-4xl mb-3" style="animation: examPulseRing 1.4s ease-out infinite">🚨</div>
                <h3 class="text-lg font-bold text-red-600 dark:text-red-400 mb-2">Pelanggaran Terdeteksi!</h3>
                <p class="text-sm text-surface-600 dark:text-surface-300 mb-4" x-text="warningText"></p>
                <p class="text-xs text-surface-400 dark:text-surface-500 mb-4">
                    Pelanggaran: <span class="font-bold text-red-500" x-text="violationCount"></span>
                    <span x-show="maxViolations > 0">/ <span x-text="maxViolations"></span></span>
                </p>
                <button @click="dismissWarning()"
                        class="py-2.5 px-6 bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold transition-all duration-150 hover:-translate-y-0.5 shadow-soft">
                    Saya Mengerti — Kembali ke Ujian
                </button>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- MAIN EXAM LAYOUT --}}
    {{-- ================================================================ --}}
    <div x-show="phase === 'exam'" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-[0.99]"
         x-transition:enter-end="opacity-100 scale-100"
         class="flex flex-col h-screen overflow-hidden">

        {{-- ---- HEADER ---- --}}
        <header class="flex-none bg-white/70 dark:bg-surface-900/70 backdrop-blur-xl border-b border-white/40 dark:border-surface-800/60 shadow-soft z-10">
            <div class="max-w-7xl mx-auto px-4 py-3 flex items-center gap-3 sm:gap-4">

                {{-- Nama ujian --}}
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <div class="flex-none w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-indigo-600 text-white flex items-center justify-center text-lg shadow-soft hidden sm:flex">📝</div>
                    <div class="min-w-0">
                        <p class="text-xs text-surface-400 dark:text-surface-500 truncate">{{ $attempt->student->nama }} &bull; {{ $attempt->student->nis }}</p>
                        <h1 class="font-bold text-surface-800 dark:text-surface-100 truncate">{{ $attempt->exam->judul }}</h1>
                    </div>
                </div>

                {{-- Timer --}}
                <div class="flex-none">
                    <div :class="timerClass()"
                         class="exam-timer flex items-center gap-2 px-4 py-2 rounded-2xl font-mono font-bold text-lg text-white shadow-soft-md transition-all duration-300">
                        <span class="text-base">⏱</span>
                        <span x-text="formatTime(sisaDetik)" class="tabular-nums tracking-wide"></span>
                    </div>
                </div>

                {{-- Progress --}}
                <div class="flex-none text-center hidden sm:block px-2">
                    <span class="text-[10px] uppercase tracking-wider text-surface-400 dark:text-surface-500">Terjawab</span>
                    <div class="font-bold text-surface-700 dark:text-surface-200 tabular-nums">
                        <span x-text="answeredCount" class="text-primary-600 dark:text-primary-400"></span><span class="text-surface-400">/</span><span x-text="totalQuestions"></span>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="flex-none">
                    <button @click="confirmSubmit()"
                            class="group px-4 py-2 bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-400 hover:to-green-500 text-white text-sm font-semibold rounded-xl shadow-soft transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="hidden sm:inline">Selesai &</span> Kumpul
                            <span class="transition-transform group-hover:translate-x-0.5">✓</span>
                        </span>
                    </button>
                </div>
            </div>

            {{-- Progress bar --}}
            <div class="h-1.5 bg-surface-100/80 dark:bg-surface-800/60">
                <div class="exam-progress h-1.5 rounded-r-full transition-all duration-500 ease-soft"
                     :style="`width: ${(answeredCount / totalQuestions) * 100}%`"></div>
            </div>
        </header>

        {{-- ---- BODY ---- --}}
        <div class="flex-1 overflow-hidden flex">

            {{-- Soal area --}}
            <main class="flex-1 overflow-y-auto p-4 sm:p-6">
                @foreach ($questions as $index => $examQuestion)
                    @php
                        $q      = $examQuestion->question;
                        $ans    = $answers[$q->id] ?? null;
                        $num    = $index + 1;
                    @endphp

                    <div x-show="currentIndex === {{ $index }}"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 scale-[0.98]"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         class="max-w-3xl mx-auto">

                        {{-- Soal card --}}
                        <div class="bg-white/80 dark:bg-surface-900/70 backdrop-blur-md rounded-3xl border border-white/50 dark:border-surface-800/60 shadow-soft p-5 sm:p-6 mb-5">
                            <div class="flex items-start gap-3">
                                <span class="flex-none w-9 h-9 rounded-2xl bg-gradient-to-br from-primary-500 to-indigo-600 text-white text-sm font-bold flex items-center justify-center shadow-soft">
                                    {{ $num }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <span class="inline-block px-2.5 py-0.5 text-xs font-semibold rounded-full mb-2
                                        {{ $q->tipe === 'pilihan_ganda' ? 'bg-primary-50 text-primary-700 dark:bg-primary-950/50 dark:text-primary-300' : 'bg-violet-50 text-violet-700 dark:bg-violet-950/50 dark:text-violet-300' }}">
                                        {{ $q->tipe === 'pilihan_ganda' ? 'Pilihan Ganda' : 'Esai' }}
                                        &bull; Bobot {{ $q->bobot }}
                                    </span>
                                    <div class="prose prose-sm max-w-none text-surface-800 dark:text-surface-100 dark:prose-invert">
                                        {!! $q->pertanyaan !!}
                                    </div>
                                    @if ($q->gambar)
                                        <img src="{{ Storage::url($q->gambar) }}"
                                             alt="Gambar soal"
                                             class="mt-3 max-h-64 rounded-2xl border border-surface-200 dark:border-surface-700">
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- PG Options --}}
                        @if ($q->tipe === 'pilihan_ganda')
                            <div class="space-y-2.5">
                                @foreach ($q->options as $opt)
                                    <label class="exam-option group flex items-center gap-3 p-3.5 rounded-2xl border-2 cursor-pointer
                                               hover:border-primary-300 hover:bg-primary-50/60 dark:hover:bg-primary-950/30
                                               has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50 dark:has-[:checked]:bg-primary-950/40 has-[:checked]:shadow-soft
                                               {{ ($ans?->jawaban_pg === $opt->label) ? 'border-primary-500 bg-primary-50 dark:bg-primary-950/40 shadow-soft' : 'border-surface-200 dark:border-surface-700 bg-white/70 dark:bg-surface-900/50' }}">
                                        <input type="radio"
                                               name="q_{{ $q->id }}"
                                               value="{{ $opt->label }}"
                                               {{ ($ans?->jawaban_pg === $opt->label) ? 'checked' : '' }}
                                               @change="saveAnswer({{ $q->id }}, 'pg', $event.target.value)"
                                               class="peer sr-only">
                                        <span class="flex-none w-8 h-8 rounded-xl flex items-center justify-center font-bold text-sm transition-all duration-200
                                                     bg-surface-100 text-surface-500 dark:bg-surface-800 dark:text-surface-400
                                                     group-hover:bg-primary-100 group-hover:text-primary-600 dark:group-hover:bg-primary-900/50
                                                     peer-checked:bg-gradient-to-br peer-checked:from-primary-500 peer-checked:to-indigo-600 peer-checked:text-white peer-checked:shadow-soft">
                                            {{ $opt->label }}
                                        </span>
                                        <span class="text-surface-700 dark:text-surface-200 peer-checked:text-surface-900 dark:peer-checked:text-white peer-checked:font-medium prose prose-sm max-w-none dark:prose-invert">{!! $opt->teks_opsi !!}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif

                        {{-- Esai --}}
                        @if ($q->tipe === 'esai')
                            <div>
                                <textarea
                                    name="q_{{ $q->id }}"
                                    rows="8"
                                    placeholder="Tulis jawaban Anda di sini..."
                                    class="w-full p-4 rounded-2xl border-2 border-surface-200 dark:border-surface-700 bg-white/70 dark:bg-surface-900/50 focus:border-primary-400 focus:ring-2 focus:ring-primary-400/30 focus:outline-none resize-none text-surface-800 dark:text-surface-100 text-sm transition-all duration-200"
                                    @input.debounce.800ms="saveAnswer({{ $q->id }}, 'esai', $event.target.value)"
                                    >{{ $ans?->jawaban_esai }}</textarea>
                                <p class="text-xs text-surface-400 dark:text-surface-500 mt-1.5 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span>
                                    Jawaban disimpan otomatis.
                                </p>
                            </div>
                        @endif

                        {{-- Ragu-ragu --}}
                        <div class="mt-4 flex items-center gap-3">
                            <label class="inline-flex items-center gap-2 text-sm text-surface-500 dark:text-surface-400 cursor-pointer select-none px-3 py-1.5 rounded-xl hover:bg-amber-50 dark:hover:bg-amber-950/30 transition-colors">
                                <input type="checkbox"
                                       :checked="doubts.includes({{ $index }})"
                                       @change="toggleDoubt({{ $index }})"
                                       class="accent-amber-500">
                                <span>🏷 Tandai ragu-ragu</span>
                            </label>
                        </div>

                        {{-- Prev / Next --}}
                        <div class="mt-6 flex gap-3">
                            <button @click="prev()"
                                    x-show="{{ $index }} > 0"
                                    class="group px-5 py-2.5 text-sm font-medium border border-surface-300 dark:border-surface-700 text-surface-600 dark:text-surface-300 rounded-xl hover:bg-white dark:hover:bg-surface-800 hover:shadow-soft transition-all duration-150 hover:-translate-y-0.5">
                                <span class="inline-flex items-center gap-1.5"><span class="transition-transform group-hover:-translate-x-0.5">←</span> Sebelumnya</span>
                            </button>
                            <button @click="next()"
                                    x-show="{{ $index }} < {{ $questions->count() - 1 }}"
                                    class="group px-5 py-2.5 text-sm font-semibold bg-gradient-to-r from-primary-600 to-indigo-600 hover:from-primary-500 hover:to-indigo-500 text-white rounded-xl shadow-soft transition-all duration-150 hover:-translate-y-0.5">
                                <span class="inline-flex items-center gap-1.5">Selanjutnya <span class="transition-transform group-hover:translate-x-0.5">→</span></span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </main>

            {{-- ---- SIDEBAR navigasi nomor soal ---- --}}
            <aside class="flex-none w-52 hidden lg:flex flex-col border-l border-white/40 dark:border-surface-800/60 bg-white/60 dark:bg-surface-900/50 backdrop-blur-xl p-4 overflow-y-auto scrollbar-thin">
                <p class="text-xs font-semibold text-surface-400 dark:text-surface-500 uppercase tracking-wider mb-3">Nomor Soal</p>
                <div class="grid grid-cols-4 gap-2">
                    @foreach ($questions as $index => $examQuestion)
                        @php $qId = $examQuestion->question_id; @endphp
                        <button
                            @click="goTo({{ $index }})"
                            :class="navBtnClass({{ $index }}, {{ $qId }})"
                            class="exam-nav-btn h-9 w-full rounded-xl text-sm font-semibold shadow-soft">
                            {{ $index + 1 }}
                        </button>
                    @endforeach
                </div>

                <div class="mt-5 space-y-2 text-xs text-surface-500 dark:text-surface-400">
                    <div class="flex items-center gap-2">
                        <span class="w-4 h-4 rounded-md bg-gradient-to-br from-emerald-400 to-green-500 inline-block"></span> Terjawab
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-4 h-4 rounded-md bg-gradient-to-br from-amber-300 to-orange-400 inline-block"></span> Ragu-ragu
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-4 h-4 rounded-md bg-surface-200 dark:bg-surface-700 inline-block"></span> Belum
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-4 h-4 rounded-md border-2 border-primary-500 inline-block"></span> Aktif
                    </div>
                </div>
            </aside>
        </div>
    </div>

    {{-- Confirm submit modal --}}
    <div x-show="showConfirmSubmit" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 z-50 flex items-center justify-center bg-surface-950/70 backdrop-blur-md p-4">
        <div class="exam-pop relative bg-white/95 dark:bg-surface-900/95 backdrop-blur-xl rounded-3xl shadow-soft-lg border border-white/40 dark:border-surface-700/50 p-6 max-w-sm w-full text-center overflow-hidden">
            <div class="absolute -top-20 left-1/2 -translate-x-1/2 w-48 h-48 bg-emerald-500/25 blur-3xl rounded-full"></div>
            <div class="relative">
                <div class="exam-float inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 text-white text-3xl shadow-soft-md mb-3">📤</div>
                <h3 class="text-lg font-bold text-surface-800 dark:text-surface-100 mb-2">Kumpulkan Ujian?</h3>
                <p class="text-sm text-surface-600 dark:text-surface-300 mb-1">
                    Terjawab: <strong class="text-primary-600 dark:text-primary-400" x-text="answeredCount"></strong> dari <strong x-text="totalQuestions"></strong> soal.
                </p>
                <p class="text-xs text-amber-600 dark:text-amber-400 mb-5 mt-1" x-show="answeredCount < totalQuestions">
                    ⚠ Masih ada soal yang belum dijawab.
                </p>
                <div class="flex gap-3 mt-4">
                    <button @click="showConfirmSubmit = false"
                            class="flex-1 py-2.5 border border-surface-300 dark:border-surface-700 rounded-xl text-surface-600 dark:text-surface-300 hover:bg-surface-50 dark:hover:bg-surface-800 transition-all duration-150">
                        Batal
                    </button>
                    <button @click="submitExam()"
                            class="flex-1 py-2.5 bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-400 hover:to-green-500 text-white font-semibold rounded-xl shadow-soft transition-all duration-150 hover:-translate-y-0.5">
                        Ya, Kumpulkan
                    </button>
                </div>
            </div>
        </div>
    </div>

<script>
/**
 * Alpine.js exam application.
 * Handles: timer, auto-save, anti-cheat, fullscreen management, submit.
 */
function examApp(attemptId, initialSeconds, totalQuestions) {
    return {
        // --- State ---
        phase: 'start',          // 'start' | 'exam' | 'done'
        currentIndex: 0,
        totalQuestions,
        sisaDetik: initialSeconds,
        answered: {},            // { questionId: true }
        doubts: [],              // [index, ...]
        violationCount: 0,
        maxViolations: {{ $attempt->exam->max_pelanggaran ?? 0 }},
        autoKeluar: {{ $attempt->exam->auto_keluar ? 'true' : 'false' }},
        tampilkanPeringatan: {{ $attempt->exam->tampilkan_peringatan ? 'true' : 'false' }},

        // UI state
        showWarning: false,
        warningText: '',
        showConfirmSubmit: false,
        doneIcon: '✅',
        doneTitle: 'Ujian Selesai',
        doneMessage: 'Jawaban Anda telah dikumpulkan.',

        // Internal
        _timerInterval: null,
        _heartbeatInterval: null,
        _savingQueue: {},       // questionId → in-flight flag
        _submitted: false,
        _fullscreenWarningActive: false,

        // --- Init ---
        init() {
            // Pre-populate answered from server-rendered data
            const answered = @json(
                collect($answers)
                    ->filter(fn($a) => $a !== null && ($a->jawaban_pg !== null || ($a->jawaban_esai !== null && $a->jawaban_esai !== '')))
                    ->keys()
                    ->values()
            );
            answered.forEach(id => this.answered[id] = true);

            this.$watch('phase', (val) => {
                if (val === 'exam') {
                    this._startTimer();
                    this._startHeartbeat();
                }
            });

            // Listen for fullscreen change
            document.addEventListener('fullscreenchange', () => this._onFullscreenChange());
            document.addEventListener('webkitfullscreenchange', () => this._onFullscreenChange());
        },

        // --- Computed ---
        get answeredCount() {
            return Object.keys(this.answered).length;
        },

        // --- Timer ---
        _startTimer() {
            this._timerInterval = setInterval(() => {
                if (this.sisaDetik <= 0) {
                    clearInterval(this._timerInterval);
                    this._autoSubmit('Waktu habis.');
                    return;
                }
                this.sisaDetik--;
            }, 1000);
        },

        formatTime(seconds) {
            seconds = Math.max(0, Math.floor(seconds));
            if (seconds <= 0) return '00:00';
            const h = Math.floor(seconds / 3600);
            const m = Math.floor((seconds % 3600) / 60);
            const s = seconds % 60;
            if (h > 0) return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
            return `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
        },

        timerClass() {
            if (this.sisaDetik <= 60)  return 'exam-timer-danger bg-gradient-to-r from-red-500 to-rose-600';
            if (this.sisaDetik <= 300) return 'bg-gradient-to-r from-amber-400 to-orange-500';
            return 'bg-gradient-to-r from-primary-500 to-indigo-600';
        },

        // --- Heartbeat (sync sisa waktu dari server) ---
        _startHeartbeat() {
            this._heartbeatInterval = setInterval(async () => {
                try {
                    const res = await fetch(`/exam/attempt/${attemptId}/heartbeat`, {
                        method: 'POST',
                        headers: this._headers(),
                    });
                    if (res.ok) {
                        const data = await res.json();
                        if (data.status === 'dikeluarkan') {
                            this._endExam('dikeluarkan', '🚫', 'Anda Dikeluarkan', data.reason ?? 'Terlalu banyak pelanggaran.');
                            return;
                        }
                        if (data.status === 'selesai') {
                            this._endExam('selesai', '✅', 'Ujian Telah Selesai', 'Jawaban Anda telah tersimpan.');
                            return;
                        }
                        // Correct drift: trust server time
                        if (Math.abs(this.sisaDetik - data.sisa_detik) > 5) {
                            this.sisaDetik = data.sisa_detik;
                        }
                        if (data.violation_count !== undefined) {
                            this.violationCount = data.violation_count;
                        }
                    }
                } catch (e) {
                    // Network hiccup — timer keeps going from last known value
                }
            }, 30_000);
        },

        // --- Fullscreen ---
        startExam() {
            const el = document.documentElement;
            const req = el.requestFullscreen?.() ?? el.webkitRequestFullscreen?.();
            Promise.resolve(req).finally(() => {
                this.phase = 'exam';
            });
        },

        _onFullscreenChange() {
            if (this.phase !== 'exam' || this._submitted) return;
            const isFs = !!(document.fullscreenElement || document.webkitFullscreenElement);
            if (!isFs) {
                this.logCheat('fullscreen_exit');
                // Mode senyap: jangan paksa kembali ke layar penuh agar siswa tak sadar dipantau.
                if (!this.tampilkanPeringatan) return;
                // Try to re-enter fullscreen
                if (!this._fullscreenWarningActive) {
                    this._fullscreenWarningActive = true;
                    this._showWarning('Anda keluar dari layar penuh! Kembali ke layar penuh segera.');
                    setTimeout(() => {
                        document.documentElement.requestFullscreen?.()
                            .catch(() => {})
                            .finally(() => { this._fullscreenWarningActive = false; });
                    }, 1000);
                }
            }
        },

        // --- Visibility / Blur ---
        handleVisibility() {
            if (this.phase !== 'exam' || this._submitted) return;
            if (document.hidden) this.logCheat('blur');
        },

        handleBlur() {
            if (this.phase !== 'exam' || this._submitted) return;
            // Small delay to avoid false positive from browser focus on fullscreen request
            setTimeout(() => {
                if (this.phase === 'exam' && !this._submitted) this.logCheat('blur');
            }, 300);
        },

        // --- Key intercept ---
        handleKey(event) {
            if (this.phase !== 'exam' || this._submitted) return;

            const blocked = [
                { key: 'F12' },
                { key: 'PrintScreen' },
                { key: 'c', ctrl: true },
                { key: 'v', ctrl: true },
                { key: 'p', ctrl: true },
                { key: 'u', ctrl: true },
                { key: 's', ctrl: true },
                { key: 'a', ctrl: true },
                { key: 'F5' },
                { key: 'r', ctrl: true },
            ];

            for (const rule of blocked) {
                const match = rule.key === event.key &&
                    (!rule.ctrl || event.ctrlKey || event.metaKey);
                if (match) {
                    event.preventDefault();
                    const jenis = rule.key === 'c' ? 'key_copy'
                               : rule.key === 'v' ? 'key_paste'
                               : rule.key === 'p' ? 'key_print'
                               : 'key_ctrl';
                    this.logCheat(jenis, { key: event.key });
                    return;
                }
            }
        },

        // --- Anti-cheat log ---
        async logCheat(jenis, detail = null) {
            this.violationCount++;
            this._showWarning(this._warningMessage(jenis));

            try {
                await fetch(`/exam/attempt/${attemptId}/cheat`, {
                    method: 'POST',
                    headers: this._headers(),
                    body: JSON.stringify({ jenis, detail, terjadi_at: new Date().toISOString() }),
                });
            } catch (e) {
                // Best-effort; server also records at heartbeat
            }

            // Check auto-keluar threshold
            if (this.autoKeluar && this.maxViolations > 0 && this.violationCount >= this.maxViolations) {
                this._endExam('dikeluarkan', '🚫', 'Anda Dikeluarkan', 'Batas pelanggaran tercapai.');
            }
        },

        _warningMessage(jenis) {
            const map = {
                fullscreen_exit: 'Anda keluar dari mode layar penuh!',
                blur:            'Anda berpindah tab atau jendela!',
                right_click:     'Klik kanan tidak diizinkan!',
                key_copy:        'Menyalin teks tidak diizinkan!',
                key_paste:       'Menempel teks tidak diizinkan!',
                key_print:       'Mencetak tidak diizinkan!',
                key_ctrl:        'Pintasan keyboard tidak diizinkan!',
            };
            return map[jenis] ?? 'Tindakan tidak diizinkan selama ujian!';
        },

        // --- Warning overlay ---
        _showWarning(text) {
            // Mode senyap: pelanggaran tetap direkam ke server (lihat logCheat),
            // tetapi siswa tidak diberi peringatan visual.
            if (!this.tampilkanPeringatan) return;
            this.warningText = text;
            this.showWarning = true;
        },

        dismissWarning() {
            this.showWarning = false;
            // Try to re-enter fullscreen if not already
            if (!(document.fullscreenElement || document.webkitFullscreenElement)) {
                document.documentElement.requestFullscreen?.().catch(() => {});
            }
        },

        // --- Navigation ---
        goTo(index)  { this.currentIndex = index; },
        next()       { if (this.currentIndex < this.totalQuestions - 1) this.currentIndex++; },
        prev()       { if (this.currentIndex > 0) this.currentIndex--; },

        toggleDoubt(index) {
            const i = this.doubts.indexOf(index);
            i === -1 ? this.doubts.push(index) : this.doubts.splice(i, 1);
        },

        navBtnClass(index, questionId) {
            const isAnswered = !!this.answered[questionId];
            const isDoubt    = this.doubts.includes(index);
            const isCurrent  = this.currentIndex === index;

            if (isCurrent)   return 'border-2 border-primary-500 bg-white dark:bg-surface-900 text-primary-600 dark:text-primary-400 ring-2 ring-primary-400/30';
            if (isDoubt)     return 'bg-gradient-to-br from-amber-300 to-orange-400 text-white';
            if (isAnswered)  return 'bg-gradient-to-br from-emerald-400 to-green-500 text-white';
            return 'bg-surface-200 dark:bg-surface-700 text-surface-600 dark:text-surface-300 hover:bg-surface-300 dark:hover:bg-surface-600';
        },

        // --- Save answer ---
        saveAnswer(questionId, tipe, nilai) {
            if (this._submitted) return;

            // Mark as answered immediately for UX
            if (nilai && nilai.trim() !== '') {
                this.answered[questionId] = true;
            } else {
                delete this.answered[questionId];
            }

            // Debounce concurrent saves for same question
            if (this._savingQueue[questionId]) return;
            this._savingQueue[questionId] = true;

            const body = tipe === 'pg'
                ? { jawaban_pg: nilai }
                : { jawaban_esai: nilai };

            fetch(`/exam/attempt/${attemptId}/answer/${questionId}`, {
                method: 'POST',
                headers: this._headers(),
                body: JSON.stringify(body),
            })
            .catch(() => { /* silent — will retry on heartbeat */ })
            .finally(() => {
                delete this._savingQueue[questionId];
            });
        },

        // --- Submit ---
        confirmSubmit() {
            this.showConfirmSubmit = true;
        },

        async submitExam() {
            this.showConfirmSubmit = false;
            await this._doSubmit('manual');
        },

        async _autoSubmit(reason) {
            if (this._submitted) return;
            await this._doSubmit('auto', reason);
        },

        async _doSubmit(mode, reason = null) {
            if (this._submitted) return;
            this._submitted = true;

            clearInterval(this._timerInterval);
            clearInterval(this._heartbeatInterval);

            try {
                const res = await fetch(`/exam/attempt/${attemptId}/submit`, {
                    method: 'POST',
                    headers: this._headers(),
                    body: JSON.stringify({ mode, reason }),
                });
                const data = await res.json();

                if (data.status === 'dikeluarkan') {
                    this._endExam('dikeluarkan', '🚫', 'Anda Dikeluarkan', data.reason ?? '');
                } else {
                    this._endExam('selesai', '✅', 'Ujian Selesai!', 'Jawaban Anda berhasil dikumpulkan.');
                }
            } catch (e) {
                this._endExam('selesai', '✅', 'Ujian Selesai', 'Jawaban dikumpulkan. Periksa koneksi Anda.');
            }
        },

        _endExam(status, icon, title, message) {
            clearInterval(this._timerInterval);
            clearInterval(this._heartbeatInterval);

            // Exit fullscreen
            (document.exitFullscreen?.() ?? document.webkitExitFullscreen?.())?.catch(() => {});

            this.doneIcon    = icon;
            this.doneTitle   = title;
            this.doneMessage = message;
            this.phase       = 'done';
        },

        // --- Helpers ---
        _headers() {
            return {
                'Content-Type':  'application/json',
                'Accept':        'application/json',
                'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content,
            };
        },
    };
}
</script>
</body>
</html>
