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
        $dari   = $request->get('dari',   Carbon::now()->startOfMonth()->format('Y-m-d'));
        $sampai = $request->get('sampai', Carbon::now()->format('Y-m-d'));
        $tahun  = (int) $request->get('tahun', Carbon::now()->year);

        // ── Data dari Google Sheets (sudah aktif) ──
        $chartBOR   = $this->service->getChartBORBulanan($tahun);
        $chartAvlos = $this->service->getChartAvlosBulanan($tahun);
        $indikator  = $this->service->getIndikatorMutu($tahun, $dari, $sampai);

        // ── Data yang endpointnya belum tersedia (return kosong) ──
        $ringkasanRanap = $this->service->getRingkasanRanap($dari, $sampai);
        $ringkasanRajal = $this->service->getRingkasanRajal($dari, $sampai);
        $ringkasanIGD   = $this->service->getRingkasanIGD($dari, $sampai);
        $trendHarian    = $this->service->getTrendHarian($dari, $sampai);
        $triageIGD      = $this->service->getIGDPerTriage($dari, $sampai);

        return view('portal.pelayananpasien', [
            'bor' => $indikator['bor'],
            'los' => $indikator['los'],
            'toi' => $indikator['toi'],
            'bto' => $indikator['bto'],

            'chartBOR'   => $chartBOR,
            'chartAvlos' => $chartAvlos,

            'ringkasanRanap' => $ringkasanRanap,
            'ringkasanRajal' => $ringkasanRajal,
            'ringkasanIGD'   => $ringkasanIGD,
            'trendHarian'    => $trendHarian,
            'triageIGD'      => $triageIGD,

            'tanggalMulai'   => $dari,
            'tanggalSelesai' => $sampai,
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
}