<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa — PiCBT</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full">

{{-- Header --}}
<header class="bg-white border-b border-slate-200 sticky top-0 z-10">
    <div class="max-w-3xl mx-auto px-4 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center font-bold text-white text-sm">CBT</div>
            <span class="font-bold text-slate-800">PiCBT</span>
        </div>
        <div class="flex items-center gap-4">
            <div class="text-right hidden sm:block">
                <p class="text-sm font-semibold text-slate-800">{{ $student->nama }}</p>
                <p class="text-xs text-slate-400">{{ $student->nis }} &bull; Kelas {{ $kelas }}</p>
            </div>
            <form method="POST" action="{{ route('student.logout') }}">
                @csrf
                <button class="text-sm text-slate-400 hover:text-red-500 transition">Keluar</button>
            </form>
        </div>
    </div>
</header>

<main class="max-w-3xl mx-auto px-4 py-6 space-y-6">

    {{-- Flash --}}
    @if (session('info'))
        <div class="bg-blue-50 border border-blue-200 text-blue-700 rounded-xl px-4 py-3 text-sm">
            {{ session('info') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Ujian Tersedia --}}
    <section>
        <h2 class="font-semibold text-slate-700 mb-3">Ujian Tersedia untuk Kelas {{ $kelas }}</h2>

        @if ($availableExams->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 px-5 py-10 text-center text-slate-400 text-sm">
                Tidak ada ujian aktif saat ini.
            </div>
        @else
            <div class="space-y-3">
                @foreach ($availableExams as $item)
                    @php $exam = $item['exam']; $attempt = $item['attempt']; @endphp
                    <div class="bg-white rounded-2xl border border-slate-200 p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-slate-800">{{ $exam->judul }}</p>
                                <p class="text-sm text-slate-400 mt-0.5">
                                    {{ $exam->questionBank->subject->nama }} &bull;
                                    {{ $exam->jumlah_soal }} soal &bull;
                                    {{ $exam->durasi_menit }} menit
                                </p>
                                <p class="text-xs text-slate-400 mt-1">
                                    Berakhir: {{ $exam->selesai_pada?->isoFormat('D MMM Y, HH:mm') ?? '—' }}
                                </p>
                            </div>

                            @if (! $attempt)
                                {{-- Belum mulai --}}
                                <form method="POST" action="{{ route('student.exam.start', $exam->id) }}">
                                    @csrf
                                    <button class="flex-none px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition">
                                        Mulai Ujian
                                    </button>
                                </form>
                            @elseif ($attempt->status === 'berlangsung')
                                {{-- Lanjutkan --}}
                                <a href="{{ route('exam.take', $exam->id) }}"
                                   class="flex-none px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-xl transition">
                                    Lanjutkan
                                </a>
                            @elseif ($attempt->status === 'selesai')
                                <span class="flex-none px-3 py-1.5 bg-green-100 text-green-700 text-xs font-semibold rounded-xl">
                                    Selesai ✓
                                </span>
                            @elseif ($attempt->status === 'dikeluarkan')
                                <span class="flex-none px-3 py-1.5 bg-red-100 text-red-600 text-xs font-semibold rounded-xl">
                                    Dikeluarkan
                                </span>
                            @endif
                        </div>

                        @if ($attempt && $attempt->status === 'berlangsung')
                            <div class="mt-3 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2 text-xs text-amber-700">
                                Ujian sedang berlangsung — sisa waktu: {{ gmdate('H:i:s', $attempt->sisaDetik()) }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Riwayat --}}
    <section>
        <h2 class="font-semibold text-slate-700 mb-3">Riwayat Ujian</h2>
        @if ($history->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 px-5 py-8 text-center text-slate-400 text-sm">
                Belum ada riwayat ujian.
            </div>
        @else
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 text-xs">Ujian</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 text-xs">Tanggal</th>
                            <th class="text-right px-4 py-3 font-semibold text-slate-500 text-xs">Skor</th>
                            <th class="text-right px-4 py-3 font-semibold text-slate-500 text-xs">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($history as $h)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-800">{{ $h->exam->judul }}</p>
                                    <p class="text-xs text-slate-400">{{ $h->exam->questionBank->subject->nama }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-500 text-xs">
                                    {{ $h->selesai_at?->isoFormat('D MMM Y, HH:mm') ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if ($h->total_skor !== null)
                                        <span class="font-bold text-slate-800">{{ number_format($h->total_skor, 1) }}</span>
                                    @else
                                        <span class="text-slate-400 text-xs">Dinilai...</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="inline-block px-2 py-0.5 text-xs rounded-full font-medium
                                        {{ $h->status === 'selesai' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                                        {{ $h->status === 'selesai' ? 'Selesai' : 'Dikeluarkan' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</main>

</body>
</html>
