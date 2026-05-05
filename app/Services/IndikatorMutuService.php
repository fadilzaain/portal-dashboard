<?php

namespace App\Services;

use App\Models\CapaianIndikator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class IndikatorMutuService
{
    // ─── Endpoint external API ────────────────────────────────────────────────
    private const URL_PMKP = 'http://192.168.10.8/sikawan-api/public/api/v1/pmkp';
    private const URL_NDR  = 'https://script.google.com/macros/s/AKfycbwiAdTXwqMRslCm7WV64koyzvq2yDZTK3_hFOxLt1PQc4x1ApqD6WAoxq_lNgJkm1Tr/exec';
    // Cache TTL dalam detik (10 menit)
    private const TTL = 600;

    /**
     * Update ini jika sudah ada sumber data yang proper
     */
    private const JENIS_MUTU_MAP = [
        'Kepatuhan Identifikasi Pasien'                                                       => 'nasional',
        'Kepatuhan Penggunaan Formularium Nasional bagi RS Provider BPJS'                     => 'nasional',
        'Kepatuhan Terhadap Clinical Pathway'                                                 => 'nasional',
        'Kepatuhan Upaya Pencegahan Risiko Cedera akibat Pasien Jatuh pada pasien Rawat Inap' => 'nasional',
        'Waktu Lapor Hasil Tes Kritis Laboratorium'                                           => 'nasional',
        'Kepatuhan Cuci Tangan'                                                               => 'nasional',
        'Kepatuhan Pemakaian APD'                                                             => 'nasional',
        'Kecepatan Respon Terhadap Komplain'                                                  => 'nasional',
        'Kepuasan Pasien dan Keluarga'                                                        => 'nasional',
        'Waktu Tunggu Rawat Jalan'                                                            => 'prioritas',
        'Penundaan Operasi Elektif'                                                           => 'prioritas',
        'Kepatuhan Jam Visite Dokter Spesialis'                                               => 'prioritas',
        'Waktu Tanggap Operasi SC Emergency kurang dari 30 menit'                             => 'prioritas',
    ];

    // ─── Fetch dari API ───────────────────────────────────────────────────────

    /**
     * Ambil data PMKP 
     */
    public function fetchPmkp(?int $triwulan = 1, int $tahun = 0): array
    {
        $tahun    = $tahun ?: (int) date('Y');
        $cacheKey = "pmkp_tw{$triwulan}_{$tahun}";

        return Cache::remember($cacheKey, self::TTL, function () use ($triwulan, $tahun) {
            $response = Http::timeout(15)->get(self::URL_PMKP, array_filter([
                'triwulan' => $triwulan,
                'tahun'    => $tahun,
            ]));

            if (!$response->successful()) {
                throw new \RuntimeException("API PMKP tidak dapat diakses (HTTP {$response->status()})");
            }

            $body = $response->json();

            if (!($body['success'] ?? false) || !isset($body['data'])) {
                throw new \RuntimeException('Respons API PMKP tidak valid');
            }

            return collect($body['data'])
                ->filter(fn($row) => !empty(trim(strip_tags($row['indikator'] ?? ''))))
                ->values()
                ->toArray();
        });
    }

    /**
     * Ambil data NDR 
     */
    public function fetchNdr(?int $triwulan = 1, int $tahun = 0): array
    {
        $tahun    = $tahun ?: (int) date('Y');
        $cacheKey = "ndr_gas_{$tahun}";

        return Cache::remember($cacheKey, self::TTL, function () use ($tahun) {
            $response = Http::timeout(20)
                ->withoutVerifying()
                ->withOptions(['allow_redirects' => true])
                ->get(self::URL_NDR, [
                    'aksi'  => 'ndr',
                    'tahun' => $tahun,
                ]);

            if (!$response->successful()) {
                throw new \RuntimeException("API NDR tidak dapat diakses (HTTP {$response->status()})");
            }

            $body = $response->json();

            if (!($body['success'] ?? false) || !isset($body['rows'])) {
                throw new \RuntimeException('Respons API NDR tidak valid: ' . ($body['msg'] ?? 'unknown'));
            }

            return $body['rows'];
        });
    }

    // ─── Format Tabel ─────────────────────────────────────────────────────────

    public function formatTabel(array $pmkpRaw, ?string $jenisMutuFilter = null): array
    {
        $result = [];

        foreach ($pmkpRaw as $row) {
            $namaClean = strip_tags($row['indikator'] ?? '');
            $jenis     = self::JENIS_MUTU_MAP[$namaClean] ?? 'prioritas';

            // Filter jenis_mutu jika dipilih
            if ($jenisMutuFilter && $jenis !== $jenisMutuFilter) {
                continue;
            }

            $targetRaw = trim($row['target'] ?? '');
            $targetNum = $this->parseTarget($targetRaw);
            $isLower   = $this->isLowerBetter($targetRaw);

            $bulanData = $this->buildBulanData($row);

            // Rata-rata dari bulan yang tersedia (dipertahankan dari Service lama)
            $capaianValues = collect($bulanData)
                ->pluck('capaian')
                ->filter(fn($v) => $v !== null);

            $rataCapaian = $capaianValues->count() > 0
                ? round($capaianValues->average(), 2)
                : null;

            // Gunakan nilai triwulan dari API jika tersedia, fallback ke rata-rata
            $triwulanVal = $this->parseFloatSafe($row['triwulan'] ?? null);
            $nilaiAcuan  = $triwulanVal ?? $rataCapaian;
            $status      = $this->getStatusRaw($nilaiAcuan, $targetNum, $isLower);

            $result[] = [
                'nama'         => $namaClean,
                'nama_html'    => $row['indikator'], // dengan tag <i> jika ada
                'jenis_mutu'   => $jenis,
                'label_jenis'  => $jenis === 'nasional' ? 'Nasional' : 'Prioritas',
                'target_raw'   => $targetRaw,
                'target_num'   => $targetNum,
                'is_lower'     => $isLower,
                'bulan_data'   => $bulanData,
                'rata_capaian' => $rataCapaian,
                'triwulan'     => $triwulanVal,
                'status'       => $status, // 'tercapai' | 'belum' | null
            ];
        }

        return $result;
    }

    // ─── Format Grafik Capaian ────────────────────────────────────────────────
    public function formatGrafik(array $tabel, ?int $triwulan = 1): array
    {
        $bulanRange = $this->getBulanRange($triwulan);
        $labels     = collect($bulanRange)->map(fn($b) => $this->namaBulanShort($b))->toArray();

        // Hitung rata-rata capaian semua indikator per bulan
        $avgPerBulan = collect($bulanRange)->map(function ($bulan) use ($tabel) {
            $key  = $this->bulanToApiKey($bulan);
            $vals = collect($tabel)
                ->map(fn($ind) => collect($ind['bulan_data'])->firstWhere('key', $key)['capaian'] ?? null)
                ->filter(fn($v) => $v !== null);
            return $vals->count() > 0 ? round($vals->average(), 2) : null;
        })->toArray();

        // Rata-rata target semua indikator sebagai garis referensi
        $avgTarget = collect($tabel)->pluck('target_num')->filter()->average();
        $avgTarget = $avgTarget ? round($avgTarget, 2) : 80;

        return [
            'labels'   => $labels,
            'datasets' => [
                [
                    'label'                => 'Capaian (%)',
                    'data'                 => $avgPerBulan,
                    'borderColor'          => '#38bdf8',
                    'backgroundColor'      => 'rgba(56,189,248,.08)',
                    'borderWidth'          => 2,
                    'fill'                 => true,
                    'tension'              => 0.4,
                    'pointRadius'          => 5,
                    'pointHoverRadius'     => 7,
                    'pointBackgroundColor' => '#38bdf8',
                    'spanGaps'             => true,
                ],
                [
                    'label'       => 'Target (%)',
                    'data'        => array_fill(0, count($labels), $avgTarget),
                    'borderColor' => '#34d399',
                    'borderDash'  => [6, 4],
                    'borderWidth' => 1.5,
                    'pointRadius' => 0,
                    'fill'        => false,
                ],
            ],
        ];
    }

    // ─── Format Grafik NDR ────────────────────────────────────────────────────
    public function formatNdrGrafik(array $ndrRaw, ?int $triwulan = 1): array
    {
        $bulanRange = $this->getBulanRange($triwulan);
        $labels     = collect($bulanRange)->map(fn($b) => $this->namaBulanShort($b))->toArray();

        $bulanKeys  = collect($bulanRange)->map(fn($b) => $this->bulanToGasKey($b))->toArray();

        $colors = ['#f87171','#38bdf8','#34d399','#f59e0b','#a78bfa','#fb923c','#22d3ee','#e879f9','#4ade80'];

        $totalPerBulan = collect($bulanKeys)->map(function ($bk) use ($ndrRaw) {
            $sumD   = collect($ndrRaw)->sum("{$bk}_d");
            $sumKrs = collect($ndrRaw)->sum("{$bk}_krs");
            return $sumKrs > 0 ? round(($sumD / $sumKrs) * 1000, 2) : 0;
        })->toArray();

        $datasets = [
            [
                'label'                => 'Total RS',
                'data'                 => $totalPerBulan,
                'borderColor'          => '#f87171',
                'backgroundColor'      => 'rgba(248,113,113,.1)',
                'borderWidth'          => 2.5,
                'fill'                 => true,
                'tension'              => 0.4,
                'pointRadius'          => 5,
                'pointHoverRadius'     => 7,
                'pointBackgroundColor' => '#f87171',
                'hidden'               => false,
            ],
            [
                'label'       => 'Target (< 1.5‰)',
                'data'        => array_fill(0, count($labels), 1.5),
                'borderColor' => '#f59e0b',
                'borderDash'  => [6, 4],
                'borderWidth' => 1.5,
                'pointRadius' => 0,
                'fill'        => false,
                'hidden'      => false,
            ],
        ];

        foreach ($ndrRaw as $i => $row) {
            $ruangan = $row['RUANGAN'] ?? "Ruangan " . ($i + 1);
            $color   = $colors[$i % count($colors)];

            $dataPerBulan = collect($bulanKeys)->map(function ($bk) use ($row) {
                $val = $row["{$bk}_ndr"] ?? null;
                // PERUBAHAN: GAS sudah kirim nilai ×1000, jadi bagi 1000 (bukan 100)
                return $val !== null ? round((float) $val / 1000, 4) : null;
            })->toArray();

            $datasets[] = [
                'label'                => $ruangan,
                'data'                 => $dataPerBulan,
                'borderColor'          => $color,
                'backgroundColor'      => 'transparent',
                'borderWidth'          => 1.5,
                'fill'                 => false,
                'tension'              => 0.4,
                'pointRadius'          => 3,
                'pointHoverRadius'     => 5,
                'pointBackgroundColor' => $color,
                'hidden'               => true,
            ];
        }

        return [
            'labels'       => $labels,
            'datasets'     => $datasets,
            'ruangan_list' => collect($ndrRaw)->pluck('RUANGAN')->filter()->values()->toArray(),
        ];
    }
    // ─── Tahun Tersedia ───────────────────────────────────────────────────────
    public function getTahunTersedia(): array
    {
        try {
            $tahunDb = CapaianIndikator::select('tahun')
                ->distinct()
                ->orderByDesc('tahun')
                ->pluck('tahun')
                ->toArray();
        } catch (\Throwable) {
            $tahunDb = [];
        }

        $tahunNow = (int) date('Y');

        if (!in_array($tahunNow, $tahunDb)) {
            array_unshift($tahunDb, $tahunNow);
        }

        // Minimal tampilkan 4 tahun jika DB kosong
        if (count($tahunDb) < 2) {
            $tahunDb = range($tahunNow, $tahunNow - 3);
        }

        return $tahunDb;
    }

    // ─── Private Helpers ─────────────────────────────────────────────────────
    private function buildBulanData(array $row): array
    {
        $fieldMap = [
            'jan' => 1,  'feb' => 2,  'mar' => 3,
            'apr' => 4,  'mei' => 5,  'jun' => 6,
            'jul' => 7,  'ags' => 8,  'sep' => 9,
            'okt' => 10, 'nov' => 11, 'des' => 12,
        ];

        $result = [];
        foreach ($fieldMap as $key => $nomorBulan) {
            if (!array_key_exists($key, $row)) continue;

            $result[] = [
                'key'        => $key,
                'bulan'      => $nomorBulan,
                'nama_bulan' => $this->namaBulanPanjang($nomorBulan),
                'capaian'    => $this->parseFloatSafe($row[$key]),
            ];
        }

        return $result;
    }

    private function getBulanRange(?int $triwulan): array
    {
        if (!$triwulan) return range(1, 12);
        $start = ($triwulan - 1) * 3 + 1;
        return range($start, $start + 2);
    }

    private function bulanToApiKey(int $bulan): string
    {
        $map = [1=>'jan',2=>'feb',3=>'mar',4=>'apr',5=>'mei',6=>'jun',
                7=>'jul',8=>'ags',9=>'sep',10=>'okt',11=>'nov',12=>'des'];
        return $map[$bulan] ?? 'jan';
    }

    private function bulanToGasKey(int $bulan): string
    {
        $map = [
            1  => 'Januari',   2  => 'Februari',  3  => 'Maret',
            4  => 'April',     5  => 'Mei',        6  => 'Juni',
            7  => 'Juli',      8  => 'Agustus',    9  => 'September',
            10 => 'Oktober',   11 => 'November',   12 => 'Desember',
        ];
        return $map[$bulan] ?? 'Januari';
    }

    private function namaBulanShort(int $bulan): string
    {
        $map = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',
                7=>'Jul',8=>'Agt',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
        return $map[$bulan] ?? '-';
    }

    private function namaBulanPanjang(int $bulan): string
    {
        $map = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
                7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
        return $map[$bulan] ?? '-';
    }

    /**
     * Potong nama panjang
     */
    private function truncateNama(string $nama, int $max): string
    {
        return strlen($nama) > $max ? substr($nama, 0, $max) . '…' : $nama;
    }

    private function getStatusRaw(?float $capaian, ?float $target, bool $isLower): ?string
    {
        if ($capaian === null || $target === null) return null;
        return $isLower
            ? ($capaian <= $target ? 'tercapai' : 'belum')
            : ($capaian >= $target ? 'tercapai' : 'belum');
    }

    private function parseTarget(string $raw): ?float
    {
        $clean = preg_replace('/[><=% ]/', '', $raw);
        $clean = str_replace(',', '.', $clean);
        return is_numeric($clean) ? (float) $clean : null;
    }

    private function isLowerBetter(string $targetRaw): bool
    {
        return str_contains($targetRaw, '<');
    }

    private function parseFloatSafe(mixed $val): ?float
    {
        if ($val === null || $val === '') return null;
        $f = (float) str_replace(',', '.', (string) $val);
        return is_nan($f) ? null : round($f, 2);
    }
}