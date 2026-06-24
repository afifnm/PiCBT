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
    <title>{{ $attempt->exam->judul }} — CBT</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Disable user selection globally during exam */
        body { -webkit-user-select: none; user-select: none; }
        /* Allow text selection inside answer textarea */
        textarea { -webkit-user-select: text; user-select: text; }
        /* Fullscreen background */
        :-webkit-full-screen body, :fullscreen body { background: #f8fafc; }
    </style>
</head>
<body class="h-full bg-slate-100 font-sans"
      x-data="examApp({{ $attempt->id }}, {{ $sisaDetik }}, {{ $questions->count() }})"
      x-init="init()"
      @keydown.window="handleKey($event)"
      @visibilitychange.document="handleVisibility()"
      @blur.window="handleBlur()"
      @contextmenu.window.prevent="logCheat('right_click')"
      @copy.window.prevent="logCheat('key_copy')"
      @paste.window.prevent="logCheat('key_paste')"
      @cut.window.prevent="logCheat('key_copy')">

    {{-- ================================================================ --}}
    {{-- OVERLAY: Belum mulai / sudah selesai --}}
    {{-- ================================================================ --}}
    <div x-show="phase === 'start'" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-800/90">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 text-center">
            <div class="text-4xl mb-4">📋</div>
            <h1 class="text-2xl font-bold text-slate-800 mb-2">{{ $attempt->exam->judul }}</h1>
            <p class="text-slate-500 mb-1">Siswa: <strong>{{ $attempt->student->nama }}</strong></p>
            <p class="text-slate-500 mb-6">
                {{ $questions->count() }} soal &bull;
                {{ $attempt->exam->durasi_menit }} menit
            </p>
            <ul class="text-left text-sm text-slate-600 bg-amber-50 rounded-lg p-4 mb-6 space-y-1">
                <li>⚠ Ujian akan berjalan dalam mode <strong>layar penuh</strong>.</li>
                <li>⚠ Dilarang berpindah tab, menyalin, atau mencetak.</li>
                <li>⚠ Pelanggaran akan direkam dan dilaporkan ke pengawas.</li>
                <li>⚠ Waktu berjalan dari server — pastikan koneksi stabil.</li>
            </ul>
            <button @click="startExam()"
                    class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition">
                Mulai Ujian — Aktifkan Layar Penuh
            </button>
        </div>
    </div>

    {{-- OVERLAY: Dikeluarkan / Waktu habis --}}
    <div x-show="phase === 'done'" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-800/90">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-sm w-full mx-4 text-center">
            <div class="text-5xl mb-4" x-text="doneIcon"></div>
            <h2 class="text-xl font-bold text-slate-800 mb-2" x-text="doneTitle"></h2>
            <p class="text-slate-500 text-sm mb-6" x-text="doneMessage"></p>
            <a href="{{ route('student.dashboard') }}"
               class="inline-block py-3 px-6 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition">
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    {{-- OVERLAY: Warning pelanggaran --}}
    <div x-show="showWarning" x-cloak
         class="fixed inset-0 z-40 flex items-center justify-center bg-black/60">
        <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4 text-center">
            <div class="text-4xl mb-3">🚨</div>
            <h3 class="text-lg font-bold text-red-600 mb-2">Pelanggaran Terdeteksi!</h3>
            <p class="text-sm text-slate-600 mb-4" x-text="warningText"></p>
            <p class="text-xs text-slate-400 mb-4">
                Pelanggaran: <span class="font-bold text-red-500" x-text="violationCount"></span>
                <span x-show="maxViolations > 0">/ <span x-text="maxViolations"></span></span>
            </p>
            <button @click="dismissWarning()"
                    class="py-2 px-6 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition">
                Saya Mengerti — Kembali ke Ujian
            </button>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- MAIN EXAM LAYOUT --}}
    {{-- ================================================================ --}}
    <div x-show="phase === 'exam'" x-cloak class="flex flex-col h-screen overflow-hidden">

        {{-- ---- HEADER ---- --}}
        <header class="flex-none bg-white border-b border-slate-200 shadow-sm z-10">
            <div class="max-w-7xl mx-auto px-4 py-3 flex items-center gap-4">

                {{-- Nama ujian --}}
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-slate-400 truncate">{{ $attempt->student->nama }} &bull; {{ $attempt->student->nis }}</p>
                    <h1 class="font-bold text-slate-800 truncate">{{ $attempt->exam->judul }}</h1>
                </div>

                {{-- Timer --}}
                <div class="flex-none">
                    <div :class="timerClass()"
                         class="flex items-center gap-2 px-4 py-2 rounded-xl font-mono font-bold text-lg transition-colors">
                        <span>⏱</span>
                        <span x-text="formatTime(sisaDetik)"></span>
                    </div>
                </div>

                {{-- Progress --}}
                <div class="flex-none text-center hidden sm:block">
                    <span class="text-xs text-slate-400">Terjawab</span>
                    <div class="font-bold text-slate-700">
                        <span x-text="answeredCount"></span>/<span x-text="totalQuestions"></span>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="flex-none">
                    <button @click="confirmSubmit()"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition">
                        Selesai & Kumpul
                    </button>
                </div>
            </div>

            {{-- Progress bar --}}
            <div class="h-1 bg-slate-100">
                <div class="h-1 bg-blue-500 transition-all duration-300"
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
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-x-4"
                         x-transition:enter-end="opacity-100 translate-x-0"
                         class="max-w-3xl mx-auto">

                        {{-- Soal header --}}
                        <div class="flex items-start gap-3 mb-4">
                            <span class="flex-none w-8 h-8 rounded-full bg-blue-600 text-white text-sm font-bold flex items-center justify-center">
                                {{ $num }}
                            </span>
                            <div class="flex-1">
                                <span class="inline-block px-2 py-0.5 text-xs rounded-full mb-2
                                    {{ $q->tipe === 'pilihan_ganda' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                    {{ $q->tipe === 'pilihan_ganda' ? 'Pilihan Ganda' : 'Esai' }}
                                    &bull; Bobot {{ $q->bobot }}
                                </span>
                                <div class="prose prose-sm max-w-none text-slate-800">
                                    {!! $q->pertanyaan !!}
                                </div>
                                @if ($q->gambar)
                                    <img src="{{ Storage::url($q->gambar) }}"
                                         alt="Gambar soal"
                                         class="mt-3 max-h-64 rounded-lg border">
                                @endif
                            </div>
                        </div>

                        {{-- PG Options --}}
                        @if ($q->tipe === 'pilihan_ganda')
                            <div class="space-y-2 ml-11">
                                @foreach ($q->options as $opt)
                                    <label class="flex items-start gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all
                                               hover:border-blue-300 hover:bg-blue-50
                                               {{ ($ans?->jawaban_pg === $opt->label) ? 'border-blue-500 bg-blue-50' : 'border-slate-200 bg-white' }}">
                                        <input type="radio"
                                               name="q_{{ $q->id }}"
                                               value="{{ $opt->label }}"
                                               {{ ($ans?->jawaban_pg === $opt->label) ? 'checked' : '' }}
                                               @change="saveAnswer({{ $q->id }}, 'pg', $event.target.value)"
                                               class="mt-0.5 accent-blue-600">
                                        <span class="font-bold text-blue-700 flex-none">{{ $opt->label }}.</span>
                                        <span class="text-slate-700">{{ $opt->teks_opsi }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif

                        {{-- Esai --}}
                        @if ($q->tipe === 'esai')
                            <div class="ml-11">
                                <textarea
                                    name="q_{{ $q->id }}"
                                    rows="8"
                                    placeholder="Tulis jawaban Anda di sini..."
                                    class="w-full p-3 rounded-xl border-2 border-slate-200 focus:border-blue-400 focus:outline-none resize-none text-slate-800 text-sm transition"
                                    @input.debounce.800ms="saveAnswer({{ $q->id }}, 'esai', $event.target.value)"
                                    >{{ $ans?->jawaban_esai }}</textarea>
                                <p class="text-xs text-slate-400 mt-1">Jawaban disimpan otomatis.</p>
                            </div>
                        @endif

                        {{-- Ragu-ragu --}}
                        <div class="ml-11 mt-4 flex items-center gap-3">
                            <label class="flex items-center gap-2 text-sm text-slate-500 cursor-pointer select-none">
                                <input type="checkbox"
                                       :checked="doubts.includes({{ $index }})"
                                       @change="toggleDoubt({{ $index }})"
                                       class="accent-amber-500">
                                Tandai ragu-ragu
                            </label>
                        </div>

                        {{-- Prev / Next --}}
                        <div class="ml-11 mt-6 flex gap-3">
                            <button @click="prev()"
                                    x-show="{{ $index }} > 0"
                                    class="px-4 py-2 text-sm border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                                ← Sebelumnya
                            </button>
                            <button @click="next()"
                                    x-show="{{ $index }} < {{ $questions->count() - 1 }}"
                                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                Selanjutnya →
                            </button>
                        </div>
                    </div>
                @endforeach
            </main>

            {{-- ---- SIDEBAR navigasi nomor soal ---- --}}
            <aside class="flex-none w-48 hidden lg:flex flex-col border-l border-slate-200 bg-white p-4 overflow-y-auto">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Nomor Soal</p>
                <div class="grid grid-cols-4 gap-1.5">
                    @foreach ($questions as $index => $examQuestion)
                        @php $qId = $examQuestion->question_id; @endphp
                        <button
                            @click="goTo({{ $index }})"
                            :class="navBtnClass({{ $index }}, {{ $qId }})"
                            class="h-9 w-full rounded-lg text-sm font-semibold transition">
                            {{ $index + 1 }}
                        </button>
                    @endforeach
                </div>

                <div class="mt-4 space-y-1.5 text-xs text-slate-500">
                    <div class="flex items-center gap-2">
                        <span class="w-4 h-4 rounded bg-green-500 inline-block"></span> Terjawab
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-4 h-4 rounded bg-amber-400 inline-block"></span> Ragu-ragu
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-4 h-4 rounded bg-slate-200 inline-block"></span> Belum
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-4 h-4 rounded border-2 border-blue-500 inline-block"></span> Aktif
                    </div>
                </div>
            </aside>
        </div>
    </div>

    {{-- Confirm submit modal --}}
    <div x-show="showConfirmSubmit" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60">
        <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4 text-center">
            <div class="text-4xl mb-3">📤</div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Kumpulkan Ujian?</h3>
            <p class="text-sm text-slate-600 mb-1">
                Terjawab: <strong x-text="answeredCount"></strong> dari <strong x-text="totalQuestions"></strong> soal.
            </p>
            <p class="text-xs text-amber-600 mb-5" x-show="answeredCount < totalQuestions">
                Masih ada soal yang belum dijawab.
            </p>
            <div class="flex gap-3">
                <button @click="showConfirmSubmit = false"
                        class="flex-1 py-2 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </button>
                <button @click="submitExam()"
                        class="flex-1 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition">
                    Ya, Kumpulkan
                </button>
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
            if (seconds <= 0) return '00:00';
            const h = Math.floor(seconds / 3600);
            const m = Math.floor((seconds % 3600) / 60);
            const s = seconds % 60;
            if (h > 0) return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
            return `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
        },

        timerClass() {
            if (this.sisaDetik <= 60)  return 'bg-red-100 text-red-700 animate-pulse';
            if (this.sisaDetik <= 300) return 'bg-amber-100 text-amber-700';
            return 'bg-slate-100 text-slate-700';
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

            if (isCurrent)   return 'border-2 border-blue-500 bg-white text-blue-600';
            if (isDoubt)     return 'bg-amber-400 text-white';
            if (isAnswered)  return 'bg-green-500 text-white';
            return 'bg-slate-200 text-slate-600 hover:bg-slate-300';
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
