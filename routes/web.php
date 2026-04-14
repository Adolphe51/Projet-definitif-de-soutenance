<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

// 🔐 Routes d'authentification avec rate limiting
Route::get('/login', [LoginController::class, 'create'])
    ->name('login')
    ->middleware('throttle');

Route::post('/otp/send', [LoginController::class, 'sendOtp'])
    ->name('otp.send')
    ->middleware('throttle');

Route::post('/otp/resend', [LoginController::class, 'resendOtp'])
    ->name('otp.resend')
    ->middleware('throttle');

Route::get('/otp/verify', [LoginController::class, 'showVerifyForm'])
    ->name('otp.verify.form');

Route::post('/otp/verify', [LoginController::class, 'verifyOtp'])
    ->name('otp.verify')
    ->middleware('throttle');

Route::prefix('auth')->group(function () {

    // 🔐 Ordre des middlewares corrigé
    Route::middleware([
        'csrf',
        'honeypot',
        'blocked.ip',
        'session.security',
        'ip.authorized',
        'audit'
    ])->group(function () {
        Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
    });
});

// 🔐 Ordre des middlewares corrigé
Route::prefix('admin')->name('admin.')->middleware([
    'csrf',
    'honeypot',
    'blocked.ip',
    'session.security',
    'ip.authorized',
    'audit'
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
});

// Routes pour l'intranet académique (système de test isolé)
// 🔐 Ordre des middlewares corrigé : csrf, honeypot, blocked.ip, session.security, ip.authorized, audit
Route::prefix('intranet')->name('intranet.')->middleware([
    'csrf',
    'honeypot',
    'blocked.ip',
    'session.security',
    'ip.authorized',
    'audit'
])->group(function () {

    // Page d'accueil de l'intranet
    Route::get('/', function () {
        return view('intranet.index');
    })->name('index');

    // Routes pour les étudiants
    Route::resource('students', \App\Http\Controllers\Intranet\StudentController::class);

    // Routes pour les cours
    Route::resource('courses', \App\Http\Controllers\Intranet\CourseController::class);

    // Routes pour les messages
    Route::resource('messages', \App\Http\Controllers\Intranet\MessageController::class);

    // Routes supplémentaires pour les inscriptions et présences
    Route::get('enrollments', [\App\Http\Controllers\Intranet\EnrollmentController::class, 'index'])->name('enrollments.index');
    Route::get('attendances', [\App\Http\Controllers\Intranet\AttendanceController::class, 'index'])->name('attendances.index');
});

require __DIR__ . '/auth.php';
