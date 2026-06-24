<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    public function destroy(QuestionBank $bank): JsonResponse
    {
        DB::transaction(fn () => $bank->delete());
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
