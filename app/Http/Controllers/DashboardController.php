<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class DashboardController extends Controller
{
    private const BULAN_NAMES = [
        '',
        'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember',
    ];

    public function index(Request $request)
    {
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);

        $bulanLabel = self::BULAN_NAMES[$bulan] ?? self::BULAN_NAMES[now()->month];
        $bulanLabelPelayanan = 'Jan – ' . (self::BULAN_NAMES[$bulan] ?? '');
        $bulanLabelData = self::BULAN_NAMES[$bulan] ?? $bulanLabel;

        $pelayanan = $this->getPelayananSummary($tahun, $bulan);
        $keuangan = $this->getKeuanganSummary($tahun);
        $sdm = $this->getSdmSummary();
        $mutu = $this->getMutuSummary($tahun, $bulan);
        $bpjs = $this->getBpjsSummary($tahun, $bulan);
        $apps = config('portal.apps');

        return view('dashboard.index', compact(
            'apps',
            'bulan', 'tahun', 'bulanLabel',
            'bulanLabelData',
            'bulanLabelPelayanan',
            'pelayanan', 'keuangan', 'sdm', 'mutu', 'bpjs'
        ));
    }

    private function getPelayananSummary(int $tahun, int $bulan): array
    {
        try {
            $dari   = Carbon::create($tahun, 1, 1)->format('Y-m-d');
            $sampai = Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

            $baseUrl  = env('BOR_API_URL', 'http://192.168.10.8:8082');
            $response = Http::timeout(10)->get("{$baseUrl}/getborlostoi/all/{$dari}/{$sampai}");

            if (!$response->successful()) return $this->emptyPelayanan();

            $row = $response->json()['rows'][0] ?? null;
            if (!$row) return $this->emptyPelayanan();

            return [
                'bor' => round($row['bor']   ?? 0, 1),
                'los' => round($row['avlos'] ?? 0, 1),
                'toi' => round($row['toi']   ?? 0, 1),
                'bto' => round($row['bto']   ?? 0, 1),
            ];
        } catch (\Exception $e) {
            return $this->emptyPelayanan();
        }
    }

    private function emptyPelayanan(): array
    {
        return ['bor' => 0, 'los' => 0, 'toi' => 0, 'bto' => 0];
    }

    private function getKeuanganSummary(int $tahun): array
    {
        try {
            $bulanTerbaru = now()->month;

            $pendapatan = (float) DB::connection('mysql3')
                ->table('tr_mutasirekbank')
                ->whereYear('effective_date', $tahun)
                ->whereMonth('effective_date', '<=', $bulanTerbaru)
                ->whereNotNull('credit')
                ->where('credit', '>', 0)
                ->sum('credit');

            $belanja = (float) DB::connection('mysql2')
                ->table('cheque')
                ->whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', '<=', $bulanTerbaru)
                ->sum('jumlah');

            return [
                'pendapatan'   => $pendapatan,
                'belanja'      => $belanja,
                'bulan_awal'   => 1,
                'bulan_akhir'  => $bulanTerbaru,
            ];
        } catch (\Exception $e) {
            return ['pendapatan' => 0, 'belanja' => 0, 'bulan_awal' => 1, 'bulan_akhir' => now()->month];
        }
    }

    private function getSdmSummary(): array
    {
        try {
            $apiBase  = env('API_SIKAWAN_BASE', 'http://192.168.10.8/sikawan-api/public/api/v1');
            $response = Http::timeout(10)->get("{$apiBase}/sikawan");
            $data     = $response->successful() ? ($response->json('data') ?? []) : [];

            return [
                'total'       => (int) ($data['total_pegawai'] ?? 0),
                'medis'       => (int) ($data['total_medis'] ?? 0),
                'non_medis'   => (int) ($data['total_non_medis'] ?? 0),
                'shift_pagi'  => (int) ($data['total_shift_pagi'] ?? 0),
                'shift_siang' => (int) ($data['total_shift_siang'] ?? 0),
                'shift_malam' => (int) ($data['total_shift_malam'] ?? 0),
            ];
        } catch (\Exception $e) {
            return [
                'total'       => 0,
                'medis'       => 0,
                'non_medis'   => 0,
                'shift_pagi'  => 0,
                'shift_siang' => 0,
                'shift_malam' => 0,
            ];
        }
    }

    private function getMutuSummary(int $tahun, int $bulan): array
    {
        try {
            $service = app(\App\Services\IndikatorMutuService::class);

            $triwulan = (int) ceil($bulan / 3);
            $pmkpRaw  = $service->fetchPmkp($triwulan, $tahun);
            $tabel    = $service->formatTabel($pmkpRaw);

            return [
                'total'          => count($tabel),
                'tercapai'       => collect($tabel)->where('status', 'tercapai')->count(),
                'tidak_tercapai' => collect($tabel)->where('status', 'belum')->count(),
            ];
        } catch (\Exception $e) {
            return ['total' => 0, 'tercapai' => 0, 'tidak_tercapai' => 0];
        }
    }

    private function getBpjsSummary(int $tahun, int $bulan): array
    {
        try {
            $from = Carbon::create($tahun, $bulan, 1)->startOfMonth();
            $to   = Carbon::create($tahun, $bulan, 1)->endOfMonth();

            $rinapRaw = $this->getKlaimBpjsRow('mon_klaim_rinap', 'tglPulang', $from, $to);
            $rjalanRaw = $this->getKlaimBpjsRow('mon_klaim_rjalan', 'tglSep', $from, $to);

            return [
                'rawat_inap'            => (int)   ($rinapRaw->total    ?? 0),
                'rawat_jalan'           => (int)   ($rjalanRaw->total   ?? 0),
                'nominal_rinap'         => (float) ($rinapRaw->nominal  ?? 0),
                'nominal_rjalan'        => (float) ($rjalanRaw->nominal ?? 0),
                'nominal_terbayar'      => (float)($rinapRaw->nominal_terbayar  ?? 0) + (float)($rjalanRaw->nominal_terbayar  ?? 0),
                'nominal_pending'       => (float)($rinapRaw->nominal_pending   ?? 0) + (float)($rjalanRaw->nominal_pending   ?? 0),
                'nominal_tidak_layak'   => (float)($rinapRaw->nominal_tidak_layak ?? 0) + (float)($rjalanRaw->nominal_tidak_layak ?? 0),
                'terbayar'              => (int)   ($rinapRaw->terbayar    ?? 0) + (int) ($rjalanRaw->terbayar    ?? 0),
                'pending'               => (int)   ($rinapRaw->pending     ?? 0) + (int) ($rjalanRaw->pending     ?? 0),
                'tidak_layak'           => (int)   ($rinapRaw->tidak_layak ?? 0) + (int) ($rjalanRaw->tidak_layak ?? 0),
            ];
        } catch (\Exception $e) {
            return [
                'rawat_inap' => 0,
                'rawat_jalan' => 0,
                'nominal_rinap' => 0,
                'nominal_rjalan' => 0,
                'nominal_terbayar' => 0,
                'nominal_pending' => 0,
                'nominal_tidak_layak' => 0,
                'terbayar' => 0,
                'pending' => 0,
                'tidak_layak' => 0,
            ];
        }
    }

    private function getKlaimBpjsRow(string $table, string $dateColumn, Carbon $from, Carbon $to): object
    {
        return DB::connection('klaim_bpjs')->selectOne("
            SELECT
                COUNT(*) AS total,
                SUM(biaya_byPengajuan) AS nominal,
                SUM(CASE WHEN status LIKE '3%' THEN 1 ELSE 0 END) AS terbayar,
                SUM(CASE WHEN status LIKE '2%' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status LIKE '4%' THEN 1 ELSE 0 END) AS tidak_layak,
                SUM(CASE WHEN status LIKE '3%' THEN biaya_byPengajuan ELSE 0 END) AS nominal_terbayar,
                SUM(CASE WHEN status LIKE '2%' THEN biaya_byPengajuan ELSE 0 END) AS nominal_pending,
                SUM(CASE WHEN status LIKE '4%' THEN biaya_byPengajuan ELSE 0 END) AS nominal_tidak_layak
            FROM {$table}
            WHERE {$dateColumn} BETWEEN ? AND ?
        ", [$from, $to]);
    }
}
