<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GoogleSheetApiService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected int    $cacheTtl;

    public function __construct()
    {
        $this->baseUrl  = config('services.googlesheet.url');
        $this->apiKey   = config('services.googlesheet.key');
        $this->cacheTtl = (int) config('services.googlesheet.cache_ttl', 300); // 5 menit default
    }

    // ─── Core HTTP ─────────────────────────────────────────────────────────────

    /**
     * Hit API Google Sheet dengan parameter aksi + extra params.
     * Response di-cache per kombinasi parameter.
     */
    public function fetch(string $aksi, array $params = []): array
    {
        $params = array_merge(['aksi' => $aksi], $params);
        $cacheKey = 'gsapi_' . md5($aksi . serialize($params));

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($params) {
            $response = Http::timeout(15)
                ->retry(2, 500)
                ->get($this->baseUrl, $params);

            if (! $response->successful()) {
                Log::error('GoogleSheetApi error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                    'params' => $params,
                ]);
                throw new \RuntimeException('Gagal mengambil data dari Google Sheet API. Status: ' . $response->status());
            }

            $json = $response->json();

            if (! ($json['success'] ?? false)) {
                throw new \RuntimeException('API mengembalikan success=false: ' . ($json['msg'] ?? 'Unknown error'));
            }

            return $json;
        });
    }

    /** Paksa refresh cache untuk aksi tertentu */
    public function forgetCache(string $aksi, array $params = []): void
    {
        $params = array_merge(['aksi' => $aksi], $params);
        Cache::forget('gsapi_' . md5($aksi . serialize($params)));
    }

    // ─── Endpoint: rateTahun ───────────────────────────────────────────────────

    /**
     * GET ?aksi=rateTahun&tahun=YYYY
     * Response rows: [{ID, TAHUN, BULAN, BOR, AVLOST, TOI, BTO}, ...]
     * Hanya kembalikan bulan dengan data (BOR > 0)
     */
    public function getRateTahun(int $tahun): Collection
    {
        $baseUrl  = config('services.bor_api.url');
        $cacheKey = "bor_rate_{$tahun}";

        return Cache::remember($cacheKey, now()->addHours(1), function () use ($tahun, $baseUrl) {
            return collect(range(1, 12))->map(function ($bulan) use ($tahun, $baseUrl) {
                $start = \Carbon\Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
                $stop  = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');
                $url   = "{$baseUrl}/getborlostoi/{$bulan}/{$start}/{$stop}";

                try {
                    $response = Http::timeout(10)->get($url);
                    $json     = $response->json();

                    if (empty($json['succes']) || empty($json['rows'])) {
                        return null;
                    }

                    $row = $json['rows'][0];

                    return (object) [
                        'bulan' => $bulan,
                        'bor'   => (float) ($row['bor']   ?? 0),
                        'avlos' => (float) ($row['avlos']  ?? 0),
                        'toi'   => (float) ($row['toi']    ?? 0),
                        'bto'   => (float) ($row['bto']    ?? 0),
                    ];

                } catch (\Exception $e) {
                    Log::warning("BOR API gagal bulan {$bulan}/{$tahun}: " . $e->getMessage());
                    return null;
                }
            })->filter();
        });
    }

    // ─── Endpoint: Rajal ───────────────────────────────────────────────────────
    // TODO: Aktifkan setelah endpoint tersedia

    /*
    public function getRingkasanRajal(string $dari, string $sampai): \Illuminate\Support\Collection
    {
        $json = $this->fetch('rajalRingkasan', ['dari' => $dari, 'sampai' => $sampai]);

        return collect($json['rows'] ?? [])
            ->map(fn($row) => (object) [
                'nama_poli'        => $row['NAMA_POLI'],
                'total_kunjungan'  => (int) $row['TOTAL_KUNJUNGAN'],
                'pasien_baru'      => (int) $row['PASIEN_BARU'],
                'pasien_lama'      => (int) $row['PASIEN_LAMA'],
                'bpjs'             => (int) $row['BPJS'],
                'umum'             => (int) $row['UMUM'],
            ]);
    }
    */

    // ─── Endpoint: IGD ─────────────────────────────────────────────────────────
    // TODO: Aktifkan setelah endpoint tersedia

    /*
    public function getRingkasanIGD(string $dari, string $sampai): array
    {
        $json = $this->fetch('igdRingkasan', ['dari' => $dari, 'sampai' => $sampai]);
        $row  = $json['rows'][0] ?? [];

        return [
            'total'            => (int)   ($row['TOTAL']            ?? 0),
            'pulang'           => (int)   ($row['PULANG']           ?? 0),
            'rawat_inap'       => (int)   ($row['RAWAT_INAP']       ?? 0),
            'meninggal'        => (int)   ($row['MENINGGAL']        ?? 0),
            'avg_waktu_tunggu' => (int) round($row['AVG_TUNGGU']    ?? 0),
        ];
    }
    */

    // ─── Endpoint: Ranap ───────────────────────────────────────────────────────
    // TODO: Aktifkan setelah endpoint tersedia

    /*
    public function getRingkasanRanap(string $dari, string $sampai): array
    {
        $json = $this->fetch('ranapRingkasan', ['dari' => $dari, 'sampai' => $sampai]);
        $row  = $json['rows'][0] ?? [];

        return [
            'total_masuk'     => (int) ($row['TOTAL_MASUK']     ?? 0),
            'total_keluar'    => (int) ($row['TOTAL_KELUAR']    ?? 0),
            'total_meninggal' => (int) ($row['TOTAL_MENINGGAL'] ?? 0),
            'masih_dirawat'   => (int) ($row['MASIH_DIRAWAT']   ?? 0),
        ];
    }
    */

    // ─── Endpoint: Trend Harian ────────────────────────────────────────────────
    // TODO: Aktifkan setelah endpoint tersedia

    /*
    public function getTrendHarian(string $dari, string $sampai): \Illuminate\Support\Collection
    {
        $json = $this->fetch('trendHarian', ['dari' => $dari, 'sampai' => $sampai]);

        return collect($json['rows'] ?? [])
            ->map(fn($row) => (object) [
                'tanggal' => $row['TANGGAL'],
                'ranap'   => (int) $row['RANAP'],
                'rajal'   => (int) $row['RAJAL'],
                'igd'     => (int) $row['IGD'],
            ]);
    }
    */

    // ─── Endpoint: Triage IGD ──────────────────────────────────────────────────
    // TODO: Aktifkan setelah endpoint tersedia

    /*
    public function getTriageIGD(string $dari, string $sampai): \Illuminate\Support\Collection
    {
        $json = $this->fetch('triageIGD', ['dari' => $dari, 'sampai' => $sampai]);

        return collect($json['rows'] ?? [])
            ->map(fn($row) => (object) [
                'kategori_triage' => $row['KATEGORI'],
                'jumlah'          => (int) $row['JUMLAH'],
            ]);
    }
    */
}