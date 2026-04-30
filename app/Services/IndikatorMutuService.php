<?php

namespace App\Services;

use App\Models\IndikatorMutu;
use App\Models\CapaianIndikator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class IndikatorMutuService
{
    /**
     * Ambil data indikator + capaian per bulan berdasarkan filter
     */
    public function getIndikatorDenganCapaian(array $filters): Collection
    {
        $tahun    = $filters['tahun']     ?? date('Y');
        $triwulan = $filters['triwulan']  ?? null;
        $jenis    = $filters['jenis_mutu'] ?? null;

        $query = IndikatorMutu::aktif()
            ->with(['capaian' => function ($q) use ($tahun, $triwulan) {
                $q->where('tahun', $tahun)
                  ->when($triwulan, fn($q) => $q->where('triwulan', $triwulan))
                  ->orderBy('bulan');
            }]);

        if ($jenis) {
            $query->jenisMutu($jenis);
        }

        return $query->orderBy('kode')->get();
    }

    /**
     * Format data untuk tabel: target, num, denum, capaian per bulan
     */
    public function formatDataTabel(Collection $indikators, ?int $triwulan): array
    {
        $bulanRange = $this->getBulanRange($triwulan);

        return $indikators->map(function (IndikatorMutu $ind) use ($bulanRange) {
            $capaianMap = $ind->capaian->keyBy('bulan');

            $bulanData = collect($bulanRange)->map(function ($bulan) use ($capaianMap) {
                $c = $capaianMap->get($bulan);
                return [
                    'bulan'       => $bulan,
                    'nama_bulan'  => $c?->nama_bulan_short ?? $this->namaBulanShort($bulan),
                    'numerator'   => $c?->numerator ?? null,
                    'denominator' => $c?->denominator ?? null,
                    'capaian'     => $c ? $c->capaian : null,
                ];
            });

            // Rata-rata capaian periode
            $capaianList = $bulanData->whereNotNull('capaian')->pluck('capaian');
            $rataCapaian = $capaianList->count() > 0
                ? round($capaianList->avg(), 2)
                : null;

            $status = $this->getStatus($ind, $rataCapaian);

            return [
                'id'              => $ind->id,
                'kode'            => $ind->kode,
                'nama'            => $ind->nama,
                'jenis_mutu'      => $ind->jenis_mutu,
                'label_jenis'     => $ind->label_jenis_mutu,
                'target'          => $ind->target,
                'is_lower_better' => $ind->is_lower_better,
                'bulan_data'      => $bulanData->values()->toArray(),
                'rata_capaian'    => $rataCapaian,
                'status'          => $status, // 'tercapai' | 'belum' | null
            ];
        })->values()->toArray();
    }

    /**
     * Format data untuk grafik Chart.js
     */
    public function formatDataGrafik(Collection $indikators, ?int $triwulan): array
    {
        $bulanRange  = $this->getBulanRange($triwulan);
        $labelBulan  = collect($bulanRange)->map(fn($b) => $this->namaBulanShort($b))->toArray();

        $colors = [
            '#38bdf8', // sky-400
            '#34d399', // emerald-400
            '#f59e0b', // amber-400
            '#f87171', // red-400
            '#a78bfa', // violet-400
            '#fb923c', // orange-400
        ];

        $datasets = $indikators->values()->map(function (IndikatorMutu $ind, $idx) use ($bulanRange, $colors) {
            $capaianMap = $ind->capaian->keyBy('bulan');
            $data = collect($bulanRange)->map(function ($bulan) use ($capaianMap) {
                $c = $capaianMap->get($bulan);
                return $c ? $c->capaian : null;
            })->toArray();

            $color = $colors[$idx % count($colors)];

            return [
                'label'                => $ind->kode . ' - ' . $this->truncateNama($ind->nama, 35),
                'data'                 => $data,
                'borderColor'          => $color,
                'backgroundColor'      => $color . '33',
                'pointBackgroundColor' => $color,
                'tension'              => 0.3,
                'fill'                 => true,
                'spanGaps'             => true,
                'target'               => $ind->target,
            ];
        })->toArray();

        return [
            'labels'   => $labelBulan,
            'datasets' => $datasets,
        ];
    }

    /**
     * Ambil daftar tahun yang tersedia
     */
    public function getTahunTersedia(): array
    {
        $tahunDb = CapaianIndikator::select('tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun')
            ->toArray();

        // Pastikan tahun berjalan selalu ada
        $tahunNow = (int) date('Y');
        if (!in_array($tahunNow, $tahunDb)) {
            array_unshift($tahunDb, $tahunNow);
        }

        return $tahunDb;
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function getBulanRange(?int $triwulan): array
    {
        if (!$triwulan) {
            return range(1, 12);
        }
        $start = ($triwulan - 1) * 3 + 1;
        return range($start, $start + 2);
    }

    private function namaBulanShort(int $bulan): string
    {
        $map = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',
                7=>'Jul',8=>'Agt',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
        return $map[$bulan] ?? '-';
    }

    private function truncateNama(string $nama, int $max): string
    {
        return strlen($nama) > $max ? substr($nama, 0, $max) . '…' : $nama;
    }

    private function getStatus(IndikatorMutu $ind, ?float $capaian): ?string
    {
        if ($capaian === null) return null;

        if ($ind->is_lower_better) {
            return $capaian <= $ind->target ? 'tercapai' : 'belum';
        }
        return $capaian >= $ind->target ? 'tercapai' : 'belum';
    }
}