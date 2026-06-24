<?php

use App\Http\Controllers\Student\ExamAttemptController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Student Exam Routes
|--------------------------------------------------------------------------
| All routes protected by 'auth.student' middleware.
| Rate-limiting applied to mutation endpoints.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth.student'])->prefix('exam')->name('exam.')->group(function () {

    // Show exam taking page
    Route::get('/{examId}/take', [ExamAttemptController::class, 'show'])
        ->name('take');

    // Attempt mutations — rate-limited
    Route::middleware(['throttle:exam_mutations'])->prefix('attempt/{attemptId}')->name('attempt.')->group(function () {

        // Auto-save individual answer (high frequency allowed)
        Route::post('/answer/{questionId}', [ExamAttemptController::class, 'saveAnswer'])
            ->middleware('throttle:exam_autosave')
            ->name('answer');

        // Heartbeat every 30s
        Route::post('/heartbeat', [ExamAttemptController::class, 'heartbeat'])
            ->name('heartbeat');

        // Cheat log (rate-limit per-ip to avoid abuse)
        Route::post('/cheat', [ExamAttemptController::class, 'logCheat'])
            ->middleware('throttle:exam_cheat')
            ->name('cheat');

        // Final submit
        Route::post('/submit', [ExamAttemptController::class, 'submit'])
            ->name('submit');
    });
});
