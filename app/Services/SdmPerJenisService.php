<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Ambil & olah data dari API "monitoring SDM per jenis" (per unit x per jabatan).
 * Response API berbentuk: { "success": true, "data": { "Poli Bedah": [ {...}, {...} ], "Poli Syaraf": [...] } }
 * Dipakai buat section "Rasio Kecukupan SDM per Kategori" biar sedetail SI-OSMAR (breakdown per unit).
 */
class SdmPerJenisService
{
    private string $url;
    private int $timeout;
    private int $cacheTtl;

    public function __construct()
    {
        $this->url      = env('API_SDM_PERJENIS_URL', '');
        $this->timeout  = (int) env('API_SDM_PERJENIS_TIMEOUT', 15);
        $this->cacheTtl = (int) env('API_SDM_PERJENIS_CACHE_TTL', 3600);
    }

    // Data mentah sudah diflat jadi 1 baris per (unit, jabatan), sudah di-cache
    public function getData(): Collection
    {
        if (empty($this->url)) {
            Log::warning('SdmPerJenisService: API_SDM_PERJENIS_URL belum diset di .env');
            return collect();
        }

        return Cache::remember('sdm_perjenis_data', $this->cacheTtl, function () {
            return $this->fetchAndParse();
        });
    }

    private function fetchAndParse(): Collection
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->url);

            if (!$response->successful()) {
                Log::warning('SdmPerJenisService: response tidak sukses', [
                    'status' => $response->status(),
                    'url'    => $this->url,
                ]);
                return collect();
            }

            $raw = $response->json('data');

            if (!is_array($raw)) {
                Log::warning('SdmPerJenisService: field "data" tidak ditemukan/bukan array');
                return collect();
            }

            // $raw = ['Poli Bedah' => [ [...], [...] ], 'Poli Syaraf' => [ [...] ], ... ]
            // Diflat jadi 1 collection baris per (unit, jabatan) + kategori hasil mapping.
            return collect($raw)
                ->flatMap(function ($rows, $unitKey) {
                    if (!is_array($rows)) {
                        return collect();
                    }

                    return collect($rows)->map(function ($row) use ($unitKey) {
                        $jabatan = trim((string) ($row['jabatan'] ?? '-'));

                        return (object) [
                            'unit'        => trim((string) ($row['unit'] ?? $unitKey)),
                            'jabatan'     => $jabatan,
                            'kualifikasi' => trim((string) ($row['kualifikasi'] ?? '-')),
                            'pns'         => (int) ($row['pns'] ?? 0),
                            'pppk'        => (int) ($row['pppk'] ?? 0),
                            'pppk_pw'     => (int) ($row['pppk_pw'] ?? 0),
                            'non_asn'     => (int) ($row['non_asn'] ?? 0),
                            'jumlah'      => (int) ($row['jumlah'] ?? 0),
                            'kebutuhan'   => (int) ($row['kebutuhan'] ?? 0),
                            'keterangan'  => strtoupper(trim((string) ($row['keterangan'] ?? '-'))),
                            'kekurangan'  => (int) ($row['kekurangan'] ?? 0),
                            'kategori'    => JabatanKategori::resolve($jabatan),
                        ];
                    });
                })
                ->filter(fn($r) => !empty($r->jabatan) && $r->jabatan !== '-')
                ->values();

        } catch (\Exception $e) {
            Log::error('SdmPerJenisService: gagal fetch', ['message' => $e->getMessage()]);
            return collect();
        }
    }

    /**
     * Ringkasan per kategori besar buat card "Rasio Kecukupan SDM per Kategori",
     * lengkap dengan detail per unit x per jabatan buat expand di UI.
     *
     * @return array<int, array{
     *   kategori: string, kebutuhan: int, tersedia: int, pct: int, status: string,
     *   unitCount: int, kurangCount: int, detail: array
     * }>
     */
    public function getRingkasanKategori(): array
    {
        $data      = $this->getData();
        $ringkasan = [];

        foreach (JabatanKategori::URUTAN as $kategori) {
            $rows = $data->filter(fn($r) => $r->kategori === $kategori)->values();
            if ($rows->isEmpty()) {
                continue;
            }

            $kebutuhan = $rows->sum('kebutuhan');
            $tersedia  = $rows->sum('jumlah');
            $pct       = $kebutuhan > 0 ? (int) min(round($tersedia / $kebutuhan * 100), 100) : 100;

            $ringkasan[] = [
                'kategori'    => $kategori,
                'kebutuhan'   => $kebutuhan,
                'tersedia'    => $tersedia,
                'pct'         => $pct,
                'status'      => $pct >= 80 ? 'aman' : ($pct >= 60 ? 'waspada' : 'kritis'),
                'unitCount'   => $rows->pluck('unit')->unique()->count(),
                'kurangCount' => $rows->filter(fn($r) => $r->keterangan === 'KURANG')->count(),
                'detail'      => $rows
                    ->map(fn($r) => (array) $r)
                    ->sortBy(fn($r) => $r['unit'] . '|' . $r['jabatan'])
                    ->values()
                    ->toArray(),
            ];
        }

        return $ringkasan;
    }

    // Flush cache manual (misal dipanggil dari artisan command atau admin action)
    public function flushCache(): void
    {
        Cache::forget('sdm_perjenis_data');
    }
}