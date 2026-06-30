<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\QuestionBankController;
use App\Http\Controllers\Admin\ResultController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\UserController;
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
    // Master User (Admin & Guru)
    // ----------------------------------------------------------------
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/',                          [UserController::class, 'index'])->name('index');
        Route::get('/json',                      [UserController::class, 'json'])->name('json');
        Route::post('/',                         [UserController::class, 'store'])->name('store');
        Route::put('/{user}',                    [UserController::class, 'update'])->name('update');
        Route::patch('/{user}/reset-password',   [UserController::class, 'resetPassword'])->name('reset-password');
        Route::delete('/{user}',                 [UserController::class, 'destroy'])->name('destroy');
    });

    // ----------------------------------------------------------------
    // Profil user yang login
    // ----------------------------------------------------------------
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/',    [ProfileController::class, 'edit'])->name('edit');
        Route::put('/',    [ProfileController::class, 'update'])->name('update');
    });

    // ----------------------------------------------------------------
    // Master Siswa
    // ----------------------------------------------------------------
    Route::prefix('students')->name('students.')->group(function () {
        Route::get('/',           [StudentController::class, 'index'])->name('index');
        Route::get('/json',       [StudentController::class, 'json'])->name('json');
        Route::get('/classes-json', [StudentController::class, 'classesJson'])->name('classes-json');
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
        Route::get('/questions/import/template',      [QuestionBankController::class, 'importTemplate'])->name('questions.template');
        Route::get('/questions/import/panduan',       [QuestionBankController::class, 'importGuide'])->name('questions.guide');
        Route::get('/{bank}/questions',               [QuestionBankController::class, 'questions'])->name('questions');
        Route::post('/{bank}/questions',              [QuestionBankController::class, 'storeQuestion'])->name('questions.store');
        Route::post('/questions/ai/unlock',            [QuestionBankController::class, 'unlockAiGenerator'])
            ->middleware('throttle:ai_passcode')
            ->name('questions.ai-unlock');
        Route::post('/{bank}/questions/ai-generate',  [QuestionBankController::class, 'generateAiQuestions'])
            ->middleware('throttle:ai_question_generation')
            ->name('questions.ai-generate');
        Route::post('/{bank}/questions/import',       [QuestionBankController::class, 'importQuestions'])->name('questions.import');
        Route::delete('/{bank}/questions',            [QuestionBankController::class, 'destroyAllQuestions'])->name('questions.destroyAll');
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
        Route::get('/exam/{exam}',                    [ResultController::class, 'exam'])->name('exam');
        Route::get('/attempt/{attempt}',              [ResultController::class, 'detail'])->name('detail');
        Route::delete('/attempt/{attempt}/reset',     [ResultController::class, 'resetAttempt'])->name('attempt.reset');
        Route::delete('/exam/{exam}/reset-all',       [ResultController::class, 'resetAllAttempts'])->name('exam.reset-all');
        Route::patch('/answer/{answer}/score',        [ResultController::class, 'overrideScore'])->name('override');
        Route::post('/answer/{answer}/ai-score',      [ResultController::class, 'aiScore'])->name('ai-score');
        Route::get('/export/excel',                   [ResultController::class, 'exportExcel'])->name('export.excel');
        Route::get('/print',                          [ResultController::class, 'printResults'])->name('print');
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
    Route::post('/logout', [App\Http\Controllers\Auth\AdminLoginController::class, 'logout'])->name('logout');
});
