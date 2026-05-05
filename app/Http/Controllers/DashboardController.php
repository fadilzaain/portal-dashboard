<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);

        $bulanNames = ['','Januari','Februari','Maret','April','Mei','Juni',
                       'Juli','Agustus','September','Oktober','November','Desember'];
        $bulanLabel = $bulanNames[$bulan];

        // 1.Pelayanan Pasien
        try {
            /** @var \App\Services\GoogleSheetApiService $gsApi */
            $gsApi     = app(\App\Services\GoogleSheetApiService::class);
            $rateTahun = $gsApi->getRateTahun($tahun);

            // Ambil data bulan yang sesuai
            $row = $rateTahun->firstWhere('bulan', $bulan);

            // BTO dari API adalah nilai harian, kali 30 untuk estimasi bulanan
            $btoRaw = $row ? round($row->bto * 30, 1) : 0;

            $pelayanan = [
                'bor' => $row ? round($row->bor,  1) : 0,
                'los' => $row ? round($row->avlos, 1) : 0,
                'toi' => $row ? round($row->toi,  1) : 0,
                'bto' => $btoRaw,
            ];
        } catch (\Exception $e) {
            $pelayanan = ['bor' => 0, 'los' => 0, 'toi' => 0, 'bto' => 0];
        }

        // 2. Keuangan
        try {
            $pendapatan = (float) DB::connection('mysql3')
                ->table('tr_mutasirekbank')
                ->whereYear('effective_date', $tahun)
                ->whereMonth('effective_date', $bulan)
                ->whereNotNull('credit')->where('credit', '>', 0)
                ->sum('credit');

            $belanja = (float) DB::connection('mysql2')
                ->table('cheque')
                ->whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
                ->sum('jumlah');

            $keuangan = [
                'pendapatan' => $pendapatan,
                'belanja'    => $belanja,
            ];
        } catch (\Exception $e) {
            $keuangan = ['pendapatan' => 0, 'belanja' => 0];
        }

        // 3. SDM 
        try {
            $apiBase  = env('API_SIKAWAN_BASE', 'http://192.168.10.8/sikawan-api/public/api/v1');
            $response = Http::timeout(10)->get("{$apiBase}/sikawan");
            $data     = $response->successful() ? ($response->json('data') ?? []) : [];

            $get = fn(string $key) => (int) ($data[$key] ?? 0);

            $medis    = $get('total_medis');
            $nonMedis = $get('total_non_medis');

            $sdm = [
                'total'     => $medis + $nonMedis,
                'medis'     => $medis,
                'non_medis' => $nonMedis,
            ];
        } catch (\Exception $e) {
            $sdm = ['total' => 0, 'medis' => 0, 'non_medis' => 0];
        }

        // API SiKawan
        try {
            $responseSdm = Http::timeout(10)->get(env('API_SIKAWAN_BASE', 'http://192.168.10.8/sikawan-api/public/api/v1') . '/sikawan');
            $dataSdm = $responseSdm->successful() ? ($responseSdm->json('data') ?? []) : [];
        } catch (\Exception $e) {
            $dataSdm = [];
        }

        $sdm = [
            'total'       => $dataSdm['total_pegawai'] ?? 0,
            'medis'       => $dataSdm['total_medis']   ?? 0,
            'non_medis'   => $dataSdm['total_non_medis'] ?? 0,
            'shift_pagi'  => $dataSdm['total_shift_pagi']  ?? 0,
            'shift_siang' => $dataSdm['total_shift_siang'] ?? 0,
            'shift_malam' => $dataSdm['total_shift_malam'] ?? 0,
        ];

        // 4. Indikator Mutu 
        try {
            $service = app(\App\Services\IndikatorMutuService::class);

            // Konversi bulan - triwulan
            $triwulan = (int) ceil($bulan / 3);
            $pmkpRaw  = $service->fetchPmkp($triwulan, $tahun);
            $tabel    = $service->formatTabel($pmkpRaw);

            $mutu = [
                'total'          => count($tabel),
                'tercapai'       => collect($tabel)->where('status', 'tercapai')->count(),
                'tidak_tercapai' => collect($tabel)->where('status', 'belum')->count(),
            ];
        } catch (\Exception $e) {
            $mutu = ['total' => 0, 'tercapai' => 0, 'tidak_tercapai' => 0];
        }

        // 5. Klaim BPJS 
        try {
            $from = Carbon::create($tahun, $bulan, 1)->startOfMonth();
            $to   = Carbon::create($tahun, $bulan, 1)->endOfMonth();

            $rinapRaw = DB::connection('klaim_bpjs')->selectOne("
                SELECT
                    COUNT(*) AS total,
                    SUM(biaya_byPengajuan) AS nominal,
                    SUM(CASE WHEN status LIKE '3%' THEN 1 ELSE 0 END) AS terbayar,
                    SUM(CASE WHEN status LIKE '2%' THEN 1 ELSE 0 END) AS pending,
                    SUM(CASE WHEN status LIKE '4%' THEN 1 ELSE 0 END) AS tidak_layak
                FROM mon_klaim_rinap
                WHERE tglPulang BETWEEN ? AND ?
            ", [$from, $to]);

            $rjalanRaw = DB::connection('klaim_bpjs')->selectOne("
                SELECT
                    COUNT(*) AS total,
                    SUM(biaya_byPengajuan) AS nominal,
                    SUM(CASE WHEN status LIKE '3%' THEN 1 ELSE 0 END) AS terbayar,
                    SUM(CASE WHEN status LIKE '2%' THEN 1 ELSE 0 END) AS pending,
                    SUM(CASE WHEN status LIKE '4%' THEN 1 ELSE 0 END) AS tidak_layak
                FROM mon_klaim_rjalan
                WHERE tglSep BETWEEN ? AND ?
            ", [$from, $to]);

            $bpjs = [
                'rawat_inap'     => (int)   ($rinapRaw->total    ?? 0),
                'rawat_jalan'    => (int)   ($rjalanRaw->total   ?? 0),
                'nominal_rinap'  => (float) ($rinapRaw->nominal  ?? 0),
                'nominal_rjalan' => (float) ($rjalanRaw->nominal ?? 0),
                'terbayar'       => (int)   ($rinapRaw->terbayar    ?? 0) + (int) ($rjalanRaw->terbayar    ?? 0),
                'pending'        => (int)   ($rinapRaw->pending     ?? 0) + (int) ($rjalanRaw->pending     ?? 0),
                'tidak_layak'    => (int)   ($rinapRaw->tidak_layak ?? 0) + (int) ($rjalanRaw->tidak_layak ?? 0),
            ];

        } catch (\Exception $e) {
            $bpjs = ['rawat_inap' => 0, 'rawat_jalan' => 0, 'nominal_rinap' => 0, 'nominal_rjalan' => 0, 'terbayar' => 0, 'pending' => 0, 'tidak_layak' => 0];
        }

        $apps = config('portal.apps');
        // dd([
        //     'pelayanan' => $pelayanan,
        //     'keuangan'  => $keuangan,
        //     'sdm'       => $sdm,
        //     'mutu'      => $mutu,
        //     'bpjs'      => $bpjs,
        // ]);

        return view('dashboard.index', compact(
            'apps',
            'bulan', 'tahun', 'bulanLabel',
            'pelayanan', 'keuangan', 'sdm', 'mutu', 'bpjs'
        ));
    }
}