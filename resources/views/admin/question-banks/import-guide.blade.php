@extends('layouts.admin')
@section('title', 'Panduan Import Soal')
@section('page-title', 'Panduan Import Soal')

@section('content')
<div class="max-w-3xl space-y-5" x-data="{ tab: 'txt' }">

    {{-- ── Tab Switcher ── --}}
    <div class="card p-1.5 flex gap-1">
        <button @click="tab = 'txt'"
                :class="tab === 'txt'
                    ? 'bg-primary-600 text-white shadow-sm'
                    : 'text-surface-500 dark:text-surface-400 hover:bg-surface-100 dark:hover:bg-surface-800'"
                class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
            </svg>
            Import via File TXT
        </button>
        <button @click="tab = 'ai'"
                :class="tab === 'ai'
                    ? 'bg-primary-600 text-white shadow-sm'
                    : 'text-surface-500 dark:text-surface-400 hover:bg-surface-100 dark:hover:bg-surface-800'"
                class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
            Import via Prompt AI
        </button>
    </div>

    {{-- ═══════════════════════════════════════════
         TAB 1 — IMPORT VIA FILE TXT
    ════════════════════════════════════════════ --}}
    <div x-show="tab === 'txt'" x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
         class="space-y-5">

        {{-- Intro + Download --}}
        <div class="card p-5 flex items-start gap-4">
            <div class="flex-none w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-950/50
                        flex items-center justify-center text-primary-600 dark:text-primary-400">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm text-surface-600 dark:text-surface-300 leading-relaxed">
                    Buat soal sendiri menggunakan file
                    <code class="font-mono text-xs bg-surface-100 dark:bg-surface-800 px-1.5 py-0.5 rounded">.txt</code>
                    dengan format sederhana. Mendukung soal <strong>Pilihan Ganda</strong> dan <strong>Esai</strong>.
                </p>
                <a href="{{ route('admin.banks.questions.template') }}"
                   class="btn-primary inline-flex items-center gap-2 mt-3 text-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download Template
                </a>
            </div>
        </div>

        {{-- Cara Pakai --}}
        <div class="card p-5">
            <h3 class="font-semibold text-surface-800 dark:text-surface-100 mb-4 text-sm">Cara Pakai</h3>
            <div class="space-y-3">
                @php
                $steps = [
                    ['icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4',
                     'text' => 'Download template di atas, isi soal sesuai format, lalu simpan sebagai <code>.txt</code>.'],
                    ['icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z',
                     'text' => 'Ikuti format field di bawah. Pisahkan setiap soal dengan <strong>satu baris kosong</strong>.'],
                    ['icon' => 'M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12',
                     'text' => 'Buka <strong>Bank Soal → (pilih bank) → Import TXT</strong>, unggah file atau tempel teks, klik <strong>Import Soal</strong>.'],
                ];
                @endphp
                @foreach ($steps as $i => $s)
                <div class="flex items-start gap-3">
                    <div class="flex-none w-7 h-7 rounded-full bg-primary-50 dark:bg-primary-950/50
                                text-primary-700 dark:text-primary-400 text-xs font-bold
                                flex items-center justify-center">{{ $i + 1 }}</div>
                    <p class="text-sm text-surface-600 dark:text-surface-300 leading-relaxed pt-0.5">
                        {!! $s['text'] !!}
                    </p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Format Field --}}
        <div class="card overflow-hidden">
            <div class="card-header">
                <h3 class="font-semibold text-surface-800 dark:text-surface-100 text-sm">Format Field</h3>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-surface-400 dark:text-surface-500 border-b border-surface-100 dark:border-surface-800">
                        <th class="py-2.5 px-4 font-semibold w-28">Field</th>
                        <th class="py-2.5 px-2 font-semibold w-20">Wajib</th>
                        <th class="py-2.5 px-4 font-semibold">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="text-surface-600 dark:text-surface-300 divide-y divide-surface-50 dark:divide-surface-800/60">
                    <tr>
                        <td class="py-2.5 px-4"><code class="text-xs font-mono bg-surface-100 dark:bg-surface-800 px-1.5 py-0.5 rounded">TIPE:</code></td>
                        <td class="py-2.5 px-2 text-surface-400 text-xs">opsional</td>
                        <td class="py-2.5 px-4 text-xs"><code class="font-mono">pg</code> atau <code class="font-mono">esai</code>. Default: <code class="font-mono">pg</code></td>
                    </tr>
                    <tr>
                        <td class="py-2.5 px-4"><code class="text-xs font-mono bg-surface-100 dark:bg-surface-800 px-1.5 py-0.5 rounded">SOAL:</code></td>
                        <td class="py-2.5 px-2"><span class="text-xs font-semibold text-rose-500">wajib</span></td>
                        <td class="py-2.5 px-4 text-xs">Teks pertanyaan. Boleh lebih dari satu baris.</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 px-4"><code class="text-xs font-mono bg-surface-100 dark:bg-surface-800 px-1.5 py-0.5 rounded">A. … *</code></td>
                        <td class="py-2.5 px-2 text-surface-400 text-xs">khusus PG</td>
                        <td class="py-2.5 px-4 text-xs">Opsi A–E. Tandai kunci jawaban dengan <code class="font-mono">*</code> di akhir. Min. 2 opsi, tepat 1 kunci.</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 px-4"><code class="text-xs font-mono bg-surface-100 dark:bg-surface-800 px-1.5 py-0.5 rounded">RUBRIK:</code></td>
                        <td class="py-2.5 px-2 text-surface-400 text-xs">khusus esai</td>
                        <td class="py-2.5 px-4 text-xs">Kriteria/jawaban acuan untuk penilaian AI.</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 px-4"><code class="text-xs font-mono bg-surface-100 dark:bg-surface-800 px-1.5 py-0.5 rounded">BOBOT:</code></td>
                        <td class="py-2.5 px-2 text-surface-400 text-xs">opsional</td>
                        <td class="py-2.5 px-4 text-xs">Bobot soal. Default: <code class="font-mono">10</code>.</td>
                    </tr>
                </tbody>
            </table>
            <p class="text-xs text-surface-400 dark:text-surface-500 px-4 py-3 border-t border-surface-50 dark:border-surface-800">
                Baris yang diawali <code class="font-mono">#</code> diabaikan (komentar). Bisa juga pakai <code class="font-mono">---</code> sebagai pemisah soal.
            </p>
        </div>

        {{-- Contoh TXT --}}
        <div class="card overflow-hidden">
            <div class="card-header">
                <h3 class="font-semibold text-surface-800 dark:text-surface-100 text-sm">Contoh File TXT</h3>
            </div>
            <pre class="bg-surface-900 dark:bg-surface-950 text-surface-100 text-xs leading-relaxed p-4 overflow-x-auto"><code><span class="text-slate-500"># Soal pilihan ganda</span>
TIPE: pg
BOBOT: 10
SOAL: Apa ibu kota Indonesia?
A. Bandung
B. <span class="text-emerald-400">Jakarta*</span>
C. Surabaya
D. Medan

<span class="text-slate-500"># Soal esai</span>
TIPE: esai
BOBOT: 20
SOAL: Jelaskan proses fotosintesis secara singkat.
RUBRIK: Sebutkan reaktan (air, CO₂, cahaya) dan produk (glukosa, O₂).</code></pre>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════
         TAB 2 — IMPORT VIA PROMPT AI
    ════════════════════════════════════════════ --}}
    <div x-show="tab === 'ai'" x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
         x-data="{ example: 'pilgan' }"
         class="space-y-5">

        {{-- Intro --}}
        <div class="card p-5 flex items-start gap-4">
            <div class="flex-none w-10 h-10 rounded-xl bg-violet-50 dark:bg-violet-950/50
                        flex items-center justify-center text-violet-600 dark:text-violet-400">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm text-surface-600 dark:text-surface-300 leading-relaxed">
                    Gunakan AI seperti <strong>ChatGPT</strong>, <strong>Gemini</strong>, atau <strong>Claude</strong> untuk membuat soal otomatis.
                    Salin salah satu prompt contoh di bawah, tempel ke AI, lalu impor hasilnya langsung ke sistem.
                </p>
                <div class="flex flex-wrap gap-2 mt-3">
                    @foreach([
                        ['slug' => 'chatgpt', 'label' => 'ChatGPT', 'url' => 'https://chat.openai.com'],
                        ['slug' => 'gemini',  'label' => 'Gemini',  'url' => 'https://gemini.google.com'],
                        ['slug' => 'claude',  'label' => 'Claude',  'url' => 'https://claude.ai'],
                    ] as $ai)
                    <a href="{{ $ai['url'] }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-lg
                              bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-300
                              hover:bg-surface-200 dark:hover:bg-surface-700 transition-colors">
                        {{ $ai['label'] }}
                        <svg class="w-3 h-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Cara Pakai AI --}}
        <div class="card p-5">
            <h3 class="font-semibold text-surface-800 dark:text-surface-100 mb-4 text-sm">Cara Pakai</h3>
            <div class="space-y-3">
                @php
                $aiSteps = [
                    ['text' => 'Pilih salah satu <strong>contoh prompt</strong> di bawah sesuai kebutuhan (semua pilgan, semua esai, atau kombinasi).'],
                    ['text' => 'Klik <strong>Salin Prompt</strong>, lalu tempel ke ChatGPT / Gemini / Claude. Sesuaikan topik, jumlah soal, dan tingkat kesulitan.'],
                    ['text' => 'Salin seluruh teks hasil dari AI, lalu buka <strong>Bank Soal → (pilih bank) → Import TXT</strong> dan tempel di kolom teks.'],
                    ['text' => 'Klik <strong>Import Soal</strong>. Sistem akan langsung memproses dan menyimpan soal.'],
                ];
                @endphp
                @foreach ($aiSteps as $i => $s)
                <div class="flex items-start gap-3">
                    <div class="flex-none w-7 h-7 rounded-full bg-violet-50 dark:bg-violet-950/50
                                text-violet-700 dark:text-violet-400 text-xs font-bold
                                flex items-center justify-center">{{ $i + 1 }}</div>
                    <p class="text-sm text-surface-600 dark:text-surface-300 leading-relaxed pt-0.5">
                        {!! $s['text'] !!}
                    </p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Pilihan Contoh Prompt --}}
        <div class="card overflow-hidden">
            <div class="card-header flex-wrap gap-3">
                <h3 class="font-semibold text-surface-800 dark:text-surface-100 text-sm">Contoh Prompt untuk AI</h3>
                <div class="flex gap-1 flex-wrap">
                    <button @click="example = 'pilgan'"
                            :class="example === 'pilgan'
                                ? 'bg-primary-600 text-white'
                                : 'bg-surface-100 dark:bg-surface-800 text-surface-500 dark:text-surface-400 hover:bg-surface-200 dark:hover:bg-surface-700'"
                            class="px-3 py-1 rounded-md text-xs font-medium transition-colors">
                        Semua Pilgan
                    </button>
                    <button @click="example = 'esai'"
                            :class="example === 'esai'
                                ? 'bg-primary-600 text-white'
                                : 'bg-surface-100 dark:bg-surface-800 text-surface-500 dark:text-surface-400 hover:bg-surface-200 dark:hover:bg-surface-700'"
                            class="px-3 py-1 rounded-md text-xs font-medium transition-colors">
                        Semua Esai
                    </button>
                    <button @click="example = 'kombinasi'"
                            :class="example === 'kombinasi'
                                ? 'bg-primary-600 text-white'
                                : 'bg-surface-100 dark:bg-surface-800 text-surface-500 dark:text-surface-400 hover:bg-surface-200 dark:hover:bg-surface-700'"
                            class="px-3 py-1 rounded-md text-xs font-medium transition-colors">
                        Kombinasi
                    </button>
                </div>
            </div>

            {{-- PROMPT: Semua Pilgan --}}
            <div x-show="example === 'pilgan'" class="relative">
                <div class="px-4 py-2 bg-surface-800/60 dark:bg-surface-800 border-b border-surface-700/50 text-xs text-surface-400 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span>
                    Contoh: 10 soal Matematika (Operasi Aljabar) — Semua Pilihan Ganda
                </div>
                <div class="relative">
                    <pre id="prompt-pilgan" class="bg-surface-900 dark:bg-surface-950 text-surface-100 text-xs leading-relaxed p-4 overflow-x-auto whitespace-pre-wrap">Buatkan 10 soal pilihan ganda tentang Operasi Aljabar untuk siswa SMK kelas X.

Format setiap soal HARUS persis seperti ini:

TIPE: pg
BOBOT: 10
SOAL: [teks pertanyaan]
A. [opsi A]
B. [opsi B]
C. [opsi C]
D. [opsi D]
E. [opsi E]

Aturan wajib:
- Pisahkan setiap soal dengan SATU baris kosong
- Tandai kunci jawaban dengan tanda * di akhir opsi (contoh: C. 12*)
- Hanya SATU opsi yang benar per soal
- Jangan tambahkan nomor soal, penjelasan, atau teks apapun di luar format
- Semua soal harus dalam format TIPE: pg</pre>
                    <button onclick="copyPrompt('prompt-pilgan', this)"
                            class="absolute top-2 right-2 text-xs px-3 py-1.5 rounded-lg
                                   bg-surface-700 hover:bg-surface-600 text-surface-200 transition-colors">
                        Salin Prompt
                    </button>
                </div>
                <div class="px-4 py-2.5 bg-surface-800/40 dark:bg-surface-900/60 border-t border-surface-700/50">
                    <p class="text-xs text-surface-400 dark:text-surface-500">
                        <span class="text-amber-400 font-medium">Tips:</span>
                        Ganti <code class="font-mono bg-surface-700/50 px-1 rounded">Operasi Aljabar</code> dan
                        <code class="font-mono bg-surface-700/50 px-1 rounded">SMK kelas X</code> sesuai kebutuhan.
                        Bisa juga minta AI untuk menambahkan tingkat kesulitan (mudah / sedang / sulit).
                    </p>
                </div>
            </div>

            {{-- PROMPT: Semua Esai --}}
            <div x-show="example === 'esai'" class="relative">
                <div class="px-4 py-2 bg-surface-800/60 dark:bg-surface-800 border-b border-surface-700/50 text-xs text-surface-400 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-violet-400 inline-block"></span>
                    Contoh: 5 soal Matematika (Fungsi Kuadrat) — Semua Esai
                </div>
                <div class="relative">
                    <pre id="prompt-esai" class="bg-surface-900 dark:bg-surface-950 text-surface-100 text-xs leading-relaxed p-4 overflow-x-auto whitespace-pre-wrap">Buatkan 5 soal esai tentang Fungsi Kuadrat untuk siswa SMK kelas X.

Format setiap soal HARUS persis seperti ini:

TIPE: esai
BOBOT: 20
SOAL: [teks pertanyaan]
RUBRIK: [kriteria penilaian / jawaban acuan yang lengkap]

Aturan wajib:
- Pisahkan setiap soal dengan SATU baris kosong
- RUBRIK harus berisi poin-poin penilaian yang jelas (langkah penyelesaian, rumus, hasil akhir)
- Jangan tambahkan nomor soal, penjelasan, atau teks apapun di luar format
- Semua soal harus dalam format TIPE: esai</pre>
                    <button onclick="copyPrompt('prompt-esai', this)"
                            class="absolute top-2 right-2 text-xs px-3 py-1.5 rounded-lg
                                   bg-surface-700 hover:bg-surface-600 text-surface-200 transition-colors">
                        Salin Prompt
                    </button>
                </div>
                <div class="px-4 py-2.5 bg-surface-800/40 dark:bg-surface-900/60 border-t border-surface-700/50">
                    <p class="text-xs text-surface-400 dark:text-surface-500">
                        <span class="text-amber-400 font-medium">Tips:</span>
                        RUBRIK yang baik membantu AI menilai jawaban siswa lebih akurat.
                        Minta AI menuliskan langkah-langkah penyelesaian sebagai rubrik, bukan hanya jawaban akhir.
                    </p>
                </div>
            </div>

            {{-- PROMPT: Kombinasi --}}
            <div x-show="example === 'kombinasi'" class="relative">
                <div class="px-4 py-2 bg-surface-800/60 dark:bg-surface-800 border-b border-surface-700/50 text-xs text-surface-400 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-orange-400 inline-block"></span>
                    Contoh: 7 PG + 3 Esai Matematika (Statistika) — Kombinasi
                </div>
                <div class="relative">
                    <pre id="prompt-kombinasi" class="bg-surface-900 dark:bg-surface-950 text-surface-100 text-xs leading-relaxed p-4 overflow-x-auto whitespace-pre-wrap">Buatkan soal tentang Statistika Dasar untuk siswa SMK kelas XI.
Terdiri dari: 7 soal pilihan ganda dan 3 soal esai.

Format soal PILIHAN GANDA:

TIPE: pg
BOBOT: 10
SOAL: [teks pertanyaan]
A. [opsi A]
B. [opsi B]
C. [opsi C]
D. [opsi D]
E. [opsi E]

Format soal ESAI:

TIPE: esai
BOBOT: 20
SOAL: [teks pertanyaan]
RUBRIK: [kriteria penilaian / langkah-langkah penyelesaian]

Aturan wajib:
- Pisahkan setiap soal dengan SATU baris kosong
- Kunci jawaban PG ditandai dengan * di akhir opsi (contoh: B. Mean*)
- Hanya SATU opsi yang benar per soal PG
- RUBRIK esai harus berisi poin-poin penilaian yang jelas
- Jangan tambahkan nomor soal, judul, penjelasan, atau teks apapun di luar format di atas
- Urutan bebas (boleh campur PG dan esai)</pre>
                    <button onclick="copyPrompt('prompt-kombinasi', this)"
                            class="absolute top-2 right-2 text-xs px-3 py-1.5 rounded-lg
                                   bg-surface-700 hover:bg-surface-600 text-surface-200 transition-colors">
                        Salin Prompt
                    </button>
                </div>
                <div class="px-4 py-2.5 bg-surface-800/40 dark:bg-surface-900/60 border-t border-surface-700/50">
                    <p class="text-xs text-surface-400 dark:text-surface-500">
                        <span class="text-amber-400 font-medium">Tips:</span>
                        Sesuaikan rasio PG dan esai sesuai format ujian. Untuk ujian 90 menit,
                        kombinasi 20 PG + 5 esai adalah proporsi yang umum digunakan.
                    </p>
                </div>
            </div>
        </div>

        {{-- Contoh Output AI --}}
        <div class="card overflow-hidden">
            <div class="card-header">
                <div>
                    <h3 class="font-semibold text-surface-800 dark:text-surface-100 text-sm">Contoh Hasil Output AI</h3>
                    <p class="text-xs text-surface-400 dark:text-surface-500 mt-0.5">Ini adalah contoh teks yang dihasilkan AI — siap untuk langsung diimpor.</p>
                </div>
            </div>
            <pre class="bg-surface-900 dark:bg-surface-950 text-surface-100 text-xs leading-relaxed p-4 overflow-x-auto"><code><span class="text-slate-500"># === Pilihan Ganda ===</span>
TIPE: pg
BOBOT: 10
SOAL: Diketahui data: 4, 6, 8, 8, 10. Berapakah nilai modus dari data tersebut?
A. 4
B. 6
C. <span class="text-emerald-400">8*</span>
D. 10
E. 36

TIPE: pg
BOBOT: 10
SOAL: Nilai mean dari data 5, 7, 9, 11, 13 adalah ...
A. 7
B. 8
C. <span class="text-emerald-400">9*</span>
D. 10
E. 11

<span class="text-slate-500"># === Esai ===</span>
TIPE: esai
BOBOT: 20
SOAL: Sebuah kelas memiliki data nilai ulangan: 70, 75, 80, 85, 90, 90, 95.
Tentukan mean, median, dan modus dari data tersebut!
RUBRIK: Mean = (70+75+80+85+90+90+95)/7 = 83,57 (skor 4).
Median = nilai tengah data terurut = 85 (skor 3).
Modus = 90 karena muncul 2 kali (skor 3).</code></pre>
        </div>

        {{-- Catatan Penting --}}
        <div class="card p-4 border border-amber-200 dark:border-amber-800/50 bg-amber-50 dark:bg-amber-950/30">
            <div class="flex items-start gap-3">
                <svg class="flex-none w-5 h-5 text-amber-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="text-sm text-amber-800 dark:text-amber-300 space-y-1">
                    <p class="font-semibold">Catatan Penting</p>
                    <ul class="text-xs space-y-1 text-amber-700 dark:text-amber-400 list-disc list-inside">
                        <li>Periksa kembali soal hasil AI sebelum diimpor — AI bisa membuat soal yang salah secara faktual.</li>
                        <li>Pastikan setiap soal PG hanya memiliki <strong>satu</strong> tanda <code class="font-mono bg-amber-100 dark:bg-amber-900 px-1 rounded">*</code>.</li>
                        <li>Jika AI menambahkan teks di luar format (misal: "Berikut adalah soal..."), hapus teks tersebut sebelum mengimpor.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function copyPrompt(id, btn) {
    const text = document.getElementById(id).innerText;
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.textContent;
        btn.textContent = '✓ Tersalin!';
        setTimeout(() => btn.textContent = orig, 2000);
    });
}
</script>
@endpush
@endsection
