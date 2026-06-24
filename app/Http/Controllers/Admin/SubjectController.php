<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        return view('admin.subjects.index');
    }

    public function json(Request $request)
    {
        $subjects = Subject::query()
            ->when($request->search, fn ($q, $s) => $q->where('nama', 'like', "%$s%")->orWhere('kode', 'like', "%$s%"))
            ->withCount('questionBanks')
            ->orderBy('nama')
            ->get();

        return response()->json($subjects);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode' => 'required|string|max:20|unique:subjects,kode',
            'nama' => 'required|string|max:100',
        ]);

        $subject = Subject::create($data);

        return response()->json($subject, 201);
    }

    public function update(Request $request, Subject $subject)
    {
        $data = $request->validate([
            'kode' => 'required|string|max:20|unique:subjects,kode,' . $subject->id,
            'nama' => 'required|string|max:100',
        ]);

        $subject->update($data);

        return response()->json($subject);
    }

    public function destroy(Subject $subject)
    {
        if ($subject->questionBanks()->exists()) {
            return response()->json(['message' => 'Tidak dapat dihapus — mata pelajaran ini masih digunakan oleh bank soal.'], 422);
        }

        $subject->delete();

        return response()->json(['ok' => true]);
    }
}
