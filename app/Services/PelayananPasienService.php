<?php

namespace App\Services;

use App\Models\PelayananPasien;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PelayananPasienService
{
    private const BULAN = [
        1  => 'Jan', 2  => 'Feb', 3  => 'Mar', 4  => 'Apr',
        5  => 'Mei', 6  => 'Jun', 7  => 'Jul', 8  => 'Ags',
        9  => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    public const STANDAR = [
        'bor'       => ['min' => 60,  'max' => 85],
        'los'       => ['min' => 3,   'max' => 12],
        'toi'       => ['min' => 1,   'max' => 3],
        'bto_bulan' => ['min' => 40,  'max' => 50],
    ];

    // =========================================================
    // INDIKATOR MUTU (BOR, LOS, TOI, BTO)
    // =========================================================

    public function getIndikatorMutu(int $tahun, string $dari, string $sampai): array
    {
        $bulanDalam = $this->getBulanDalamRentang($dari, $sampai);

        $data = PelayananPasien::getByRentang($tahun, $bulanDalam)
            ->filter(fn($r) => ($r->bor ?? 0) > 0);

        return $data->isEmpty()
            ? $this->emptyIndikator()
            : $this->hitungRataIndikator($data);
    }

    public function getIndikatorMutuYTD(int $tahun): array
    {
        $data = PelayananPasien::getYTD($tahun, Carbon::now()->month);

        return $data->isEmpty()
            ? $this->emptyIndikator()
            : $this->hitungRataIndikator($data);
    }

    public function getIndikatorMutuBeranda(): array
    {
        [$tahun, $sampaibulan] = $this->tahunDanBulanLalu();

        $data = DB::connection('dashi')
            ->table('borlosttoiall_thn')
            ->where('tahun', $tahun)
            ->whereBetween('bulan', [1, $sampaibulan])
            ->where('bor', '>', 0)
            ->orderBy('bulan')
            ->get();

        $indikator = $data->isEmpty()
            ? $this->emptyIndikator()
            : $this->hitungRataIndikator($data);

        return array_merge($indikator, [
            'tahun'       => $tahun,
            'sampaibulan' => $sampaibulan,
        ]);
    }

    // =========================================================
    // CHART DATA
    // =========================================================

    public function getChartBORBulanan(int $tahun): Collection
    {
        $rows = PelayananPasien::getByTahun($tahun)->keyBy('bulan');

        return collect(range(1, 12))->map(fn($m) => (object) [
            'bulan' => self::BULAN[$m],
            'bor'   => round($rows->get($m)?->bor ?? 0, 2),
        ]);
    }

    public function getChartAvlosBulanan(int $tahun): Collection
    {
        $rows = PelayananPasien::getByTahun($tahun)->keyBy('bulan');

        return collect(range(1, 12))->map(fn($m) => (object) [
            'bulan'   => self::BULAN[$m],
            'avlos'   => round($rows->get($m)?->los ?? 0, 2),
            'toi'     => round($rows->get($m)?->toi ?? 0, 2),
            'bor'     => round($rows->get($m)?->bor ?? 0, 2),
            'bto'     => round($rows->get($m)?->bto ?? 0, 2),
            'periode' => Carbon::create($tahun, $m, 1)->daysInMonth,
        ]);
    }

    // =========================================================
    // TREN KUNJUNGAN — kunjungan_rekap (dashi)
    // =========================================================

    public function getTrendKunjungan(int $tahun): Collection
    {
        $rows = PelayananPasien::getKunjunganRekap($tahun)->keyBy('bulan');

        return collect(range(1, 12))->map(fn($m) => (object) [
            'bulan'         => self::BULAN[$m],
            'jml_kunjungan' => (int)   ($rows->get($m)?->jml_kunjungan ?? 0),
            'presentase'    => (float) ($rows->get($m)?->presentase     ?? 0),
            'jml_hari'      => (int)   ($rows->get($m)?->jml_hari       ?? 0),
            'jml_rata_rata' => (int)   ($rows->get($m)?->jml_rata_rata  ?? 0),
        ]);
    }

    // =========================================================
    // LIVE — IGD & Kunjungan hari ini
    // =========================================================

    public function getKunjunganHariIni(): array
    {
        return PelayananPasien::getKunjunganHariIni();
    }

    public function getMonitoringIGD(): array
    {
        return PelayananPasien::getMonitoringIGD();
    }

    // =========================================================
    // STUB — endpoint belum tersedia
    // =========================================================

    /** @stub Rawat jalan per poli */
    public function getRingkasanRajal(string $dari, string $sampai): Collection
    {
        return collect();
    }

    /** @stub Ringkasan IGD */
    public function getRingkasanIGD(string $dari, string $sampai): array
    {
        return ['total' => 0, 'pulang' => 0, 'rawat_inap' => 0, 'meninggal' => 0, 'avg_waktu_tunggu' => 0];
    }

    /** @stub Ringkasan rawat inap */
    public function getRingkasanRanap(string $dari, string $sampai): array
    {
        return ['total_masuk' => 0, 'total_keluar' => 0, 'masih_dirawat' => 0, 'total_meninggal' => 0];
    }

    /** @stub IGD per kategori triage */
    public function getIGDPerTriage(string $dari, string $sampai): Collection
    {
        return collect();
    }

    // =========================================================
    // HELPER PRIVATE
    // =========================================================

    private function hitungRataIndikator($data): array
    {
        return [
            'bor' => round($data->avg('bor'), 2),
            'los' => round($data->avg('los'), 2),
            'toi' => round($data->avg('toi'), 2),
            'bto' => round($data->avg('bto'), 2),
        ];
    }

    private function emptyIndikator(): array
    {
        return ['bor' => 0.0, 'los' => 0.0, 'toi' => 0.0, 'bto' => 0.0];
    }

    private function tahunDanBulanLalu(): array
    {
        $now = Carbon::now();
        return $now->month === 1
            ? [$now->year - 1, 12]
            : [$now->year, $now->month - 1];
    }

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