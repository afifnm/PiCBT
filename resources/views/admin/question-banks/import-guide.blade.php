@extends('layouts.admin')
@section('title', 'Panduan Import Soal')
@section('page-title', 'Panduan Import Soal')

@section('content')
<div class="max-w-3xl space-y-6">

    {{-- Intro --}}
    <div class="card p-6">
        <p class="text-sm text-surface-600 dark:text-surface-300 leading-relaxed">
            Fitur <b>Import TXT</b> memungkinkan Anda menambahkan banyak soal sekaligus ke dalam sebuah
            bank soal hanya dengan satu file teks (<code>.txt</code>) atau dengan menempelkan teks soal.
            Mendukung soal <b>Pilihan Ganda</b> maupun <b>Esai</b>.
        </p>
        <div class="mt-4">
            <a href="{{ route('admin.banks.questions.template') }}" class="btn-primary inline-flex">
                ⬇ Download Template TXT
            </a>
        </div>
    </div>

    {{-- Langkah --}}
    <div class="card p-6">
        <h3 class="font-semibold text-surface-800 dark:text-surface-100 mb-4">Langkah-langkah</h3>
        <ol class="space-y-3 text-sm text-surface-600 dark:text-surface-300">
            <li class="flex gap-3">
                <span class="flex-none w-6 h-6 rounded-full bg-primary-50 text-primary-700 dark:bg-primary-950/50 dark:text-primary-400 text-xs font-bold flex items-center justify-center">1</span>
                <span>Unduh template di atas, lalu isi/sunting sesuai soal Anda. Bisa juga membuat file <code>.txt</code> sendiri.</span>
            </li>
            <li class="flex gap-3">
                <span class="flex-none w-6 h-6 rounded-full bg-primary-50 text-primary-700 dark:bg-primary-950/50 dark:text-primary-400 text-xs font-bold flex items-center justify-center">2</span>
                <span>Pastikan format setiap soal mengikuti aturan di bawah.</span>
            </li>
            <li class="flex gap-3">
                <span class="flex-none w-6 h-6 rounded-full bg-primary-50 text-primary-700 dark:bg-primary-950/50 dark:text-primary-400 text-xs font-bold flex items-center justify-center">3</span>
                <span>Buka <b>Bank Soal &rarr; (pilih bank) &rarr; Import TXT</b>, unggah file atau tempel teksnya, lalu klik <b>Import Soal</b>.</span>
            </li>
            <li class="flex gap-3">
                <span class="flex-none w-6 h-6 rounded-full bg-primary-50 text-primary-700 dark:bg-primary-950/50 dark:text-primary-400 text-xs font-bold flex items-center justify-center">4</span>
                <span>Periksa hasilnya: jumlah soal berhasil/dilewati ditampilkan. Soal yang formatnya salah akan dilewati dan dilaporkan, soal yang valid tetap masuk.</span>
            </li>
        </ol>
    </div>

    {{-- Field --}}
    <div class="card p-6">
        <h3 class="font-semibold text-surface-800 dark:text-surface-100 mb-4">Daftar Field</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-surface-400 dark:text-surface-500 border-b border-surface-100 dark:border-surface-800">
                        <th class="py-2 pr-4 font-semibold">Field</th>
                        <th class="py-2 pr-4 font-semibold">Wajib</th>
                        <th class="py-2 font-semibold">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="text-surface-600 dark:text-surface-300">
                    <tr class="border-b border-surface-50 dark:border-surface-800/60">
                        <td class="py-2 pr-4"><code>TIPE:</code></td>
                        <td class="py-2 pr-4">Tidak</td>
                        <td class="py-2"><code>pg</code> (pilihan ganda) atau <code>esai</code>. Default <code>pg</code>.</td>
                    </tr>
                    <tr class="border-b border-surface-50 dark:border-surface-800/60">
                        <td class="py-2 pr-4"><code>BOBOT:</code></td>
                        <td class="py-2 pr-4">Tidak</td>
                        <td class="py-2">Angka bobot soal. Default <code>10</code>.</td>
                    </tr>
                    <tr class="border-b border-surface-50 dark:border-surface-800/60">
                        <td class="py-2 pr-4"><code>SOAL:</code></td>
                        <td class="py-2 pr-4"><span class="text-red-500 font-semibold">Ya</span></td>
                        <td class="py-2">Teks pertanyaan. Boleh lebih dari satu baris.</td>
                    </tr>
                    <tr class="border-b border-surface-50 dark:border-surface-800/60">
                        <td class="py-2 pr-4"><code>A. teks</code></td>
                        <td class="py-2 pr-4">PG</td>
                        <td class="py-2">Opsi jawaban (A–E). Tambahkan <code>*</code> di akhir untuk menandai kunci. Minimal 2 opsi, tepat 1 kunci.</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4"><code>RUBRIK:</code></td>
                        <td class="py-2 pr-4">Tidak</td>
                        <td class="py-2">Untuk esai: jawaban acuan/rubrik penilaian AI. (Alias: <code>KUNCI:</code>)</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-xs text-surface-400 dark:text-surface-500 mt-4">
            Catatan: pisahkan tiap soal dengan <b>satu baris kosong</b> (atau baris berisi <code>---</code>).
            Baris yang diawali tanda <code>#</code> dianggap komentar dan diabaikan.
        </p>
    </div>

    {{-- Contoh --}}
    <div class="card p-6">
        <h3 class="font-semibold text-surface-800 dark:text-surface-100 mb-4">Contoh Isi File</h3>
        <pre class="bg-surface-900 text-surface-100 dark:bg-surface-950 rounded-xl p-4 text-xs leading-relaxed overflow-x-auto"><code>TIPE: pg
BOBOT: 10
SOAL: Apa ibu kota Indonesia?
A. Bandung
B. Jakarta*
C. Surabaya
D. Medan

TIPE: esai
BOBOT: 20
SOAL: Jelaskan proses fotosintesis secara singkat.
RUBRIK: Sebut reaktan (air, CO2, cahaya matahari) dan produk
(glukosa, O2). Skor penuh bila ketiganya disebut dengan benar.</code></pre>
    </div>

    {{-- Buat Soal dengan AI --}}
    <div class="card p-6">
        <h3 class="font-semibold text-surface-800 dark:text-surface-100 mb-2">Buat Soal dengan Bantuan AI</h3>
        <p class="text-sm text-surface-600 dark:text-surface-300 mb-4">
            Anda bisa meminta AI seperti <b>ChatGPT</b>, <b>Gemini</b>, atau <b>Claude</b> untuk membuat soal dalam format yang langsung bisa diimpor.
            Cukup salin prompt di bawah, sesuaikan bagian dalam kurung siku <code>[&hellip;]</code>, tempel ke AI, lalu salin hasilnya ke kolom <b>Tempel Teks Soal</b>.
        </p>

        {{-- Prompt template --}}
        <div class="relative">
            <pre id="ai-prompt-template" class="bg-surface-900 text-surface-100 dark:bg-surface-950 rounded-xl p-4 text-xs leading-relaxed overflow-x-auto whitespace-pre-wrap">Buatkan [N] soal [pilihan ganda / esai] tentang [topik] untuk tingkat [SD/SMP/SMA/Kuliah].

Gunakan format berikut untuk setiap soal:

TIPE: pg
BOBOT: 10
SOAL: [teks pertanyaan]
A. [opsi A]
B. [opsi B]*
C. [opsi C]
D. [opsi D]

TIPE: esai
BOBOT: 20
SOAL: [teks pertanyaan]
RUBRIK: [kriteria penilaian]

Aturan penting:
- Pisahkan setiap soal dengan satu baris kosong
- Untuk pilihan ganda, tandai jawaban benar dengan tanda bintang (*) di akhir opsi
- Untuk esai, sertakan RUBRIK berisi kriteria penilaian
- Jangan tambahkan teks lain di luar format di atas</pre>
            <button onclick="copyAiPrompt(this)"
                class="absolute top-2 right-2 text-xs px-3 py-1 rounded-lg bg-surface-700 hover:bg-surface-600 text-surface-200 transition-colors">
                Salin
            </button>
        </div>

        {{-- Langkah --}}
        <ol class="mt-4 space-y-2 text-sm text-surface-600 dark:text-surface-300">
            <li class="flex gap-3">
                <span class="flex-none w-6 h-6 rounded-full bg-primary-50 text-primary-700 dark:bg-primary-950/50 dark:text-primary-400 text-xs font-bold flex items-center justify-center">1</span>
                <span>Klik <b>Salin</b> di atas, lalu tempel ke ChatGPT, Gemini, atau Claude.</span>
            </li>
            <li class="flex gap-3">
                <span class="flex-none w-6 h-6 rounded-full bg-primary-50 text-primary-700 dark:bg-primary-950/50 dark:text-primary-400 text-xs font-bold flex items-center justify-center">2</span>
                <span>Ubah bagian <code>[N]</code>, <code>[topik]</code>, dan <code>[tingkat]</code> sesuai kebutuhan, lalu kirim ke AI.</span>
            </li>
            <li class="flex gap-3">
                <span class="flex-none w-6 h-6 rounded-full bg-primary-50 text-primary-700 dark:bg-primary-950/50 dark:text-primary-400 text-xs font-bold flex items-center justify-center">3</span>
                <span>Salin seluruh teks hasil respons AI, lalu tempel ke kolom <b>Tempel Teks Soal</b> dan klik <b>Import Soal</b>.</span>
            </li>
        </ol>
    </div>

</div>

@push('scripts')
<script>
function copyAiPrompt(btn) {
    const text = document.getElementById('ai-prompt-template').innerText;
    navigator.clipboard.writeText(text).then(() => {
        const original = btn.textContent;
        btn.textContent = 'Tersalin!';
        setTimeout(() => { btn.textContent = original; }, 2000);
    });
}
</script>
@endpush
@endsection
