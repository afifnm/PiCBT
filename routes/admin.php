<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\QuestionBankController;
use App\Http\Controllers\Admin\ResultController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin / Guru Routes
|--------------------------------------------------------------------------
| Protected by 'auth' middleware (web guard).
| Prefix: /admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'active.user'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ----------------------------------------------------------------
    // Master Siswa
    // ----------------------------------------------------------------
    Route::prefix('students')->name('students.')->group(function () {
        Route::get('/',           [StudentController::class, 'index'])->name('index');
        Route::get('/json',       [StudentController::class, 'json'])->name('json');
        Route::post('/',          [StudentController::class, 'store'])->name('store');
        Route::put('/{student}',  [StudentController::class, 'update'])->name('update');
        Route::delete('/{student}', [StudentController::class, 'destroy'])->name('destroy');
        Route::post('/import',    [StudentController::class, 'import'])->name('import');
        Route::get('/template',   [StudentController::class, 'template'])->name('template');
    });

    // ----------------------------------------------------------------
    // Mata Pelajaran (simple CRUD, reuse controller inline or separate)
    // ----------------------------------------------------------------
    Route::prefix('subjects')->name('subjects.')->group(function () {
        Route::get('/',              [SubjectController::class, 'index'])->name('index');
        Route::get('/json',          [SubjectController::class, 'json'])->name('json');
        Route::post('/',             [SubjectController::class, 'store'])->name('store');
        Route::put('/{subject}',     [SubjectController::class, 'update'])->name('update');
        Route::delete('/{subject}',  [SubjectController::class, 'destroy'])->name('destroy');
    });

    // ----------------------------------------------------------------
    // Bank Soal
    // ----------------------------------------------------------------
    Route::prefix('banks')->name('banks.')->group(function () {
        Route::get('/',                               [QuestionBankController::class, 'index'])->name('index');
        Route::get('/json',                           [QuestionBankController::class, 'json'])->name('json');
        Route::post('/',                              [QuestionBankController::class, 'store'])->name('store');
        Route::put('/{bank}',                         [QuestionBankController::class, 'update'])->name('update');
        Route::delete('/{bank}',                      [QuestionBankController::class, 'destroy'])->name('destroy');
        Route::get('/{bank}/questions',               [QuestionBankController::class, 'questions'])->name('questions');
        Route::post('/{bank}/questions',              [QuestionBankController::class, 'storeQuestion'])->name('questions.store');
    });

    // ----------------------------------------------------------------
    // Soal (question-level endpoints without bank context)
    // ----------------------------------------------------------------
    Route::prefix('questions')->name('questions.')->group(function () {
        Route::get('/{question}',    [QuestionBankController::class, 'showQuestion'])->name('show');
        Route::put('/{question}',    [QuestionBankController::class, 'updateQuestion'])->name('update');
        Route::delete('/{question}', [QuestionBankController::class, 'destroyQuestion'])->name('destroy');
    });

    // ----------------------------------------------------------------
    // Ujian
    // ----------------------------------------------------------------
    Route::prefix('exams')->name('exams.')->group(function () {
        Route::get('/',                        [ExamController::class, 'index'])->name('index');
        Route::get('/json',                    [ExamController::class, 'json'])->name('json');
        Route::post('/',                       [ExamController::class, 'store'])->name('store');
        Route::put('/{exam}',                  [ExamController::class, 'update'])->name('update');
        Route::patch('/{exam}/status',         [ExamController::class, 'updateStatus'])->name('status');
        Route::get('/{exam}/monitor',          [ExamController::class, 'monitor'])->name('monitor');
        Route::get('/{exam}/monitor/json',     [ExamController::class, 'monitorJson'])->name('monitor.json');
    });

    // ----------------------------------------------------------------
    // Rekap Nilai
    // ----------------------------------------------------------------
    Route::prefix('results')->name('results.')->group(function () {
        Route::get('/',                               [ResultController::class, 'index'])->name('index');
        Route::get('/json',                           [ResultController::class, 'json'])->name('json');
        Route::get('/attempt/{attempt}',              [ResultController::class, 'detail'])->name('detail');
        Route::patch('/answer/{answer}/score',        [ResultController::class, 'overrideScore'])->name('override');
        Route::get('/export/excel',                   [ResultController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf',                     [ResultController::class, 'exportPdf'])->name('export.pdf');
    });

    // ----------------------------------------------------------------
    // Pengaturan
    // ----------------------------------------------------------------
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/',           [SettingController::class, 'index'])->name('index');
        Route::put('/',           [SettingController::class, 'update'])->name('update');
        Route::post('/test-gemini', [SettingController::class, 'testGemini'])->name('test-gemini');
    });

    // Logout
    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});
