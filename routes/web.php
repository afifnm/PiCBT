<?php

use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\StudentLoginController;
use App\Http\Controllers\Student\DashboardController as StudentDashboard;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — PiCBT
|--------------------------------------------------------------------------
*/

// Root redirect
Route::get('/', fn () => redirect()->route('admin.login'));

// ----------------------------------------------------------------
// Auth — Admin/Guru
// ----------------------------------------------------------------
Route::get('/login',        [AdminLoginController::class, 'showLogin'])->name('admin.login');
Route::post('/login',       [AdminLoginController::class, 'login'])->name('admin.login.post');
Route::post('/logout',      [AdminLoginController::class, 'logout']);

// ----------------------------------------------------------------
// Auth — Siswa
// ----------------------------------------------------------------
Route::get('/siswa/login',  [StudentLoginController::class, 'showLogin'])->name('student.login');
Route::post('/siswa/login', [StudentLoginController::class, 'login'])->name('student.login.post');
Route::post('/siswa/logout',[StudentLoginController::class, 'logout'])->name('student.logout');

// ----------------------------------------------------------------
// Siswa — dashboard + mulai ujian
// ----------------------------------------------------------------
Route::middleware(['auth.student'])->prefix('siswa')->name('student.')->group(function () {
    Route::get('/',                            [StudentDashboard::class, 'index'])->name('dashboard');
    Route::post('/exam/{examId}/start',        [StudentDashboard::class, 'startExam'])->name('exam.start');
});
