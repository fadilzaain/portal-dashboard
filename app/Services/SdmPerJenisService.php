<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Ambil & olah data dari API "monitoring SDM per jenis" (per unit x per jabatan).
 * Response API berbentuk: { "success": true, "data": { "Poli Bedah": [ {...}, {...} ], "Poli Syaraf": [...] } }
 * Dipakai buat section "Unit & Jabatan Perlu Perhatian" (ringkasan prioritas) dan
 * "Detail Rasio Kecukupan SDM per Unit" (breakdown per unit x per jabatan, sedetail SI-OSMAR).
 */
class SdmPerJenisService
{
    private string $url;
    private int $timeout;
    private int $cacheTtl;
    private bool $verifySsl;

    public function __construct()
    {
        $this->url       = env('API_SDM_PERJENIS_URL', '');
        $this->timeout   = (int) env('API_SDM_PERJENIS_TIMEOUT', 15);
        $this->cacheTtl  = (int) env('API_SDM_PERJENIS_CACHE_TTL', 3600);
        // Default true (aman/verified). Set false di .env kalau server SI KAWAN
        // pakai sertifikat self-signed/internal yang gak lolos verifikasi cURL error 60.
        $this->verifySsl = (bool) env('API_SDM_PERJENIS_VERIFY_SSL', true);
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
            $response = Http::timeout($this->timeout)
                ->withOptions(['verify' => $this->verifySsl])
                ->get($this->url);

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
     * Ringkasan per unit buat section "Detail Rasio Kecukupan SDM per Unit"
     * (di bawah, expand per unit buat lihat breakdown jabatannya).
     * `slug` dipakai sebagai anchor id <div id="unit-{slug}"> biar bisa di-scroll-to
     * dari card "Unit Perlu Perhatian" di atas.
     *
     * @return array<int, array{
     *   unit: string, slug: string, kebutuhan: int, tersedia: int, pct: int,
     *   status: string, kurangCount: int, detail: array
     * }>
     */
    public function getRingkasanPerUnit(): array
    {
        return $this->getData()
            ->groupBy('unit')
            ->map(function (Collection $rows, $unitKey) {
                $unit      = (string) $unitKey; // array key numerik di-auto-cast PHP jadi int, dibalikin ke string
                $kebutuhan = $rows->sum('kebutuhan');
                $tersedia  = $rows->sum('jumlah');
                $pct       = $kebutuhan > 0 ? (int) min(round($tersedia / $kebutuhan * 100), 100) : 100;

                return [
                    'unit'        => $unit,
                    'slug'        => Str::slug($unit),
                    'kebutuhan'   => $kebutuhan,
                    'tersedia'    => $tersedia,
                    'pct'         => $pct,
                    'status'      => $pct >= 80 ? 'aman' : ($pct >= 60 ? 'waspada' : 'kritis'),
                    'kurangCount' => $rows->filter(fn($r) => $r->keterangan === 'KURANG')->count(),
                    'detail'      => $rows
                        ->map(fn($r) => (array) $r)
                        ->sortBy('jabatan')
                        ->values()
                        ->toArray(),
                ];
            })
            ->sortBy('unit')
            ->values()
            ->toArray();
    }

    /**
     * Unit-unit dengan total kekurangan formasi terbanyak (dijumlah lintas semua
     * jabatan di unit tsb), buat card "Unit Perlu Perhatian" (chart batang).
     * `slug` dipakai buat scroll-to lewat sdmScrollToUnit() di sdm.blade.php.
     *
     * @return array<int, array{unit: string, slug: string, kekurangan: int, jabatanKurangCount: int}>
     */
    public function getPrioritasUnit(int $limit = 6): array
    {
        return $this->getData()
            ->groupBy('unit')
            ->map(fn(Collection $rows, $unitKey) => [
                'unit'               => (string) $unitKey,
                'slug'               => Str::slug((string) $unitKey),
                'kekurangan'         => $rows->sum('kekurangan'),
                'jabatanKurangCount' => $rows->where('keterangan', 'KURANG')->count(),
            ])
            ->filter(fn($u) => $u['kekurangan'] > 0)
            ->sortByDesc('kekurangan')
            ->take($limit)
            ->values()
            ->toArray();
    }

    /**
     * Jabatan-jabatan paling kritis lintas semua unit (kekurangan terbanyak),
     * buat card "Jabatan Perlu Perhatian" (ranked list). `slug` unit-nya sama
     * persis dengan getRingkasanPerUnit(), jadi klik item bisa langsung scroll
     * ke card unit yang bersangkutan.
     *
     * @return array<int, array{
     *   unit: string, slug: string, jabatan: string, kategori: string,
     *   kekurangan: int, kebutuhan: int, jumlah: int
     * }>
     */
    public function getPrioritasJabatan(int $limit = 8): array
    {
        return $this->getData()
            ->filter(fn($r) => $r->kekurangan > 0)
            ->sortByDesc('kekurangan')
            ->take($limit)
            ->map(fn($r) => [
                'unit'       => $r->unit,
                'slug'       => Str::slug($r->unit),
                'jabatan'    => $r->jabatan,
                'kategori'   => $r->kategori,
                'kekurangan' => $r->kekurangan,
                'kebutuhan'  => $r->kebutuhan,
                'jumlah'     => $r->jumlah,
            ])
            ->values()
            ->toArray();
    }

    // Flush cache manual (misal dipanggil dari artisan command atau admin action)
    public function flushCache(): void
    {
        Cache::forget('sdm_perjenis_data');
    }
}