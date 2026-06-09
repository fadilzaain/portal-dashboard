<?php

namespace App\Http\Controllers;

use App\Services\PelayananPasienService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PelayananPasienController extends Controller
{
    public function __construct(
        protected PelayananPasienService $service
    ) {}

    public function index(Request $request)
    {
        $bulan  = (int) $request->get('bulan',  Carbon::now()->month);
        $tahun  = (int) $request->get('tahun',  Carbon::now()->year);

        $dari   = Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
        $sampai = Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

        // ── Data dari Google Sheets ──
        $chartBOR   = $this->service->getChartBORBulanan($tahun);
        $chartAvlos = $this->service->getChartAvlosBulanan($tahun);
        $indikator  = $this->service->getIndikatorMutu($tahun, $dari, $sampai);

        // ── Data endpoint ada yang blm tersedia ──
        $ringkasanRanap = $this->service->getRingkasanRanap($dari, $sampai);
        $ringkasanRajal = $this->service->getRingkasanRajal($dari, $sampai);
        $ringkasanIGD   = $this->service->getRingkasanIGD($dari, $sampai);
        $trendHarian    = $this->service->getTrendHarian($dari, $sampai);
        $triageIGD      = $this->service->getIGDPerTriage($dari, $sampai);

        $kunjunganHariIni = $this->service->getKunjunganHariIni(); 

        return view('portal.pelayananpasien', [
            'bor' => $indikator['bor'],
            'los' => $indikator['los'],
            'toi' => $indikator['toi'],
            'bto' => $indikator['bto'],

            'chartBOR'   => $chartBOR,
            'chartAvlos' => $chartAvlos,

            'ringkasanRanap'   => $ringkasanRanap,
            'ringkasanRajal'   => $ringkasanRajal,
            'ringkasanIGD'     => $ringkasanIGD,
            'trendHarian'      => $trendHarian,
            'triageIGD'        => $triageIGD,

            'kunjunganHariIni' => $kunjunganHariIni, // ← tambah ini

            'tanggalMulai'   => $dari,
            'tanggalSelesai' => $sampai,
            'bulan'          => $bulan,
            'tahun'          => $tahun,

            'standar' => [
                'bor_min' => 60, 'bor_max' => 85,
                'los_min' => 3,  'los_max' => 12,
                'toi_min' => 1,  'toi_max' => 3,
                'bto_min' => 40, 'bto_max' => 50,
            ],
        ]);
    }

    public function detailRanap(Request $request)
    {
        $dari   = $request->get('dari',   Carbon::now()->startOfMonth()->format('Y-m-d'));
        $sampai = $request->get('sampai', Carbon::now()->format('Y-m-d'));
        $tahun  = (int) $request->get('tahun', Carbon::now()->year);

        $dataRanap = \App\Models\PelayananPasien::getDataRanap($dari, $sampai);

        return view('portal.pelayananpasien-ranap', compact(
            'dataRanap', 'dari', 'sampai', 'tahun'
        ));
    }

    public function borProxy(Request $request, $kode, $dari, $sampai)
    {
        $baseUrl = env('BOR_API_URL', 'http://192.168.10.8:8082');
        $url     = "{$baseUrl}/getborlostoi/{$kode}/{$dari}/{$sampai}";

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)->get($url);
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['succes' => false, 'rows' => [], 'msg' => $e->getMessage()], 500);
        }
    }
}