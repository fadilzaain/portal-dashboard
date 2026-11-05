<?php

namespace App\Http\Controllers;

use App\Services\IkuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

    /**
     * true  = pendapatan & belanja dihitung sampai bulan data TERAKHIR yang ada di KEDUA sumber,
     *         jadi surplus/defisit sebanding dan label "Jan – X" jujur.
     * false = perilaku lama (sampai bulan berjalan, walau salah satu sumber belum terisi).
     */
    private const SELARASKAN_PERIODE_KEUANGAN = true;

    public function index(Request $request)
    {
        $bulan = max(1, min(12, (int) $request->input('bulan', now()->month)));
        $tahun = (int) $request->input('tahun', now()->year);

        $bulanLabel     = self::BULAN_NAMES[$bulan] ?? self::BULAN_NAMES[now()->month];
        $bulanLabelData = self::BULAN_NAMES[$bulan] ?? $bulanLabel;

        // Rentang card pelayanan: Jan sampai bulan data terakhir yang benar-benar ada
        $namaBulanPendek = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
        [$tahunPelayanan, $sampaibulanPelayanan] = $this->periodePelayanan();
        $bulanLabelPelayanan = 'Jan – ' . $namaBulanPendek[$sampaibulanPelayanan];

        $pelayanan = $this->getPelayananSummary($tahunPelayanan, $sampaibulanPelayanan);
        $keuangan  = $this->getKeuanganSummary($tahun);
        $sdm       = $this->getSdmSummary();
        $mutu      = $this->getMutuSummary($tahun, $bulan);

        // Kartu BPJS: tanpa filter eksplisit, pakai bulan terakhir yang datanya ada
        // (bulan berjalan biasanya belum terisi). Kalau user memilih bulan/tahun, ikut pilihan itu.
        if ($request->filled('bulan') || $request->filled('tahun')) {
            [$tahunBpjs, $bulanBpjs] = [$tahun, $bulan];
        } else {
            [$tahunBpjs, $bulanBpjs] = $this->periodeBpjsTerakhir();
        }
        $bulanLabelBpjs = self::BULAN_NAMES[$bulanBpjs] ?? $bulanLabel;
        $bpjs           = $this->getBpjsSummary($tahunBpjs, $bulanBpjs);

        // Card IKU: periodenya otomatis (tahun terbaru & triwulan terakhir berdata), tidak ikut filter bulan/tahun
        $iku            = $this->getIkuSummary();

        $apps      = config('portal.apps');

        return view('dashboard.index', compact(
            'apps',
            'bulan', 'tahun', 'bulanLabel',
            'bulanLabelData',
            'bulanLabelPelayanan',
            'tahunPelayanan',
            'pelayanan', 'keuangan', 'sdm', 'mutu', 'bpjs', 'iku',
            'bulanLabelBpjs', 'tahunBpjs'
        ));
    }

    /**
     * Cari bulan terakhir yang punya data BOR (> 0), dengan batas sampai bulan lalu
     * (bulan berjalan belum lengkap, jadi tidak ikut dihitung).
     * Return: [tahun, sampai_bulan]
     */
    private function periodePelayanan(): array
    {
        $now = Carbon::now();

        // Batas atas = bulan lalu
        $batasTahun  = $now->month === 1 ? $now->year - 1 : $now->year;
        $batasBulan  = $now->month === 1 ? 12 : $now->month - 1;

        try {
            $terakhir = DB::connection('dashi')
                ->table('borlosttoiall_thn')
                ->where('bor', '>', 0)
                ->where(function ($q) use ($batasTahun, $batasBulan) {
                    $q->where('tahun', '<', $batasTahun)
                      ->orWhere(function ($q2) use ($batasTahun, $batasBulan) {
                          $q2->where('tahun', $batasTahun)
                             ->where('bulan', '<=', $batasBulan);
                      });
                })
                ->orderByDesc('tahun')
                ->orderByDesc('bulan')
                ->first(['tahun', 'bulan']);

            if ($terakhir) {
                return [(int) $terakhir->tahun, max(1, min(12, (int) $terakhir->bulan))];
            }
        } catch (\Throwable $e) {
            Log::warning('Dashboard: gagal cek periode pelayanan', ['error' => $e->getMessage()]);
        }

        return [$batasTahun, $batasBulan];
    }

    private function getPelayananSummary(int $tahun, int $sampaibulan): array
    {
        try {
            $data = DB::connection('dashi')
                ->table('borlosttoiall_thn')
                ->where('tahun', $tahun)
                ->whereBetween('bulan', [1, $sampaibulan])
                ->where('bor', '>', 0)
                ->get();

            if ($data->isEmpty()) return $this->emptyPelayanan();

            return [
                'bor' => round($data->avg('bor'), 1),
                'los' => round($data->avg('los'), 1),
                'toi' => round($data->avg('toi'), 1),
                'bto' => round($data->avg('bto'), 1),
            ];
        } catch (\Throwable $e) {
            Log::warning('Dashboard: ringkasan pelayanan gagal', ['error' => $e->getMessage()]);
            return $this->emptyPelayanan();
        }
    }

    private function emptyPelayanan(): array
    {
        return ['bor' => 0, 'los' => 0, 'toi' => 0, 'bto' => 0];
    }

    private function getKeuanganSummary(int $tahun): array
    {
        // Tahun lalu = setahun penuh, tahun berjalan = sampai bulan berjalan
        $batas = $tahun < now()->year ? 12 : now()->month;

        try {
            $bulanAkhir = $batas;

            if (self::SELARASKAN_PERIODE_KEUANGAN) {
                $tglPendapatan = DB::connection('mysql3')
                    ->table('tr_mutasirekbank')
                    ->whereYear('effective_date', $tahun)
                    ->whereNotNull('credit')
                    ->where('credit', '>', 0)
                    ->max('effective_date');

                $tglBelanja = DB::connection('mysql2')
                    ->table('cheque')
                    ->whereYear('tanggal', $tahun)
                    ->max('tanggal');

                $bulanData = array_filter([
                    $tglPendapatan ? Carbon::parse($tglPendapatan)->month : null,
                    $tglBelanja    ? Carbon::parse($tglBelanja)->month    : null,
                ]);

                if (!empty($bulanData)) {
                    $bulanAkhir = min($batas, ...$bulanData);
                }
            }

            $pendapatan = (float) DB::connection('mysql3')
                ->table('tr_mutasirekbank')
                ->whereYear('effective_date', $tahun)
                ->whereMonth('effective_date', '<=', $bulanAkhir)
                ->whereNotNull('credit')
                ->where('credit', '>', 0)
                ->sum('credit');

            $belanja = (float) DB::connection('mysql2')
                ->table('cheque')
                ->whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', '<=', $bulanAkhir)
                ->sum('jumlah');

            return [
                'pendapatan'  => $pendapatan,
                'belanja'     => $belanja,
                'bulan_awal'  => 1,
                'bulan_akhir' => $bulanAkhir,
            ];
        } catch (\Throwable $e) {
            Log::warning('Dashboard: ringkasan keuangan gagal', ['error' => $e->getMessage()]);
            return ['pendapatan' => 0, 'belanja' => 0, 'bulan_awal' => 1, 'bulan_akhir' => $batas];
        }
    }

    private function getSdmSummary(): array
    {
        try {
            $apiBase  = env('API_SIKAWAN_BASE', 'http://192.168.10.8/sikawan-api/public/api/v1');
            $response = Http::timeout(10)->get("{$apiBase}/sikawan");

            if (!$response->successful()) {
                Log::warning('Dashboard: API Sikawan tidak sukses', ['status' => $response->status()]);
            }

            $data = $response->successful() ? ($response->json('data') ?? []) : [];

            return [
                'total'       => (int) ($data['total_pegawai'] ?? 0),
                'medis'       => (int) ($data['total_medis'] ?? 0),
                'non_medis'   => (int) ($data['total_non_medis'] ?? 0),
                'shift_pagi'  => (int) ($data['total_shift_pagi'] ?? 0),
                'shift_siang' => (int) ($data['total_shift_siang'] ?? 0),
                'shift_malam' => (int) ($data['total_shift_malam'] ?? 0),
            ];
        } catch (\Throwable $e) {
            Log::warning('Dashboard: ringkasan SDM gagal', ['error' => $e->getMessage()]);
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
        } catch (\Throwable $e) {
            Log::warning('Dashboard: ringkasan mutu gagal', ['error' => $e->getMessage()]);
            return ['total' => 0, 'tercapai' => 0, 'tidak_tercapai' => 0];
        }
    }

    /**
     * Ringkasan IKU untuk card dashboard (data dibaca langsung dari SPM Track, jadi selalu mengikuti isian terbaru).
     * Periodenya sama dengan default halaman IKU (tahun terbaru, triwulan terakhir yang sudah berdata),
     * supaya angka di card sama persis dengan yang tampil saat card dibuka.
     * Kalau SPM Track tidak terjangkau: card tampil kosong dan errornya masuk log (tidak bikin halaman 500).
     */
    private function getIkuSummary(): array
    {
        try {
            $service  = app(IkuService::class);
            $tahun    = $service->tahunTersedia()[0];
            $triwulan = $service->triwulanTerakhir($tahun);
            $data     = $service->dataDashboard($tahun, $triwulan);
            $r        = $data['ringkasan'];

            return [
                'tahun'      => $tahun,
                'triwulan'   => $triwulan,
                'total'      => $r['total'],
                'tercapai'   => $r['tercapai'],
                'perhatian'  => $r['perlu_perhatian'],
                // "Belum tercapai" murni (tanpa yang perlu perhatian, karena itu dihitung terpisah)
                'belum'      => $r['belum_tercapai'] - $r['perlu_perhatian'],
                'kosong'     => $r['belum_ada_data'],
                'persen'     => $r['persen_capaian'],
                'status'     => $service->tentukanStatus($r['persen_capaian']),
                // Daftar per indikator untuk baris kecil di card (nama, status, persen capaian)
                'daftar'     => collect($data['baris'])->map(fn ($b) => [
                    'nama'        => $b['nama'],
                    'status'      => $b['status'],
                    'capaian_fmt' => $b['capaian_fmt'],
                ])->all(),
            ];
        } catch (\Throwable $e) {
            Log::warning('Dashboard: ringkasan IKU gagal', ['error' => $e->getMessage()]);

            return [
                'tahun'     => null,
                'triwulan'  => null,
                'total'     => 0,
                'tercapai'  => 0,
                'perhatian' => 0,
                'belum'     => 0,
                'kosong'    => 0,
                'persen'    => null,
                'status'    => 'kosong',
                'daftar'    => [],
            ];
        }
    }

    /**
     * Bulan terakhir yang punya data klaim (rawat inap atau rawat jalan, mana yang lebih baru).
     * Di-cache 10 menit supaya MAX() ke tabel besar tidak jalan di setiap muat halaman.
     * Return: [tahun, bulan]
     */
    private function periodeBpjsTerakhir(): array
    {
        try {
            return Cache::remember('dashboard_bpjs_periode_terakhir', 600, function () {
                $rinap = DB::connection('klaim_bpjs')
                    ->table('mon_klaim_rinap')
                    ->where('tglPulang', '<=', now())
                    ->max('tglPulang');

                $rjalan = DB::connection('klaim_bpjs')
                    ->table('mon_klaim_rjalan')
                    ->where('tglSep', '<=', now())
                    ->max('tglSep');

                $tgl = max((string) $rinap, (string) $rjalan);

                if ($tgl !== '') {
                    $c = Carbon::parse($tgl);
                    return [(int) $c->year, (int) $c->month];
                }

                return [now()->year, now()->month];
            });
        } catch (\Throwable $e) {
            Log::warning('Dashboard: gagal cek periode BPJS', ['error' => $e->getMessage()]);
            return [now()->year, now()->month];
        }
    }

    private function getBpjsSummary(int $tahun, int $bulan): array
    {
        try {
            $from = Carbon::create($tahun, $bulan, 1)->startOfMonth();
            $to   = Carbon::create($tahun, $bulan, 1)->endOfMonth();

            $rinapRaw  = $this->getKlaimBpjsRow('mon_klaim_rinap', 'tglPulang', $from, $to);
            $rjalanRaw = $this->getKlaimBpjsRow('mon_klaim_rjalan', 'tglSep', $from, $to);

            return [
                'rawat_inap'          => (int)   ($rinapRaw->total    ?? 0),
                'rawat_jalan'         => (int)   ($rjalanRaw->total   ?? 0),
                'nominal_rinap'       => (float) ($rinapRaw->nominal  ?? 0),
                'nominal_rjalan'      => (float) ($rjalanRaw->nominal ?? 0),
                'nominal_terbayar'    => (float) ($rinapRaw->nominal_terbayar    ?? 0) + (float) ($rjalanRaw->nominal_terbayar    ?? 0),
                'nominal_pending'     => (float) ($rinapRaw->nominal_pending     ?? 0) + (float) ($rjalanRaw->nominal_pending     ?? 0),
                'nominal_tidak_layak' => (float) ($rinapRaw->nominal_tidak_layak ?? 0) + (float) ($rjalanRaw->nominal_tidak_layak ?? 0),
                'terbayar'            => (int)   ($rinapRaw->terbayar    ?? 0) + (int) ($rjalanRaw->terbayar    ?? 0),
                'pending'             => (int)   ($rinapRaw->pending     ?? 0) + (int) ($rjalanRaw->pending     ?? 0),
                'tidak_layak'         => (int)   ($rinapRaw->tidak_layak ?? 0) + (int) ($rjalanRaw->tidak_layak ?? 0),
            ];
        } catch (\Throwable $e) {
            Log::warning('Dashboard: ringkasan BPJS gagal', ['error' => $e->getMessage()]);
            return [
                'rawat_inap'          => 0,
                'rawat_jalan'         => 0,
                'nominal_rinap'       => 0,
                'nominal_rjalan'      => 0,
                'nominal_terbayar'    => 0,
                'nominal_pending'     => 0,
                'nominal_tidak_layak' => 0,
                'terbayar'            => 0,
                'pending'             => 0,
                'tidak_layak'         => 0,
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