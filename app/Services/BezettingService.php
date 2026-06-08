<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BezettingService
{
    private string $url;
    private int $timeout;
    private int $cacheTtl;

    // Mapping kata kunci jabatan ke kategori
    private array $categoryMap = [
        'Dokter'  => ['dokter', 'dr.'],
        'Perawat' => ['perawat', 'bidan', 'penata anest', 'asisten penata'],
        'Farmasi' => ['apoteker', 'asisten apoteker'],
        'Medis Lainnya' => [
            'teknisi', 'nutrisionis', 'fisioterapi', 'analis', 'radiografer',
            'perekam', 'sanitarian', 'terapis', 'okupasi', 'ortosis',
            'refraksionis', 'fisikawan', 'psikologi',
        ],
    ];

    public function __construct()
    {
        $this->url      = env('API_BEZETTING_URL', '');
        $this->timeout  = (int) env('API_BEZETTING_TIMEOUT', 15);
        $this->cacheTtl = (int) env('API_BEZETTING_CACHE_TTL', 3600); 
    }

    //Ambil data bezetting
    public function getData(): Collection
    {
        if (empty($this->url)) {
            Log::warning('BezettingService: API_BEZETTING_URL belum diset di .env');
            return collect();
        }

        return Cache::remember('bezetting_data', $this->cacheTtl, function () {
            return $this->fetchAndParse();
        });
    }

    //Fetch dari API - parse ke Collection of objects
    private function fetchAndParse(): Collection
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->url);

            if (!$response->successful()) {
                Log::warning('BezettingService: response tidak sukses', [
                    'status' => $response->status(),
                ]);
                return collect();
            }

            $raw = $response->json();

            if (!is_array($raw)) {
                Log::warning('BezettingService: response bukan array');
                return collect();
            }

            return collect($raw)->map(function ($row) {
                // Handle variasi nama field (spasi, case)
                $delta = (int) ($row['KURANG/ LEBIH'] ?? $row['KURANG/LEBIH'] ?? $row['KURANG_LEBIH'] ?? 0);

                return (object) [
                    'jabatan'    => trim($row['JABATAN'] ?? '-'),
                    'kebutuhan'  => (int) ($row['KEBUTUHAN'] ?? 0),
                    'tersedia'   => (int) ($row['JUMLAH PEGAWAI'] ?? $row['JUMLAH_PEGAWAI'] ?? 0),
                    'delta'      => $delta,
                    'kekurangan' => $delta < 0 ? abs($delta) : 0,
                    'kategori'   => $this->resolveKategori($row['JABATAN'] ?? ''),
                    'pct'        => $this->hitungPct(
                        (int) ($row['JUMLAH PEGAWAI'] ?? 0),
                        (int) ($row['KEBUTUHAN'] ?? 0)
                    ),
                ];
            })->filter(fn($r) => !empty($r->jabatan) && $r->jabatan !== '-');

        } catch (\Exception $e) {
            Log::error('BezettingService: gagal fetch', ['message' => $e->getMessage()]);
            return collect();
        }
    }

     //=================================
     //Ringkasan untuk summary cards
     //================================
    public function getSummary(Collection $data): array
    {
        $kurang = $data->filter(fn($r) => $r->delta < 0);
        $cukup  = $data->filter(fn($r) => $r->delta === 0);
        $lebih  = $data->filter(fn($r) => $r->delta > 0);

        return [
            'total'          => $data->count(),
            'totalKurang'    => $kurang->count(),
            'totalOrangKurang' => $kurang->sum('kekurangan'),
            'totalLebih'     => $lebih->count(),
            'totalCukup'     => $cukup->count(),
            'kurang'         => $kurang->sortBy('delta')->values(),
            'cukup'          => $cukup->sortBy('jabatan')->values(),
            'lebih'          => $lebih->sortByDesc('delta')->values(),
        ];
    }

     //===============================================   
     //Data monitoring hari ini untuk section Tahap 1
     //===============================================
    public function getMonitoring(Collection $bezData, array $shiftSummary, int $totalPegawai): array
    {
        // ── Ketersediaan shift ──────────────────────────────
        $pagi  = $shiftSummary['PAGI']['total']  ?? 0;
        $siang = $shiftSummary['SIANG']['total'] ?? 0;
        $malam = $shiftSummary['MALAM']['total'] ?? 0;
        $totalHadir = $pagi + $siang + $malam;

        $pctHadir = $totalPegawai > 0
            ? round($totalHadir / $totalPegawai * 100, 1)
            : 0;

        // Shift aktif berdasarkan jam sekarang
        $jam = (int) now()->format('H');
        if ($jam >= 7 && $jam < 14) {
            $shiftAktifNama  = 'PAGI';
            $shiftAktifTotal = $pagi;
        } elseif ($jam >= 14 && $jam < 21) {
            $shiftAktifNama  = 'SIANG';
            $shiftAktifTotal = $siang;
        } else {
            $shiftAktifNama  = 'MALAM';
            $shiftAktifTotal = $malam;
        }

        // ── Rasio global (semua jabatan, skip kebutuhan = 0) ──
        $validData     = $bezData->filter(fn($r) => $r->kebutuhan > 0);
        $totalKebutuhan = $validData->sum('kebutuhan');
        $totalTersedia  = $validData->sum('tersedia');
        $rasioGlobal    = $totalKebutuhan > 0
            ? (int) min(round($totalTersedia / $totalKebutuhan * 100), 100)
            : 0;

        // ── Rasio per kategori ──────────────────────────────
        $kategoriUrut = ['Dokter', 'Perawat', 'Farmasi', 'Medis Lainnya', 'Lainnya'];
        $rasioKategori = [];

        foreach ($kategoriUrut as $kat) {
            $rows = $validData->filter(fn($r) => $r->kategori === $kat);
            if ($rows->isEmpty()) continue;

            $k = $rows->sum('kebutuhan');
            $t = $rows->sum('tersedia');
            $p = $k > 0 ? (int) min(round($t / $k * 100), 100) : 100;

            $rasioKategori[] = [
                'nama'      => $kat,
                'kebutuhan' => $k,
                'tersedia'  => $t,
                'pct'       => $p,
            ];
        }

        // ── Jabatan kritis (rasio < 70%, kebutuhan >= 2) ──
        $jabatanKritis = $bezData
            ->filter(fn($r) => $r->kebutuhan >= 2 && $r->pct < 70)
            ->sortBy('pct')
            ->take(9) // maks 9 item (3 kolom x 3 baris)
            ->map(fn($r) => [
                'jabatan'   => $r->jabatan,
                'kategori'  => $r->kategori,
                'kebutuhan' => $r->kebutuhan,
                'tersedia'  => $r->tersedia,
                'pct'       => $r->pct,
            ])
            ->values()
            ->toArray();

        return [
            'totalHadir'      => $totalHadir,
            'pctHadir'        => $pctHadir,
            'shiftAktifNama'  => $shiftAktifNama,
            'shiftAktifTotal' => $shiftAktifTotal,
            'rasioGlobal'     => $rasioGlobal,
            'rasioKategori'   => $rasioKategori,
            'jabatanKritis'   => $jabatanKritis,
        ];
    }

     //===========================================
     //Flush cache manual (misal dipanggil dari artisan command atau admin action)
     //===========================================
    public function flushCache(): void
    {
        Cache::forget('bezetting_data');
    }

     //===========================================
     //Tentukan kategori berdasarkan nama jabatan
     //===========================================
    private function resolveKategori(string $jabatan): string
    {
        $lower = strtolower($jabatan);

        foreach ($this->categoryMap as $kategori => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($lower, $kw)) {
                    return $kategori;
                }
            }
        }

        return 'Lainnya';
    }

    //=====================================================
    //Hitung persentase tersedia vs kebutuhan (0–100, max 100%)
    //=====================================================
    private function hitungPct(int $tersedia, int $kebutuhan): int
    {
        if ($kebutuhan <= 0) return 100;
        return (int) min(round($tersedia / $kebutuhan * 100), 100);
    }
}