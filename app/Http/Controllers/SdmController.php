<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class SdmController extends Controller
{
    private string $apiBase;

    public function __construct()
    {
        $this->apiBase = env('API_SIKAWAN_BASE', 'http://192.168.10.8/sikawan-api/public/api/v1');
    }
    
    public function index()
    {
        try {
            $response = Http::timeout(10)->get("{$this->apiBase}/sikawan");

            $data = $response->successful()
                ? ($response->json('data') ?? [])
                : [];
        } catch (\Exception $e) {
            $data = [];
        }

        // Helper: ambil nilai integer dari array result
        $get = fn(string $key) => (int) ($data[$key] ?? 0);

        // ── STAT CARDS ───────────────────────────────────────────────────────
        $totalPegawai       = $get('total_pegawai');
        $totalPns           = $get('total_pns');
        $totalP3k           = $get('total_p3k');
        $totalP3kParuhWaktu = $get('total_p3k_pw');
        $totalCpns          = $get('total_cpns');
        $totalKontrak       = $get('total_kontrak');
        $totalTetap         = $get('total_tetap');
        $totalOrientasi     = $get('total_orientasi');
        $totalMedis         = $get('total_medis');
        $totalNonMedis      = $get('total_non_medis');

        // Total aktif = jumlah semua status kepegawaian
        $totalAktif = $totalPns + $totalP3k + $totalP3kParuhWaktu
                    + $totalCpns + $totalKontrak + $totalTetap + $totalOrientasi;

        // ── doughnut chart profesi ─────────────────────────────────────────
        $dokterSpesialis = $get('total_dokter_spesialis');
        $dokterUmum      = $get('total_dokter_umum');
        $perawat         = $get('total_perawat');
        $bidan           = $get('total_bidan');

        // tenada medis lainnya
        $medisLainnya = max(0, $totalMedis - $dokterSpesialis - $dokterUmum - $perawat - $bidan);

        $profesiLabels = ['Dokter Spesialis', 'Dokter Umum', 'Perawat', 'Bidan', 'Tenaga Medis Lainnya', 'Tenaga Non Medis'];
        $profesiValues = [$dokterSpesialis, $dokterUmum, $perawat, $bidan, $medisLainnya, $totalNonMedis];

        // ── bar chart status kepegawaian ───────────────────────────────────
        $statusLabels = ['PNS', 'P3K', 'P3K Paruh Waktu', 'CPNS', 'Kontrak', 'Tetap', 'Orientasi'];
        $statusValues = [$totalPns, $totalP3k, $totalP3kParuhWaktu, $totalCpns, $totalKontrak, $totalTetap, $totalOrientasi];

        // ── bzetting note : integrate nanti ────────────────────────────────
        //   $r = Http::timeout(10)->get("{$this->apiBase}/bezetting");
        //   $bezettingData = collect($r->json('data') ?? [])
        //       ->map(fn($row) => (object) $row);
        //
        $bezettingData = collect();

        // ── Shift Hari Ini ───────────────────────────────────────────
        $shiftSummary = [
            'PAGI'  => ['total' => $get('total_shift_pagi'),  'detail' => []],
            'SIANG' => ['total' => $get('total_shift_siang'), 'detail' => []],
            'MALAM' => ['total' => $get('total_shift_malam'), 'detail' => []],
        ];

        $shiftTimes = [
            'PAGI'  => '07.00 – 14.00',
            'SIANG' => '14.00 – 21.00',
            'MALAM' => '21.00 – 07.00',
        ];

        return view('portal.sdm', compact(
            'totalPegawai', 'totalAktif',
            'totalPns', 'totalP3k', 'totalP3kParuhWaktu', 'totalCpns',
            'totalKontrak', 'totalTetap', 'totalOrientasi',
            'totalMedis', 'totalNonMedis',
            'profesiLabels', 'profesiValues',
            'statusLabels',  'statusValues',
            'bezettingData',
            'shiftSummary',  'shiftTimes',
        ));
    }
}