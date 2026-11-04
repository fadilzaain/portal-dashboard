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
use App\Http\Controllers\GantiPasswordController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\DaftarController;
use App\Http\Controllers\IkuController;

// Root redirect
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});


// Auth 
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    // Dibatasi 10 percobaan per menit untuk mencegah tebak password
    Route::post('/login',   [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.post');

    // Pendaftaran akun baru (status "menunggu" sampai disetujui admin).
    // Dibatasi 5 kali per 10 menit per alamat IP supaya tidak dibanjiri akun palsu.
    Route::get('/daftar',  [DaftarController::class, 'form'])->name('register');
    Route::post('/daftar', [DaftarController::class, 'proses'])->middleware('throttle:5,10')->name('register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Protected (auth required)
Route::middleware(['auth', 'aktif', 'wajib.ganti'])->group(function () {

    // Ganti password sendiri: semua role. Dibatasi 6 percobaan per menit supaya password lama tidak bisa ditebak.
    Route::get('/akun/password', [GantiPasswordController::class, 'form'])->name('akun.password');
    Route::put('/akun/password', [GantiPasswordController::class, 'simpan'])
        ->middleware('throttle:6,1')
        ->name('akun.password.simpan');



    // Khusus admin: kelola pengguna dan rute cek database untuk pengecekan manual
    Route::middleware('role:admin')->group(function () {
        // Setujui pendaftar baru, ubah role, reset password, nonaktifkan akun
        Route::get('/pengguna', [PenggunaController::class, 'index'])->name('pengguna');
        Route::patch('/pengguna/{pengguna}', [PenggunaController::class, 'perbarui'])->name('pengguna.perbarui');

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
    });

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
    //monitoring igd
    Route::get('/portal/pelayananpasien/igd-live', [PelayananPasienController::class, 'igdLive'])
        ->name('portal.pelayananpasien.igd_live');
    //detail tempat tidur
    Route::get('/api-proxy/infott', [PelayananPasienController::class, 'infottProxy'])
    ->name('portal.pelayananpasien.infott_proxy');


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

    // Dashboard IKU (Indikator Kinerja Utama) — halaman lihat terbuka untuk semua role
    Route::get('/portal/iku', [IkuController::class, 'index'])
        ->name('portal.iku');

 

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