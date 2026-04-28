<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\KlaimBpjsController;
use App\Http\Controllers\KeuanganController;
use App\Http\Controllers\PelayananPasienController;
use App\Http\Controllers\SdmController;

// Root redirect
// Route::get('/', function () {
//     if (auth()->check()) {
//         return redirect()->route('dashboard');
//     }

//     return redirect()->route('login');
// })->name('home');

Route::get('/' , function(){
    return redirect()->route('dashboard');
});

Route::get('/cek-db', function () {
    $status = DB::connection('klaim_bpjs')->select("
        SELECT DISTINCT status FROM mon_klaim_rinap
    ");
    $statusRjalan = DB::connection('klaim_bpjs')->select("
        SELECT DISTINCT status FROM mon_klaim_rjalan
    ");
    return response()->json(['rinap' => $status, 'rjalan' => $statusRjalan]);
});

Route::get('/cek-tanggal', function () {
    $rinap = DB::connection('klaim_bpjs')->selectOne("
        SELECT MIN(tglPulang) as min_tgl, MAX(tglPulang) as max_tgl, COUNT(*) as total 
        FROM mon_klaim_rinap
    ");
    $rjalan = DB::connection('klaim_bpjs')->selectOne("
        SELECT MIN(tglSep) as min_tgl, MAX(tglSep) as max_tgl, COUNT(*) as total 
        FROM mon_klaim_rjalan
    ");
    return response()->json([
        'rinap'  => $rinap,
        'rjalan' => $rjalan,
        'server_now' => now()->toDateTimeString(),
    ]);
});

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register'])->name('register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Protected (auth required)
Route::middleware('auth')->group(function () {



    // Portal utama (landing 5 pilihan)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Portal redirect ke web eksternal (keuangan, dll)
    Route::get('/portal/keuangan', [KeuanganController::class, 'index'])
        ->name('portal.keuangan');

    Route::get('/api/dashboard-trend',  [KeuanganController::class, 'apiTrend']);
    Route::get('/api/dashboard-harian', [KeuanganController::class, 'apiHarian']);
    Route::get('/api/dashboard-unit',   [KeuanganController::class, 'apiUnit']);

    Route::get('/portal/verify-token', [PortalController::class, 'verifyToken'])->name('portal.verify');

    // Portal Pelayanan Pasien
    Route::get('/portal/pelayananpasien', [PelayananPasienController::class, 'index'])
        ->name('portal.pelayananpasien');

    Route::get('/portal/pelayananpasien/ranap', [PelayananPasienController::class, 'detailRanap'])
        ->name('portal.pelayananpasien.ranap');

    // Dashboard SDM
    Route::prefix('sdm')->name('sdm.')->middleware(['auth'])->group(function () {
        Route::get('/portal/sdm', [SdmController::class, 'index'])->name('portal.sdm');
    });
    // ── Dashboard Indikator Mutu (nanti) ──────────────────
    // Route::get('/mutu', [MutuController::class, 'index'])->name('mutu.index');

    // Dashboard Klaim BPJS
    Route::prefix('bpjs')->group(function () {
        Route::get('meta',    [KlaimBpjsController::class, 'meta']);   // ← tambah ini
        Route::get('summary', [KlaimBpjsController::class, 'summary']);
        Route::get('chart',   [KlaimBpjsController::class, 'chart']);
        Route::get('list',    [KlaimBpjsController::class, 'list']);
    });
    Route::get('/bpjs', function () {
        return view('portal.klaimbpjs');
    })->name('portal.klaimbpjs');

    Route::prefix('bpjs')->group(function () {
        Route::get('summary', [KlaimBpjsController::class, 'summary']);
        Route::get('chart',   [KlaimBpjsController::class, 'chart']);
        Route::get('list',    [KlaimBpjsController::class, 'list']);
    });
});

// API verify token (untuk web eksternal)
Route::post('/api/portal/verify', [PortalController::class, 'apiVerify'])
    ->name('api.portal.verify')
    ->middleware('throttle:60,1');

    