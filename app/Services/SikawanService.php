<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SikawanService
{
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = env('API_SIKAWAN_BASE', 'http://192.168.10.8/sikawan-api/public/api/v1');
        $this->timeout = (int) env('API_SIKAWAN_TIMEOUT', 10);
    }

   // ambil data endpoint
    public function getSdmData(): array
    {
        try {
            $response = Http::timeout($this->timeout)->get("{$this->baseUrl}/sikawan");

            if (!$response->successful()) {
                Log::warning('SikawanService: response tidak sukses', [
                    'status' => $response->status(),
                    'url'    => "{$this->baseUrl}/sikawan",
                ]);
                return [];
            }

            return $response->json('data') ?? [];

        } catch (\Exception $e) {
            Log::error('SikawanService: gagal fetch data', [
                'message' => $e->getMessage(),
            ]);
            return [];
        }
    }

    //Parse raw data dari API menjadi struktur yang siap dipakai view
    public function parse(array $data): array
    {
        $get = fn(string $key) => (int) ($data[$key] ?? 0);

        $totalPns           = $get('total_pns');
        $totalP3k           = $get('total_p3k');
        $totalP3kParuhWaktu = $get('total_p3k_pw');
        $totalCpns          = $get('total_cpns');
        $totalKontrak       = $get('total_kontrak');
        $totalTetap         = $get('total_tetap');
        $totalOrientasi     = $get('total_orientasi');
        $totalMedis         = $get('total_medis');
        $totalNonMedis      = $get('total_non_medis');

        $totalAktif = $totalPns + $totalP3k + $totalP3kParuhWaktu
                    + $totalCpns + $totalKontrak + $totalTetap + $totalOrientasi;

        $dokterSpesialis = $get('total_dokter_spesialis');
        $dokterUmum      = $get('total_dokter_umum');
        $perawat         = $get('total_perawat');
        $bidan           = $get('total_bidan');
        $medisLainnya    = max(0, $totalMedis - $dokterSpesialis - $dokterUmum - $perawat - $bidan);

        return [
            // Stat cards
            'totalPegawai'       => $get('total_pegawai'),
            'totalAktif'         => $totalAktif,
            'totalPns'           => $totalPns,
            'totalP3k'           => $totalP3k,
            'totalP3kParuhWaktu' => $totalP3kParuhWaktu,
            'totalCpns'          => $totalCpns,
            'totalKontrak'       => $totalKontrak,
            'totalTetap'         => $totalTetap,
            'totalOrientasi'     => $totalOrientasi,
            'totalMedis'         => $totalMedis,
            'totalNonMedis'      => $totalNonMedis,

            // Chart status kepegawaian
            'statusLabels' => ['PNS', 'P3K', 'P3K Paruh Waktu', 'CPNS', 'Kontrak', 'Tetap', 'Orientasi'],
            'statusValues' => [$totalPns, $totalP3k, $totalP3kParuhWaktu, $totalCpns, $totalKontrak, $totalTetap, $totalOrientasi],

            // Shift
            'shiftSummary' => [
                'PAGI'  => ['total' => $get('total_shift_pagi'),  'detail' => []],
                'SIANG' => ['total' => $get('total_shift_siang'), 'detail' => []],
                'MALAM' => ['total' => $get('total_shift_malam'), 'detail' => []],
            ],
            'shiftTimes' => [
                'PAGI'  => '07.00 – 14.00',
                'SIANG' => '14.00 – 21.00',
                'MALAM' => '21.00 – 07.00',
            ],
        ];
    }

   //Hitung presentase aman dari pembagian nol
    public function pct(int $value, int $total): float
    {
        return $total > 0 ? round($value / $total * 100, 2) : 0.0;
    }
}