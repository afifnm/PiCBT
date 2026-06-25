<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\Subject;
use App\Services\QuestionTxtParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QuestionBankController extends Controller
{
    // -----------------------------------------------------------------------
    // Banks
    // -----------------------------------------------------------------------
    public function index(): View
    {
        $subjects = Subject::orderBy('nama')->get();
        return view('admin.question-banks.index', compact('subjects'));
    }

    public function json(Request $request): JsonResponse
    {
        $banks = QuestionBank::with('subject')
            ->withCount('questions')
            ->when($request->filled('search'), fn ($q) =>
                $q->where('judul', 'like', "%{$request->search}%")
            )
            ->orderBy('judul')
            ->get();

        return response()->json($banks);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'subject_id'  => ['required', 'exists:subjects,id'],
            'judul'       => ['required', 'string', 'max:255'],
            'deskripsi'   => ['nullable', 'string'],
        ]);

        $bank = DB::transaction(fn () =>
            QuestionBank::create([...$data, 'created_by' => auth()->id()])
        );

        return response()->json($bank->load('subject'), 201);
    }

    public function update(Request $request, QuestionBank $bank): JsonResponse
    {
        $data = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'judul'      => ['required', 'string', 'max:255'],
            'deskripsi'  => ['nullable', 'string'],
        ]);

        DB::transaction(fn () => $bank->update($data));

        return response()->json($bank->fresh()->load('subject'));
    }

    public function destroy(Request $request, QuestionBank $bank): JsonResponse
    {
        $force = $request->boolean('force');

        if (! $force && $bank->exams()->exists()) {
            return response()->json([
                'message' => 'Bank soal tidak dapat dihapus karena sudah digunakan pada jadwal ujian. Gunakan opsi "Paksa Hapus" jika Anda ingin menghapus beserta jadwal dan jawaban siswanya.'
            ], 422);
        }

        DB::transaction(function () use ($bank, $force) {
            if ($force) {
                $examIds = $bank->exams()->pluck('id');
                if ($examIds->isNotEmpty()) {
                    DB::table('exam_attempts')->whereIn('exam_id', $examIds)->delete();
                    DB::table('exams')->whereIn('id', $examIds)->delete();
                }
            }
            $bank->delete();
        });

        return response()->json(['ok' => true]);
    }

    // -----------------------------------------------------------------------
    // Questions within a bank
    // -----------------------------------------------------------------------
    public function questions(QuestionBank $bank): View
    {
        $questions = $bank->questions()->with('options')->get();
        return view('admin.question-banks.questions', compact('bank', 'questions'));
    }

    public function storeQuestion(Request $request, QuestionBank $bank): JsonResponse
    {
        $data = $request->validate([
            'tipe'          => ['required', 'in:pilihan_ganda,esai'],
            'pertanyaan'    => ['required', 'string'],
            'bobot'         => ['required', 'numeric', 'min:0.5'],
            'urutan'        => ['nullable', 'integer', 'min:1'],
            'kunci_jawaban' => ['nullable', 'string'],
            'options'       => ['required_if:tipe,pilihan_ganda', 'array'],
            'options.*.label'     => ['required_if:tipe,pilihan_ganda', 'string', 'size:1'],
            'options.*.teks_opsi' => ['required_if:tipe,pilihan_ganda', 'string'],
            'options.*.is_correct'=> ['boolean'],
        ]);

        $question = DB::transaction(function () use ($data, $bank) {
            $q = $bank->questions()->create([
                'tipe'          => $data['tipe'],
                'pertanyaan'    => $data['pertanyaan'],
                'bobot'         => $data['bobot'],
                'urutan'        => $data['urutan'] ?? ($bank->questions()->max('urutan') + 1),
                'kunci_jawaban' => $data['kunci_jawaban'] ?? null,
            ]);

            if ($data['tipe'] === 'pilihan_ganda') {
                foreach ($data['options'] as $opt) {
                    $q->options()->create([
                        'label'      => $opt['label'],
                        'teks_opsi'  => $opt['teks_opsi'],
                        'is_correct' => (bool) ($opt['is_correct'] ?? false),
                    ]);
                }
            }

            return $q;
        });

        return response()->json($question->load('options'), 201);
    }

    // -----------------------------------------------------------------------
    // Import soal dari TXT
    // -----------------------------------------------------------------------
    public function importQuestions(Request $request, QuestionBank $bank, QuestionTxtParser $parser): JsonResponse
    {
        $request->validate([
            'file' => ['nullable', 'file', 'mimetypes:text/plain', 'max:2048'],
            'teks' => ['nullable', 'string'],
        ]);

        $raw = $request->hasFile('file')
            ? (string) $request->file('file')->get()
            : (string) $request->input('teks', '');

        if (trim($raw) === '') {
            return response()->json([
                'message' => 'Silakan unggah file .txt atau tempel teks soal terlebih dahulu.',
            ], 422);
        }

        ['questions' => $parsed, 'errors' => $errors] = $parser->parse($raw);

        $imported = DB::transaction(function () use ($parsed, $bank) {
            $urutan = (int) $bank->questions()->max('urutan');
            $count  = 0;

            foreach ($parsed as $data) {
                $q = $bank->questions()->create([
                    'tipe'          => $data['tipe'],
                    'pertanyaan'    => $data['pertanyaan'],
                    'bobot'         => $data['bobot'],
                    'urutan'        => ++$urutan,
                    'kunci_jawaban' => $data['kunci_jawaban'] ?? null,
                ]);

                if ($data['tipe'] === 'pilihan_ganda') {
                    foreach ($data['options'] as $opt) {
                        $q->options()->create([
                            'label'      => $opt['label'],
                            'teks_opsi'  => $opt['teks_opsi'],
                            'is_correct' => (bool) ($opt['is_correct'] ?? false),
                        ]);
                    }
                }

                $count++;
            }

            return $count;
        });

        return response()->json([
            'imported' => $imported,
            'skipped'  => count($errors),
            'errors'   => $errors,
        ]);
    }

    public function importTemplate(): Response
    {
        $content = <<<TXT
        # Template Import Soal PiCBT
        # ---------------------------------------------------------------
        # Aturan:
        #  - Pisahkan tiap soal dengan satu baris kosong (atau garis ---).
        #  - TIPE: pg (pilihan ganda) atau esai. Default: pg.
        #  - BOBOT: angka, default 10.
        #  - SOAL: pertanyaan (boleh lebih dari satu baris).
        #  - Opsi PG ditulis "A. teks", tandai kunci dengan * di akhir.
        #  - RUBRIK: (untuk esai) jawaban acuan / rubrik penilaian AI.
        #  - Baris diawali '#' diabaikan (komentar).
        # ---------------------------------------------------------------

        TIPE: pg
        BOBOT: 10
        SOAL: Apa ibu kota Indonesia?
        A. Bandung
        B. Jakarta*
        C. Surabaya
        D. Medan

        TIPE: pg
        BOBOT: 10
        SOAL: 2 + 3 = ?
        A. 4
        B. 5*
        C. 6
        D. 7

        TIPE: esai
        BOBOT: 20
        SOAL: Jelaskan proses fotosintesis secara singkat.
        RUBRIK: Sebut reaktan (air, CO2, cahaya matahari) dan produk (glukosa, O2). Skor penuh bila ketiganya disebut dengan benar.
        TXT;

        return response($content, 200, [
            'Content-Type'        => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template-soal.txt"',
        ]);
    }

    public function importGuide(): View
    {
        return view('admin.question-banks.import-guide');
    }

    public function destroyAllQuestions(QuestionBank $bank): JsonResponse
    {
        DB::transaction(fn () => $bank->questions()->delete());
        return response()->json(['ok' => true]);
    }

    public function showQuestion(Question $question): JsonResponse
    {
        return response()->json($question->load('options'));
    }

    public function updateQuestion(Request $request, Question $question): JsonResponse
    {
        $data = $request->validate([
            'pertanyaan'    => ['required', 'string'],
            'bobot'         => ['required', 'numeric', 'min:0.5'],
            'urutan'        => ['nullable', 'integer', 'min:1'],
            'kunci_jawaban' => ['nullable', 'string'],
            'options'       => ['nullable', 'array'],
            'options.*.label'     => ['string', 'size:1'],
            'options.*.teks_opsi' => ['string'],
            'options.*.is_correct'=> ['boolean'],
        ]);

        DB::transaction(function () use ($data, $question) {
            $question->update([
                'pertanyaan'    => $data['pertanyaan'],
                'bobot'         => $data['bobot'],
                'urutan'        => $data['urutan'] ?? $question->urutan,
                'kunci_jawaban' => $data['kunci_jawaban'] ?? null,
            ]);

            if ($question->tipe === 'pilihan_ganda' && ! empty($data['options'])) {
                $question->options()->delete();
                foreach ($data['options'] as $opt) {
                    $question->options()->create([
                        'label'      => $opt['label'],
                        'teks_opsi'  => $opt['teks_opsi'],
                        'is_correct' => (bool) ($opt['is_correct'] ?? false),
                    ]);
                }
            }
        });

        return response()->json($question->fresh()->load('options'));
    }

    public function destroyQuestion(Question $question): JsonResponse
    {
        DB::transaction(fn () => $question->delete());
        return response()->json(['ok' => true]);
    }
}
