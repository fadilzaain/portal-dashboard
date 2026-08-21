<?php

namespace App\Services\Sdm;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BezettingService
{
    private string $url;
    private int $timeout;
    private int $cacheTtl;

    public function __construct()
    {
        $this->url      = config('services.bezetting.url', '');
        $this->timeout  = (int) config('services.bezetting.timeout', 15);
        $this->cacheTtl = (int) config('services.bezetting.cache_ttl', 3600);
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
                // Handle variasi nama field 
                $delta = (int) ($row['KURANG/ LEBIH'] ?? $row['KURANG/LEBIH'] ?? $row['KURANG_LEBIH'] ?? 0);

                return (object) [
                    'jabatan'    => trim($row['JABATAN'] ?? '-'),
                    'kebutuhan'  => (int) ($row['KEBUTUHAN'] ?? 0),
                    'tersedia'   => (int) ($row['JUMLAH PEGAWAI'] ?? $row['JUMLAH_PEGAWAI'] ?? 0),
                    'delta'      => $delta,
                    'kekurangan' => $delta < 0 ? abs($delta) : 0,
                    'kategori'   => JabatanKategori::resolve($row['JABATAN'] ?? ''),
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
     //Ringkasan summary cards
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

     //===========================================
     //Flush cache manual 
     //===========================================
    public function flushCache(): void
    {
        Cache::forget('bezetting_data');
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