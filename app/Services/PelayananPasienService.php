<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class PelayananPasienService
{
    private const BULAN = [
        1  => 'Jan', 2  => 'Feb', 3  => 'Mar', 4  => 'Apr',
        5  => 'Mei', 6  => 'Jun', 7  => 'Jul', 8  => 'Ags',
        9  => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    public function __construct(
        protected GoogleSheetApiService $api
    ) {}

    // =========================================================
    // INDIKATOR MUTU — dari Google Sheets
    // =========================================================
    public function getIndikatorMutu(int $tahun, string $dari, string $sampai): array
    {
        $rateTahun  = $this->api->getRateTahun($tahun);
        $bulanDalam = $this->getBulanDalamRentang($dari, $sampai);

        $filtered = $rateTahun->filter(
            fn($r) => in_array($r->bulan, $bulanDalam) && $r->bor > 0
        );

        $data = $filtered->isNotEmpty()
            ? $filtered
            : $rateTahun->filter(fn($r) => $r->bor > 0);

        if ($data->isEmpty()) {
            return ['bor' => 0.0, 'los' => 0.0, 'toi' => 0.0, 'bto' => 0.0];
        }

        return [
            'bor' => round($data->avg('bor'),        2),
            'los' => round($data->avg('avlos'),       2),
            'toi' => round($data->avg('toi'),         2),
            'bto' => round($data->avg('bto') * 30,    2),
        ];
    }

    // =========================================================
    // CHART BOR BULANAN — dari Google Sheets
    // =========================================================
    public function getChartBORBulanan(int $tahun): Collection
    {
        $rateTahun = $this->api->getRateTahun($tahun);

        return collect(range(1, 12))->map(function ($m) use ($rateTahun) {
            $row = $rateTahun->firstWhere('bulan', $m);
            return (object) [
                'bulan' => self::BULAN[$m],
                'bor'   => $row ? round($row->bor, 2) : 0,
            ];
        });
    }

    // =========================================================
    // CHART BARBER-JOHNSON — dari Google Sheets
    // =========================================================
    public function getChartAvlosBulanan(int $tahun): Collection
    {
        $rateTahun = $this->api->getRateTahun($tahun);

        return collect(range(1, 12))->map(function ($m) use ($rateTahun, $tahun) {
            $row = $rateTahun->firstWhere('bulan', $m);
            return (object) [
                'bulan'   => self::BULAN[$m],
                'avlos'   => $row ? round($row->avlos,     2) : 0,
                'toi'     => $row ? round($row->toi,       2) : 0,
                'bor'     => $row ? round($row->bor,       2) : 0,
                'bto'     => $row ? round($row->bto * 30,  2) : 0,
                'periode' => Carbon::create($tahun, $m, 1)->daysInMonth,
            ];
        });
    }

    // =========================================================
    // RAWAT JALAN — belum ada endpoint
    // =========================================================
    public function getRingkasanRajal(string $dari, string $sampai): Collection
    {
        return collect();
    }

    // =========================================================
    // IGD — belum ada endpoint
    // =========================================================
    public function getRingkasanIGD(string $dari, string $sampai): array
    {
        return [
            'total'            => 0,
            'pulang'           => 0,
            'rawat_inap'       => 0,
            'meninggal'        => 0,
            'avg_waktu_tunggu' => 0,
        ];
    }

    // =========================================================
    // RAWAT INAP — belum ada endpoint
    // =========================================================
    public function getRingkasanRanap(string $dari, string $sampai): array
    {
        return [
            'total_masuk'     => 0,
            'total_keluar'    => 0,
            'masih_dirawat'   => 0,
            'total_meninggal' => 0,
        ];
    }

    // =========================================================
    // TREN HARIAN — belum ada endpoint
    // =========================================================
    public function getTrendHarian(string $dari, string $sampai): Collection
    {
        return collect();
    }

    // =========================================================
    // TRIAGE IGD — belum ada endpoint
    // =========================================================
    public function getIGDPerTriage(string $dari, string $sampai): Collection
    {
        return collect();
    }

    // =========================================================
    // HELPER
    // =========================================================
    public function getBulanDalamRentang(string $dari, string $sampai): array
    {
        $start = Carbon::parse($dari);
        $end   = Carbon::parse($sampai);
        $bulan = [];

        while ($start->lte($end)) {
            $bulan[] = $start->month;
            $start->addMonth();
        }

        return array_unique($bulan);
    }
}