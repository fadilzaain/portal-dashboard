<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\BezettingService;
use App\Services\SikawanService;

class SdmController extends Controller
{
    public function __construct(
        private SikawanService  $sikawan,
        private BezettingService $bezetting,
    ) {}

    public function index()
    {
        // ── Sikawan ──────────────────────────────────────────
        $raw     = $this->sikawan->getSdmData();
        $sdm     = $this->sikawan->parse($raw);

        $pct = fn(string $key) => $this->sikawan->pct($sdm[$key], $sdm['totalAktif']);

        // ── Bezetting ────────────────────────────────────────
        $bezData    = $this->bezetting->getData();
        $bezSummary = $this->bezetting->getSummary($bezData);

        // ── View ─────────────────────────────────────────────
        return view('portal.sdm', [
            // Stat cards
            'totalPegawai'       => $sdm['totalPegawai'],
            'totalAktif'         => $sdm['totalAktif'],
            'totalPns'           => $sdm['totalPns'],
            'totalP3k'           => $sdm['totalP3k'],
            'totalP3kParuhWaktu' => $sdm['totalP3kParuhWaktu'],
            'totalCpns'          => $sdm['totalCpns'],
            'totalKontrak'       => $sdm['totalKontrak'],
            'totalTetap'         => $sdm['totalTetap'],
            'totalOrientasi'     => $sdm['totalOrientasi'],
            'totalMedis'         => $sdm['totalMedis'],
            'totalNonMedis'      => $sdm['totalNonMedis'],

            // Persentase untuk stat cards
            'pctPns'       => $pct('totalPns'),
            'pctP3k'       => $pct('totalP3k'),
            'pctP3kParuh'  => $pct('totalP3kParuhWaktu'),
            'pctCpns'      => $pct('totalCpns'),
            'pctKontrak'   => $pct('totalKontrak'),
            'pctTetap'     => $pct('totalTetap'),
            'pctOrientasi' => $pct('totalOrientasi'),
            'pctMedis'     => $pct('totalMedis'),
            'pctNonMedis'  => $pct('totalNonMedis'),

            // Chart
            'statusLabels' => $sdm['statusLabels'],
            'statusValues' => $sdm['statusValues'],

            // Shift
            'shiftSummary' => $sdm['shiftSummary'],
            'shiftTimes'   => $sdm['shiftTimes'],

            // Bezetting
            'bezSummary'   => $bezSummary,
        ]);
    }
}