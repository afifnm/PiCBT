<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\StudentsImport;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudentController extends Controller
{
    public function index()
    {
        return view('admin.students.index');
    }

    public function json(Request $request): JsonResponse
    {
        $query = Student::query();

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn ($sq) =>
                $sq->where('nama', 'like', "%{$q}%")
                   ->orWhere('nis', 'like', "%{$q}%")
            );
        }

        $allStudents = $query->orderBy('nama')->get();

        $collection = $allStudents->map(fn ($s) => array_merge($s->toArray(), [
            'kelas_sekarang' => $s->kelas_sekarang,
            'nama_kelas'     => $s->nama_kelas,
        ]));

        if ($request->filled('kelas')) {
            $collection = $collection->filter(
                fn ($s) => ($s['nama_kelas'] ?? '') === $request->kelas
            )->values();
        }

        $page = $request->input('page', 1);
        $perPage = 20;
        $total = $collection->count();
        $pagedData = $collection->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'data' => $pagedData,
            'meta' => [
                'total'        => $total,
                'current_page' => (int) $page,
                'last_page'    => ceil($total / $perPage),
                'from'         => $total > 0 ? ($page - 1) * $perPage + 1 : null,
                'to'           => $total > 0 ? min($page * $perPage, $total) : null,
            ],
        ]);
    }

    public function classesJson(): JsonResponse
    {
        $students = Student::select('id', 'tahun_masuk', 'jurusan', 'kelas_awal')->get();
        $classes = [];
        foreach ($students as $s) {
            $nk = $s->nama_kelas;
            if (!isset($classes[$nk])) {
                $classes[$nk] = 0;
            }
            $classes[$nk]++;
        }
        
        $result = [];
        foreach ($classes as $nama => $count) {
            $result[] = ['nama_kelas' => $nama, 'count' => $count];
        }
        
        usort($result, function($a, $b) {
            return strnatcmp($a['nama_kelas'], $b['nama_kelas']); // Use strnatcmp for better X, XI, XII sorting
        });
        
        return response()->json(['data' => $result]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nis'          => ['required', 'string', 'max:20', 'unique:students,nis'],
            'nama'         => ['required', 'string', 'max:255'],
            'tahun_masuk'  => ['required', 'integer', 'min:2000', 'max:' . date('Y')],
            'jurusan'      => ['nullable', 'string', 'max:100'],
            'password'     => ['nullable', 'string', 'min:6'],
        ]);

        $student = DB::transaction(function () use ($data) {
            return Student::create([
                'nis'         => $data['nis'],
                'nama'        => $data['nama'],
                'tahun_masuk' => $data['tahun_masuk'],
                'jurusan'     => $data['jurusan'] ?? null,
                'password'    => Hash::make($data['password'] ?? $data['nis']),
            ]);
        });

        return response()->json(['id' => $student->id, 'kelas_sekarang' => $student->kelas_sekarang], 201);
    }

    public function update(Request $request, Student $student): JsonResponse
    {
        $data = $request->validate([
            'nama'         => ['required', 'string', 'max:255'],
            'tahun_masuk'  => ['required', 'integer', 'min:2000', 'max:' . date('Y')],
            'jurusan'      => ['nullable', 'string', 'max:100'],
        ]);

        DB::transaction(fn () => $student->update($data));

        return response()->json(['kelas_sekarang' => $student->fresh()->kelas_sekarang]);
    }

    public function destroy(Student $student): JsonResponse
    {
        DB::transaction(fn () => $student->delete());
        return response()->json(['ok' => true]);
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120']]);

        $import = new StudentsImport();
        Excel::import($import, $request->file('file'));

        return response()->json([
            'success' => $import->successCount,
            'failed'  => $import->failureCount,
            'errors'  => $import->errors,
        ]);
    }

    public function template(): BinaryFileResponse
    {
        $path = storage_path('app/templates/import_siswa_template.xlsx');
        if (! file_exists($path)) {
            // Generate on-the-fly if template file doesn't exist yet
            $this->generateTemplate($path);
        }
        return response()->download($path, 'template_import_siswa.xlsx');
    }

    private function generateTemplate(string $path): void
    {
        $dir = dirname($path);
        if (! is_dir($dir)) mkdir($dir, 0755, true);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['nis', 'nama', 'tahun_masuk', 'jurusan'];
        foreach ($headers as $col => $header) {
            $sheet->setCellValueByColumnAndRow($col + 1, 1, $header);
        }

        $sheet->setCellValueByColumnAndRow(1, 2, '2025001');
        $sheet->setCellValueByColumnAndRow(2, 2, 'Contoh Nama');
        $sheet->setCellValueByColumnAndRow(3, 2, 2025);
        $sheet->setCellValueByColumnAndRow(4, 2, 'TKJ');

        (new Xlsx($spreadsheet))->save($path);
    }
}
