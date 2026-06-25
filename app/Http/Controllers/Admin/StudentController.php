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

        $students = $query->orderBy('nama')->paginate(20);

        // Append kelas_sekarang accessor and filter by kelas if needed
        $collection = $students->getCollection()->map(fn ($s) => array_merge($s->toArray(), [
            'kelas_sekarang' => $s->kelas_sekarang,
        ]));

        if ($request->filled('kelas')) {
            $collection = $collection->filter(
                fn ($s) => ($s['kelas_sekarang'] ?? '') === $request->kelas
            )->values();
        }

        return response()->json([
            'data' => $collection->values(),
            'meta' => [
                'total'        => $students->total(),
                'current_page' => $students->currentPage(),
                'last_page'    => $students->lastPage(),
                'from'         => $students->firstItem(),
                'to'           => $students->lastItem(),
            ],
        ]);
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
