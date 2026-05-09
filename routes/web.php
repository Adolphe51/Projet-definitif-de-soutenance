<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\AttackController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GeoController;
use App\Http\Controllers\HoneypotController;
use App\Http\Controllers\SimulationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

// 🔐 Routes d'authentification avec rate limiting
Route::get('/login', [LoginController::class, 'create'])
    ->name('login');

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
        'blocked.ip',
        'session.security',
        'ip.authorized',
        'audit'
    ])->group(function () {
        Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
    });
});

/*
|--------------------------------------------------------------------------
| CyberGuard (Interface + APIs internes)
|--------------------------------------------------------------------------
| On évite d'utiliser /admin pour l'interface réelle, car /admin est un piège honeypot
| (cf. config('cyberguard.honeypot.trap_paths')).
*/

Route::middleware(['secure', 'role:admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/stats', [DashboardController::class, 'apiStats'])->name('api.stats');

    // Attacks
    Route::get('/attacks', [AttackController::class, 'index'])->name('attacks.index');
    Route::get('/attacks/live', [AttackController::class, 'live'])->name('attacks.live');
    Route::get('/attacks/{id}', [AttackController::class, 'show'])->name('attacks.show');
    Route::delete('/attacks/{id}', [AttackController::class, 'destroy'])->name('attacks.destroy');
    Route::post('/attacks/block/{id}', [AttackController::class, 'block'])->name('attacks.block');
    Route::post('/attacks/unblock/{id}', [AttackController::class, 'unblock'])->name('attacks.unblock');
    Route::post('/attacks/{id}/alarm', [AttackController::class, 'triggerAlarm'])->name('attacks.alarm');
    Route::post('/attacks/{id}/status', [AttackController::class, 'updateStatus'])->name('attacks.status');
    Route::post('/attacks/detect', [AttackController::class, 'detect'])->name('attacks.detect');
    Route::get('/api/live-attacks', [AttackController::class, 'apiLive'])->name('api.live-attacks');

    // Alerts
    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::get('/alerts/unread', [AlertController::class, 'unread'])->name('alerts.unread');
    Route::post('/alerts/acknowledge/{id}', [AlertController::class, 'acknowledge'])->name('alerts.acknowledge');
    Route::post('/alerts/clear-all', [AlertController::class, 'clearAll'])->name('alerts.clear-all');
    Route::get('/alerts/api/count', [AlertController::class, 'apiCount'])->name('alerts.api.count');
    Route::get('/alerts/stream', [AlertController::class, 'stream'])->name('alerts.stream');

    // Honeypot (admin UI)
    Route::get('/honeypot', [HoneypotController::class, 'index'])->name('honeypot.index');
    Route::post('/honeypot/initialize', [HoneypotController::class, 'initialize'])->name('honeypot.initialize');
    Route::post('/honeypot/simulate/{id}', [HoneypotController::class, 'simulate'])->name('honeypot.simulate');
    Route::post('/honeypot/toggle/{id}', [HoneypotController::class, 'toggle'])->name('honeypot.toggle');
    Route::get('/honeypot/live-stats', [HoneypotController::class, 'liveStats'])->name('honeypot.live-stats');
    Route::get('/honeypot/{id}', [HoneypotController::class, 'detail'])->name('honeypot.detail');

    // Geo
    Route::get('/geo', [GeoController::class, 'attackers'])->name('geo.attackers');
    Route::get('/geo/trace/{ip}', [GeoController::class, 'trace'])->name('geo.trace');
    Route::get('/api/geo-data', [GeoController::class, 'apiGeoData'])->name('api.geo-data');

    // Simulations
    Route::get('/simulations', [SimulationController::class, 'index'])->name('simulations.index');
    Route::post('/simulations/launch', [SimulationController::class, 'launch'])->name('simulations.launch');
    Route::post('/simulations/stop/{id}', [SimulationController::class, 'stop'])->name('simulations.stop');
    Route::get('/simulations/status/{id}', [SimulationController::class, 'status'])->name('simulations.status');
    Route::get('/simulations/history', [SimulationController::class, 'history'])->name('simulations.history');
    Route::get('/simulations/api/feed', [SimulationController::class, 'apiFeed'])->name('simulations.api.feed');
    Route::post('/simulations/api/simulate', [SimulationController::class, 'apiSimulate'])->name('simulations.api.simulate');
});

/*
|--------------------------------------------------------------------------
| Honeypot Trap Pages (public)
|--------------------------------------------------------------------------
| Ces routes sont publiques par design (pièges). Elles sont journalisées
| par le middleware honeypot via config('cyberguard.honeypot.trap_paths').
*/

Route::middleware('honeypot')->group(function () {
    Route::match(['get', 'post'], '/admin', [HoneypotController::class, 'fakeAdmin'])->name('honeypot.trap.admin');
    Route::match(['get', 'post'], '/phpmyadmin', [HoneypotController::class, 'fakePhpMyAdmin'])->name('honeypot.trap.pma');
});

// Routes pour le mini site métier (système de test isolé)
// 🔐 Ordre des middlewares : csrf, blocked.ip, session.security, ip.authorized, audit
Route::prefix('intranet')->name('intranet.')->middleware([
    'csrf',
    'blocked.ip',
    'session.security',
    'ip.authorized',
    'audit'
])->group(function () {

    // Page d'accueil du mini site
    Route::get('/', function () {
        return view('intranet.index');
    })->name('index');

    // Routes pour les usagers
    Route::resource('students', \App\Http\Controllers\Intranet\StudentController::class);

    // Routes pour les services
    Route::resource('courses', \App\Http\Controllers\Intranet\CourseController::class);

    // Routes pour les messages
    Route::resource('messages', \App\Http\Controllers\Intranet\MessageController::class);
});

require __DIR__ . '/auth.php';
