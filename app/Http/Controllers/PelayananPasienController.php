<?php

namespace App\Http\Controllers;

use App\Services\PelayananPasienService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class PelayananPasienController extends Controller
{
    public function __construct(
        protected PelayananPasienService $service
    ) {}

    // =========================================================
    // HALAMAN UTAMA
    // =========================================================

    public function index(Request $request)
    {
        [$defaultTahun, $defaultBulan] = $this->defaultPeriode();

        $bulan  = (int) $request->get('bulan', $defaultBulan);
        $tahun  = (int) $request->get('tahun', $defaultTahun);

        $dari   = Carbon::create($tahun, $bulan, 1)->startOfMonth()->format('Y-m-d');
        $sampai = Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

        // ── Indikator & chart ─────────────────────────────────
        $indikator  = $this->service->getIndikatorMutu($tahun, $dari, $sampai);
        $chartBOR   = $this->service->getChartBORBulanan($tahun);
        $chartAvlos = $this->service->getChartAvlosBulanan($tahun);

        // ── Data live ─────────────────────────────────────────
        $kunjunganHariIni = $this->service->getKunjunganHariIni();
        $monitoringIGD    = $this->service->getMonitoringIGD();

        // ── Stub (aktifkan saat endpoint tersedia) ────────────
        $trendKunjungan = $this->service->getTrendKunjungan($tahun);
        $ringkasanRajal = $this->service->getRingkasanRajal($dari, $sampai);
        $triageIGD      = $this->service->getIGDPerTriage($dari, $sampai);

        return view('portal.pelayananpasien', [
            // Indikator KPI
            'bor' => $indikator['bor'],
            'los' => $indikator['los'],
            'toi' => $indikator['toi'],
            'bto' => $indikator['bto'],

            // Chart
            'chartBOR'   => $chartBOR,
            'chartAvlos' => $chartAvlos,

            // Live
            'kunjunganHariIni' => $kunjunganHariIni,
            'monitoringIGD'    => $monitoringIGD,

            // Stub
            // 'trendHarian'    => $trendHarian,
            'ringkasanRajal' => $ringkasanRajal,
            'triageIGD'      => $triageIGD,
            'trendKunjungan' => $trendKunjungan,

            // Meta filter
            'tanggalMulai'   => $dari,
            'tanggalSelesai' => $sampai,
            'bulan'          => $bulan,
            'tahun'          => $tahun,

            // Standar Depkes
            'standar'        => PelayananPasienService::STANDAR,
        ]);
    }

    private function defaultPeriode(): array
    {
        $now = Carbon::now();

        return $now->month === 1
            ? [$now->year - 1, 12]
            : [$now->year, $now->month - 1];
    }

    // Detail Tempat Tidur
    public function infottProxy(Request $request)
    {
        $url = config('services.infott_api.url', 'http://192.168.10.29/wslokal/kominfo/realtime/infott');
    
        try {
            $response = Http::timeout(10)->get($url);
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'metaData' => ['code' => '500', 'message' => $e->getMessage()],
                'response' => ['ruangan' => []],
            ], 500);
        }
    }

    // =========================================================
    // DETAIL RAWAT INAP
    // =========================================================

    public function detailRanap(Request $request)
    {
        $dari   = $request->get('dari',   Carbon::now()->startOfMonth()->format('Y-m-d'));
        $sampai = $request->get('sampai', Carbon::now()->format('Y-m-d'));
        $tahun  = (int) $request->get('tahun', Carbon::now()->year);

        $dataRanap = \App\Models\PelayananPasien::getDataRanap($dari, $sampai);

        return view('portal.pelayananpasien-ranap', compact('dataRanap', 'dari', 'sampai', 'tahun'));
    }

    // =========================================================
    // LIVE ENDPOINTS
    // =========================================================

    /**
     * Endpoint polling IGD ipanggil JS setiap 30 menit.
     */
    public function igdLive()
    {
        return response()->json(
            $this->service->getMonitoringIGD()
        );
    }

    // =========================================================
    // PROXY BOR API
    // =========================================================

    public function borProxy(Request $request, string $kode, string $dari, string $sampai)
    {
        $baseUrl = config('services.bor_api.url', 'http://192.168.10.8:8082');
        $url     = "{$baseUrl}/getborlostoi/{$kode}/{$dari}/{$sampai}";

        try {
            $response = Http::timeout(10)->get($url);
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'succes' => false,
                'rows'   => [],
                'msg'    => $e->getMessage(),
            ], 500);
        }
    }
}