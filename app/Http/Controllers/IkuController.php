<?php

namespace App\Http\Controllers;

use App\Models\IkuIndikator;
use App\Services\IkuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class IkuController extends Controller
{
    public function __construct(
        protected IkuService $service
    ) {}

    // Halaman utama Dashboard IKU. Terbuka untuk semua role yang sudah login (dijaga middleware di routes/web.php).
    public function index(Request $request): View
    {
        $triwulanOpsi = IkuService::ROMAWI;

        try {
            [$tahunList, $tahun, $triwulan] = $this->filter($request);

            $data  = $this->service->dataDashboard($tahun, $triwulan);
            $error = null;
        } catch (\Throwable $e) {
            // Detail error hanya ke log, pengguna cukup melihat pesan umum (tidak membocorkan isi database/server)
            Log::error('Dashboard IKU gagal dimuat: ' . $e->getMessage());

            $tahunList = [(int) now()->year];
            $tahun     = $tahunList[0];
            $triwulan  = 1;
            $data      = null;
            $error     = 'Data IKU belum bisa dimuat. Coba muat ulang halaman, atau hubungi admin kalau masih berulang.';
        }

        return view('portal.iku', compact('data', 'error', 'tahun', 'triwulan', 'tahunList', 'triwulanOpsi'));
    }

    // Halaman detail satu indikator. {indikator} otomatis dicari lewat route model binding (tidak ketemu = 404).
    public function detail(Request $request, IkuIndikator $indikator): View
    {
        // Indikator yang sudah dinonaktifkan tidak ditampilkan
        abort_unless($indikator->is_active, 404);

        $triwulanOpsi = IkuService::ROMAWI;

        [$tahunList, $tahun, $triwulan] = $this->filter($request);

        $data = $this->service->detail($indikator, $tahun, $triwulan);

        return view('portal.iku-detail', compact('data', 'tahun', 'triwulan', 'tahunList', 'triwulanOpsi'));
    }

    // Membaca filter tahun & triwulan dari query string, divalidasi:
    // kalau tidak valid, pakai default (tahun terbaru / triwulan terakhir yang sudah berdata).
    private function filter(Request $request): array
    {
        $tahunList = $this->service->tahunTersedia();

        $tahun = (int) $request->query('tahun');
        if (! in_array($tahun, $tahunList, true)) {
            $tahun = $tahunList[0];
        }

        $triwulan = (int) $request->query('triwulan');
        if (! array_key_exists($triwulan, IkuService::ROMAWI)) {
            $triwulan = $this->service->triwulanTerakhir($tahun);
        }

        return [$tahunList, $tahun, $triwulan];
    }
}