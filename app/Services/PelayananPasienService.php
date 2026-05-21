<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * PelayananPasienService
 *
 * Sumber data:
 *   ✓ BOR / AVLOS / TOI / BTO  → Google Sheets via GoogleSheetApiService::getRateTahun()
 *   ○ Rajal / IGD / Ranap / Tren / Triage → endpoint belum tersedia, return kosong
 *
 * Ketika endpoint baru siap di GoogleSheetApiService, uncomment method yang sesuai
 * dan ganti return collect() / return [] dengan pemanggilan API-nya.
 */
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
    // INDIKATOR MUTU — dari Google Sheets (sudah aktif)
    // =========================================================

    /**
     * Rata-rata BOR/LOS/TOI/BTO untuk rentang tanggal yang dipilih.
     * Kalau rentang tanggal tidak menghasilkan data, fallback ke rata-rata tahun.
     *
     * @return array{bor: float, los: float, toi: float, bto: float}
     */
    public function getIndikatorMutu(int $tahun, string $dari, string $sampai): array
    {
        $rateTahun  = $this->api->getRateTahun($tahun);
        $bulanDalam = $this->getBulanDalamRentang($dari, $sampai);

        // Coba filter per rentang tanggal dulu
        $filtered = $rateTahun->filter(
            fn($r) => in_array($r->bulan, $bulanDalam) && $r->bor > 0
        );

        // Kalau tidak ada data di rentang itu, pakai semua bulan aktif di tahun itu
        $data = $filtered->isNotEmpty()
            ? $filtered
            : $rateTahun->filter(fn($r) => $r->bor > 0);

        if ($data->isEmpty()) {
            return ['bor' => 0.0, 'los' => 0.0, 'toi' => 0.0, 'bto' => 0.0];
        }

        return [
            'bor' => round($data->avg('bor'),   2),
            'los' => round($data->avg('avlos'),  2),
            'toi' => round($data->avg('toi'),    2),
            'bto' => round($data->avg('bto') * 30, 2), // konversi ke per-bulan
        ];
    }

    // =========================================================
    // CHART BOR BULANAN — dari Google Sheets (sudah aktif)
    // =========================================================

    /**
     * BOR per bulan untuk satu tahun penuh.
     * Bulan tanpa data → bor: 0 (ditampilkan "belum ada data" di chart).
     */
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
    // CHART BARBER-JOHNSON — dari Google Sheets (sudah aktif)
    // =========================================================

    /**
     * AVLOS, TOI, BOR, BTO per bulan untuk grafik Barber-Johnson.
     * Field 'periode' = jumlah hari dalam bulan (untuk rumus garis BTO).
     */
    public function getChartAvlosBulanan(int $tahun): Collection
    {
        $rateTahun = $this->api->getRateTahun($tahun);

        return collect(range(1, 12))->map(function ($m) use ($rateTahun, $tahun) {
            $row = $rateTahun->firstWhere('bulan', $m);
            return (object) [
                'bulan'   => self::BULAN[$m],
                'avlos'   => $row ? round($row->avlos,      2) : 0,
                'toi'     => $row ? round($row->toi,        2) : 0,
                'bor'     => $row ? round($row->bor,        2) : 0,
                'bto'     => $row ? round($row->bto * 30,   2) : 0,
                'periode' => Carbon::create($tahun, $m, 1)->daysInMonth,
            ];
        });
    }

    // =========================================================
    // RAWAT JALAN — endpoint belum tersedia
    // =========================================================

    /**
     * Kembalikan collection kosong sampai endpoint rajalRingkasan aktif.
     *
     * Ketika endpoint sudah siap di GoogleSheetApiService:
     *   1. Uncomment method getRingkasanRajal() di GoogleSheetApiService
     *   2. Ganti method ini dengan:
     *      return $this->api->getRingkasanRajal($dari, $sampai);
     */
    public function getRingkasanRajal(string $dari, string $sampai): Collection
    {
        return collect(); // ← ganti ketika endpoint aktif
    }

    // =========================================================
    // IGD — endpoint belum tersedia
    // =========================================================

    /**
     * @return array{total: int, pulang: int, rawat_inap: int, meninggal: int, avg_waktu_tunggu: int}
     *
     * Ketika endpoint igdRingkasan aktif, ganti dengan:
     *   return $this->api->getRingkasanIGD($dari, $sampai);
     */
    public function getRingkasanIGD(string $dari, string $sampai): array
    {
        return [  // ← ganti ketika endpoint aktif
            'total'            => 0,
            'pulang'           => 0,
            'rawat_inap'       => 0,
            'meninggal'        => 0,
            'avg_waktu_tunggu' => 0,
        ];
    }

    // =========================================================
    // RAWAT INAP — endpoint belum tersedia
    // =========================================================

    /**
     * @return array{total_masuk: int, total_keluar: int, masih_dirawat: int, total_meninggal: int}
     *
     * Ketika endpoint ranapRingkasan aktif, ganti dengan:
     *   return $this->api->getRingkasanRanap($dari, $sampai);
     */
    public function getRingkasanRanap(string $dari, string $sampai): array
    {
        return [  // ← ganti ketika endpoint aktif
            'total_masuk'     => 0,
            'total_keluar'    => 0,
            'masih_dirawat'   => 0,
            'total_meninggal' => 0,
        ];
    }

    // =========================================================
    // TREN HARIAN — endpoint belum tersedia
    // =========================================================

    /**
     * Ketika endpoint trendHarian aktif, ganti dengan:
     *   return $this->api->getTrendHarian($dari, $sampai);
     */
    public function getTrendHarian(string $dari, string $sampai): Collection
    {
        return collect(); // ← ganti ketika endpoint aktif
    }

    // =========================================================
    // TRIAGE IGD — endpoint belum tersedia
    // =========================================================

    /**
     * Ketika endpoint triageIGD aktif, ganti dengan:
     *   return $this->api->getTriageIGD($dari, $sampai);
     */
    public function getIGDPerTriage(string $dari, string $sampai): Collection
    {
        return collect(); // ← ganti ketika endpoint aktif
    }

    // =========================================================
    // HELPER
    // =========================================================

    /**
     * Daftar nomor bulan yang tercakup dalam rentang tanggal.
     * Contoh: '2024-11-15' s.d. '2025-01-10' → [11, 12, 1]
     */
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