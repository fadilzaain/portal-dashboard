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
        $this->cacheTtl = (int) env('API_BEZETTING_CACHE_TTL', 3600); // default cache 1 jam
    }

    //ambil data bezetting, dicache agar tidak hit API tiap request
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

   //fetch dari API lalu parse ke collection of objects
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

   //ringkasan buat summary card
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

   //cache manual
    public function flushCache(): void
    {
        Cache::forget('bezetting_data');
    }

   //menentukan kategori berdasarkan nama jabatan
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

   //hitung presentase tersedia v kebutuhan (0-100 max 100%)
    private function hitungPct(int $tersedia, int $kebutuhan): int
    {
        if ($kebutuhan <= 0) return 100;
        return (int) min(round($tersedia / $kebutuhan * 100), 100);
    }
}