{{--
    Halaman review jawaban siswa setelah ujian selesai.
    Data: $attempt, $questions (ExamQuestion collection), $answers (keyed by question_id)
--}}
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review — {{ $attempt->exam->judul }} — PiCBT</title>
    <link rel="shortcut icon" href="/logo.webp" type="image/webp">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full">

{{-- Header --}}
<header class="bg-white border-b border-slate-200 sticky top-0 z-10">
    <div class="max-w-3xl mx-auto px-4 py-4 flex items-center gap-3">
        <a href="{{ route('student.dashboard') }}"
           class="flex items-center gap-1.5 text-sm text-slate-400 hover:text-slate-700 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Dashboard
        </a>
        <span class="text-slate-200">/</span>
        <span class="text-sm font-semibold text-slate-700 truncate">Review: {{ $attempt->exam->judul }}</span>
    </div>
</header>

<main class="max-w-3xl mx-auto px-4 py-6 space-y-4">

    {{-- Ringkasan --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="font-bold text-slate-800 text-lg">{{ $attempt->exam->judul }}</h1>
                <p class="text-sm text-slate-400 mt-0.5">
                    {{ $attempt->student->nama }} &bull;
                    Selesai: {{ $attempt->selesai_at?->isoFormat('D MMM Y, HH:mm') ?? '—' }}
                </p>
            </div>
            <div class="text-right">
                <p class="text-3xl font-bold text-primary-600 dark:text-primary-400">
                    {{ $attempt->total_skor !== null ? number_format($attempt->total_skor, 1) : '—' }}
                </p>
                <p class="text-xs text-slate-400">Total Skor</p>
            </div>
        </div>

        {{-- Stats bar --}}
        @php
            $totalSoal  = $questions->count();
            $totalJawab = $answers->count();
            $benar      = $answers->filter(fn($a) => $a->skor > 0)->count();
        @endphp
        <div class="grid grid-cols-3 gap-3 mt-4 pt-4 border-t border-slate-100">
            <div class="text-center">
                <p class="text-xl font-bold text-slate-800">{{ $totalSoal }}</p>
                <p class="text-xs text-slate-400">Total Soal</p>
            </div>
            <div class="text-center">
                <p class="text-xl font-bold text-emerald-600">{{ $benar }}</p>
                <p class="text-xs text-slate-400">Benar / Mendapat Skor</p>
            </div>
            <div class="text-center">
                <p class="text-xl font-bold text-slate-400">{{ $totalSoal - $totalJawab }}</p>
                <p class="text-xs text-slate-400">Tidak Dijawab</p>
            </div>
        </div>
    </div>

    {{-- Daftar soal --}}
    @foreach ($questions as $index => $examQuestion)
        @php
            $q   = $examQuestion->question;
            $ans = $answers[$q->id] ?? null;
            $num = $index + 1;

            $isPg      = $q->tipe === 'pilihan_ganda';
            $answered  = $ans !== null && ($ans->jawaban_pg !== null || ($ans->jawaban_esai !== null && $ans->jawaban_esai !== ''));
            $correct   = $isPg ? $q->options->firstWhere('is_correct', true)?->label : null;
            $isCorrect = $isPg && $answered && ($ans->jawaban_pg === $correct);
            $isWrong   = $isPg && $answered && !$isCorrect;
        @endphp

        <div class="bg-white rounded-2xl border overflow-hidden
            {{ $isCorrect ? 'border-emerald-200' : ($isWrong ? 'border-red-200' : 'border-slate-200') }}">

            {{-- Nomor & tipe --}}
            <div class="flex items-center gap-3 px-5 py-3 border-b
                {{ $isCorrect ? 'bg-emerald-50 border-emerald-100' : ($isWrong ? 'bg-red-50 border-red-100' : 'bg-slate-50 border-slate-100') }}">
                <span class="flex-none w-8 h-8 rounded-xl flex items-center justify-center text-sm font-bold text-white
                    {{ $isCorrect ? 'bg-emerald-500' : ($isWrong ? 'bg-red-500' : 'bg-slate-400') }}">
                    {{ $num }}
                </span>
                <span class="text-xs font-semibold
                    {{ $isCorrect ? 'text-emerald-700' : ($isWrong ? 'text-red-700' : 'text-slate-500') }}">
                    @if ($isPg)
                        @if ($isCorrect) ✓ Benar
                        @elseif ($isWrong) ✗ Salah
                        @else Tidak Dijawab
                        @endif
                    @else
                        Esai &bull;
                        @if ($ans?->dinilai_oleh)
                            Skor: <strong>{{ $ans->skor ?? 0 }}</strong> / {{ $q->bobot }}
                            <span class="ml-1 opacity-70">({{ $ans->dinilai_oleh === 'ai' ? 'AI' : 'Guru' }})</span>
                        @else
                            Belum Dinilai
                        @endif
                    @endif
                </span>
                @if ($isPg)
                    <span class="ml-auto text-xs font-semibold
                        {{ $isCorrect ? 'text-emerald-600' : 'text-slate-400' }}">
                        +{{ $isCorrect ? $ans->skor : 0 }} / {{ $q->bobot }}
                    </span>
                @endif
            </div>

            <div class="px-5 py-4 space-y-3">
                {{-- Teks soal --}}
                <div class="prose prose-sm max-w-none text-slate-800">
                    {!! $q->pertanyaan !!}
                </div>
                @if ($q->gambar)
                    <img src="{{ Storage::url($q->gambar) }}"
                         alt="Gambar soal" class="max-h-52 rounded-xl border border-slate-200">
                @endif

                {{-- PG: opsi --}}
                @if ($isPg)
                    <div class="space-y-1.5 mt-2">
                        @foreach ($q->options as $opt)
                            @php
                                $isKunci    = $opt->label === $correct;
                                $isPilihan  = $ans?->jawaban_pg === $opt->label;
                            @endphp
                            <div class="flex items-start gap-2.5 px-3 py-2 rounded-xl text-sm
                                {{ $isKunci   ? 'bg-emerald-50 border border-emerald-200' : '' }}
                                {{ $isPilihan && !$isKunci ? 'bg-red-50 border border-red-200' : '' }}
                                {{ !$isKunci && !$isPilihan ? 'border border-slate-100' : '' }}">
                                <span class="flex-none w-6 h-6 rounded-lg flex items-center justify-center font-bold text-xs
                                    {{ $isKunci ? 'bg-emerald-500 text-white' : ($isPilihan ? 'bg-red-400 text-white' : 'bg-slate-100 text-slate-500') }}">
                                    {{ $opt->label }}
                                </span>
                                <span class="flex-1 text-slate-700 prose prose-sm max-w-none">{!! $opt->teks_opsi !!}</span>
                                @if ($isKunci)
                                    <span class="text-emerald-600 text-xs font-semibold flex-none">✓ Kunci</span>
                                @elseif ($isPilihan)
                                    <span class="text-red-500 text-xs font-semibold flex-none">✗ Pilihan Anda</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Esai: jawaban siswa + feedback AI --}}
                @if (!$isPg)
                    <div class="mt-2 space-y-3">
                        <div>
                            <p class="text-xs font-semibold text-slate-500 mb-1">Jawaban Anda:</p>
                            @if ($ans?->jawaban_esai)
                                <div class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-700 whitespace-pre-wrap">{{ $ans->jawaban_esai }}</div>
                            @else
                                <p class="text-sm text-slate-400 italic">Tidak dijawab.</p>
                            @endif
                        </div>
                        @if ($ans?->ai_feedback)
                            <div class="bg-violet-50 border border-violet-200 rounded-xl px-4 py-3">
                                <p class="text-xs font-semibold text-violet-600 mb-1">Catatan Penilaian:</p>
                                <p class="text-sm text-violet-800">{{ $ans->ai_feedback }}</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endforeach

    <div class="pb-8 text-center">
        <a href="{{ route('student.dashboard') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold rounded-xl transition">
            ← Kembali ke Dashboard
        </a>
    </div>
</main>

</body>
</html>
