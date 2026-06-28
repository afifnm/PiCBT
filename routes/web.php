<?php

use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\StudentLoginController;
use App\Http\Controllers\Student\DashboardController as StudentDashboard;
use App\Http\Controllers\Student\ExamAttemptController as StudentAttemptController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — PiCBT
|--------------------------------------------------------------------------
*/

// Root redirect
Route::get('/', [AdminLoginController::class, 'redirectLogin']);

// ----------------------------------------------------------------
// Auth — Unified Login Portal
// ----------------------------------------------------------------
Route::get('/login', [AdminLoginController::class, 'showLogin'])->name('login');

// Admin/Guru login & logout
Route::post('/login',  [AdminLoginController::class, 'login'])->name('admin.login.post');

// Keep legacy names pointing to the same unified page
Route::get('/admin/login', fn() => redirect()->route('login'))->name('admin.login');

// Siswa login & logout
Route::post('/siswa/login',  [StudentLoginController::class, 'login'])->name('student.login.post');
Route::post('/siswa/logout', [StudentLoginController::class, 'logout'])->name('student.logout');

// Keep legacy name pointing to the unified page
Route::get('/siswa/login', fn() => redirect()->route('login'))->name('student.login');

// ----------------------------------------------------------------
// Siswa — dashboard + mulai ujian
// ----------------------------------------------------------------
Route::middleware(['auth.student'])->prefix('siswa')->name('student.')->group(function () {
    Route::get('/',                            [StudentDashboard::class, 'index'])->name('dashboard');
    Route::post('/exam/{examId}/start',        [StudentDashboard::class, 'startExam'])->name('exam.start');
    Route::get('/riwayat/{attempt}',           [StudentAttemptController::class, 'review'])->name('review');
});
