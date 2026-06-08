<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\KlaimBpjsController;
use App\Http\Controllers\KeuanganController;
use App\Http\Controllers\PelayananPasienController;
use App\Http\Controllers\SdmController;
use App\Http\Controllers\IndikatorMutuController;

// Root redirect
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
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



    // Portal utama 
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Portal redirect ke web pilihan
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
   // Dashboard Indikator Mutu
    Route::get('/portal/indikator-mutu', [IndikatorMutuController::class, 'index'])
        ->name('portal.indikatormutu');

    Route::get('/portal/indikator-mutu/data', [IndikatorMutuController::class, 'getData'])
        ->name('portal.indikatormutu.data');
    Route::get('/portal/indikator-mutu/ndr',  [IndikatorMutuController::class, 'getNdr'])  
    ->name('portal.indikatormutu.ndr');
    Route::get('/portal/indikator-mutu/gdr-ndr', [IndikatorMutuController::class, 'getGdrNdr'])
        ->name('portal.indikatormutu.gdrndr');

 

    // Dashboard Klaim BPJS
    Route::prefix('bpjs')->name('bpjs.')->group(function () {
        Route::get('/meta',        [KlaimBpjsController::class, 'meta']      )->name('meta');
        Route::get('/summary',     [KlaimBpjsController::class, 'summary']   )->name('summary');
        Route::get('/chart-jenis', [KlaimBpjsController::class, 'chartJenis'])->name('chart-jenis');
        Route::get('/list',        [KlaimBpjsController::class, 'list']      )->name('list');
    });

    // WEB API detail BOR, LOS, TOI
    Route::get('/api-proxy/borlostoi/{kode}/{dari}/{sampai}', function ($kode, $dari, $sampai) {
        $url = "http://192.168.10.8:8082/getborlostoi/{$kode}/{$dari}/{$sampai}";
        $response = \Illuminate\Support\Facades\Http::timeout(10)->get($url);
        return $response->json();
    });

    Route::get('/api-proxy/borlostoi/{kode}/{dari}/{sampai}', 
        [App\Http\Controllers\PelayananPasienController::class, 'borProxy']
    );

    
    Route::get('/bpjs', function() { 
    return view('portal.klaimbpjs'); 
})->name('portal.klaimbpjs')->middleware('auth');
});

// API verify token (untuk web eksternal)
Route::post('/api/portal/verify', [PortalController::class, 'apiVerify'])
    ->name('api.portal.verify')
    ->middleware('throttle:60,1');

    