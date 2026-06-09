<?php

namespace App\Services;

use App\Models\PelayananPasien;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PelayananPasienService
{
    private const BULAN = [
        1  => 'Jan', 2  => 'Feb', 3  => 'Mar', 4  => 'Apr',
        5  => 'Mei', 6  => 'Jun', 7  => 'Jul', 8  => 'Ags',
        9  => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    // =========================================================
    // INDIKATOR MUTU 
    // =========================================================
    public function getIndikatorMutu(int $tahun, string $dari, string $sampai): array
    {
        $bulanDalam = $this->getBulanDalamRentang($dari, $sampai);

        $data = PelayananPasien::getByRentang($tahun, $bulanDalam)
            ->filter(fn($r) => ($r->bor ?? 0) > 0);

        if ($data->isEmpty()) {
            return $this->emptyIndikator();
        }

        return [
            'bor' => round($data->avg('bor'), 2),
            'los' => round($data->avg('los'), 2),
            'toi' => round($data->avg('toi'), 2),
            'bto' => round($data->avg('bto'), 2),
        ];
    }

    // =========================================================
    // INDIKATOR MUTU YTD — rata-rata Jan s/d bulan sekarang
    // =========================================================
    public function getIndikatorMutuYTD(int $tahun): array
    {
        $sampaibulan = Carbon::now()->month;

        $data = PelayananPasien::getYTD($tahun, $sampaibulan);

        if ($data->isEmpty()) {
            return $this->emptyIndikator();
        }

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

    // =========================================================
    // PELAYANAN PASIEN
    // =========================================================vice.php
    public function getKunjunganHariIni(): array
    {
        return \App\Models\PelayananPasien::getKunjunganHariIni();
    }

    // =========================================================
    // CHART BOR BULANAN 
    // =========================================================
    public function getChartBORBulanan(int $tahun): Collection
    {
        $rows = PelayananPasien::getByTahun($tahun)->keyBy('bulan');

        return collect(range(1, 12))->map(function ($m) use ($rows) {
            $row = $rows->get($m);
            return (object) [
                'bulan' => self::BULAN[$m],
                'bor'   => $row ? round($row->bor, 2) : 0,
            ];
        });
    }

    // =========================================================
    // INDIKATOR MUTU UNTUK CARD BERANDA
    // Rata-rata Jan s/d (bulan sekarang - 1), tahun terbaru yang ada datanya
    // =========================================================
    public function getIndikatorMutuBeranda(): array
    {
        $tahunSekarang  = Carbon::now()->year;
        $bulanSekarang  = Carbon::now()->month;

        // Bulan berjalan dikecualikan
        // Kalau sekarang Januari (bulan 1), tidak ada bulan sebelumnya di tahun ini,
        // maka ambil tahun lalu bulan 1–12
        if ($bulanSekarang === 1) {
            $tahun       = $tahunSekarang - 1;
            $sampaibulan = 12;
        } else {
            $tahun       = $tahunSekarang;
            $sampaibulan = $bulanSekarang - 1;
        }

        $data = DB::connection('dashi')
            ->table('borlosttoiall_thn')
            ->where('tahun', $tahun)
            ->whereBetween('bulan', [1, $sampaibulan])
            ->where('bor', '>', 0)
            ->orderBy('bulan')
            ->get();

        if ($data->isEmpty()) {
            return [
                'bor'        => 0.0,
                'los'        => 0.0,
                'toi'        => 0.0,
                'bto'        => 0.0,
                'tahun'      => $tahun,
                'sampaibulan'=> $sampaibulan,
            ];
        }

        return [
            'bor'        => round($data->avg('bor'),  2),
            'los'        => round($data->avg('los'),  2),
            'toi'        => round($data->avg('toi'),  2),
            'bto'        => round($data->avg('bto'),  2),
            'tahun'      => $tahun,
            'sampaibulan'=> $sampaibulan,
        ];
    }

    // =========================================================
    // CHART BARBER-JOHNSON 
    // =========================================================
    public function getChartAvlosBulanan(int $tahun): Collection
    {
        $rows = PelayananPasien::getByTahun($tahun)->keyBy('bulan');

        return collect(range(1, 12))->map(function ($m) use ($rows, $tahun) {
            $row = $rows->get($m);
            return (object) [
                'bulan'   => self::BULAN[$m],
                'avlos'   => $row ? round($row->los, 2) : 0,
                'toi'     => $row ? round($row->toi, 2) : 0,
                'bor'     => $row ? round($row->bor, 2) : 0,
                'bto'     => $row ? round($row->bto, 2) : 0,
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