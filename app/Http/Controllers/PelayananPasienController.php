<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetApiService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PelayananPasienController extends Controller
{
    public function __construct(
        protected GoogleSheetApiService $api
    ) {}

    // ══════════════════════════════════════════════════════════════
    // INDEX — Dashboard Pelayanan Pasien
    // ══════════════════════════════════════════════════════════════

    public function index(Request $request)
    {
        $tanggalMulai   = $request->get('dari',   Carbon::now()->startOfMonth()->format('Y-m-d'));
        $tanggalSelesai = $request->get('sampai',  Carbon::now()->format('Y-m-d'));
        $tahun          = (int) $request->get('tahun', Carbon::now()->year);

        $isDummy = false;

        // ── bor, avlos, toi ────────────────────────────────
        try {
            $rateTahun = $this->api->getRateTahun($tahun);

            // Bulan yang punya data (BOR > 0)
            $bulanAktif = $rateTahun->filter(fn($r) => $r->bor > 0);

            // Rata-rata indikator dari bulan yang aktif 
            $bor = $bulanAktif->avg('bor')  ?? 0;
            $los = $bulanAktif->avg('avlos') ?? 0;
            $toi = $bulanAktif->avg('toi')  ?? 0;

            // BTO dari API adalah per-hari (fraksi kecil), akumulasi = sum seluruh bulan aktif
            // Jika API sudah mengembalikan BTO bulanan, gunakan sum; jika harian gunakan avg*30
            // Untuk sementara: rata-rata * 12 sebagai estimasi tahunan (sesuaikan saat endpoint baru tersedia)
            $btoRaw = $bulanAktif->avg('bto') ?? 0;
            $bto    = round($btoRaw * 30, 2); // konversi harian → bulanan (estimasi)

            // Ambil bulan aktif terakhir sebagai nilai "current" jika filter bulan tertentu
            // Jika filter rentang tanggal: pakai rata-rata bulan dalam rentang
            $bulanDalam = $this->getBulanDalamRentang($tanggalMulai, $tanggalSelesai);
            if (! empty($bulanDalam)) {
                $filtered = $rateTahun->filter(fn($r) => in_array($r->bulan, $bulanDalam) && $r->bor > 0);
                if ($filtered->isNotEmpty()) {
                    $bor = round($filtered->avg('bor'),  2);
                    $los = round($filtered->avg('avlos'), 2);
                    $toi = round($filtered->avg('toi'),  2);
                    $bto = round($filtered->avg('bto') * 30, 2);
                }
            }

            $bor = round($bor, 2);
            $los = round($los, 2);
            $toi = round($toi, 2);

        } catch (\Exception $e) {
            $isDummy = true;
            $bor = 72.5;
            $los = 7.2;
            $toi = 2.1;
            $bto = 45.0;
            $rateTahun = collect();
        }

        // ── Ringkasan Ranap ─────────────────────────────────────────────────
        // note : ganti dengan $this->api->getRingkasanRanap() jika endpoint tersedia
        $ringkasanRanap = $isDummy ? $this->dummyRanap() : $this->dummyRanap();

        // ── Ringkasan Rajal ─────────────────────────────────────────────────
        // note : Ganti dengan $this->api->getRingkasanRajal() jika endpoint tersedia
        $ringkasanRajal = $isDummy ? $this->dummyRajal() : $this->dummyRajal();

        // ── Ringkasan IGD ───────────────────────────────────────────────────
        // note : Ganti dengan $this->api->getRingkasanIGD() setelah endpoint tersedia
        $ringkasanIGD = $isDummy ? $this->dummyIGD() : $this->dummyIGD();

        // ── Chart BOR Bulanan (dari API rateTahun) ──────────────────────────
        $bulanLabel = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',
                       7=>'Jul',8=>'Ags',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];

        $chartBOR = collect(range(1, 12))->map(function ($m) use ($rateTahun, $bulanLabel) {
            $row = $rateTahun->firstWhere('bulan', $m);
            return (object) [
                'bulan' => $bulanLabel[$m],
                'bor'   => $row ? round($row->bor, 2) : 0,
            ];
        });

        // ── Chart Trend Harian ──────────────────────────────────────────────
        // note : ganti dengan $this->api->getTrendHarian() jika endpoint tersedia
        $trendHarian = $this->dummyTrendHarian();

        // ── Chart Triage IGD ────────────────────────────────────────────────
        // note : ganti dengan $this->api->getTriageIGD() jika endpoint tersedia
        $triageIGD = $this->dummyTriage();

        // ── Build chart avlos, toi, bto ─
        $chartAvlos = collect(range(1, 12))->map(function ($m) use ($rateTahun, $bulanLabel) {
            $row = $rateTahun->firstWhere('bulan', $m);
            return (object) [
                'bulan' => $bulanLabel[$m],
                'avlos' => $row ? round($row->avlos, 2) : 0,
                'toi'   => $row ? round($row->toi,   2) : 0,
                'bto'   => $row ? round($row->bto * 30, 2) : 0,
            ];
        });

        // ── Standar ────────────────────────────────────────────────────────────
        $standar = [
            'bor_min' => 60,  'bor_max' => 85,
            'los_min' => 6,   'los_max' => 9,
            'toi_min' => 1,   'toi_max' => 3,
            'bto_min' => 40,  'bto_max' => 50,
        ];

        return view('portal.pelayananpasien', compact(
            'bor', 'los', 'toi', 'bto',
            'ringkasanRanap', 'ringkasanRajal', 'ringkasanIGD',
            'chartBOR', 'chartAvlos',
            'tanggalMulai', 'tanggalSelesai', 'tahun', 'standar',
            'isDummy',
            // chart untuk JS
            'trendHarian', 'triageIGD'
        ));
    }

    // ══════════════════════════════════════════════════════════════
    // DETAIL RANAP
    // ══════════════════════════════════════════════════════════════

    public function detailRanap(Request $request)
    {
        $tanggalMulai   = $request->get('dari',   Carbon::now()->startOfMonth()->format('Y-m-d'));
        $tanggalSelesai = $request->get('sampai',  Carbon::now()->format('Y-m-d'));
        $tahun          = (int) $request->get('tahun', Carbon::now()->year);

        try {
            $rateTahun = $this->api->getRateTahun($tahun);
            // Filter bulan sesuai rentang tanggal
            $bulanDalam = $this->getBulanDalamRentang($tanggalMulai, $tanggalSelesai);
            $dataRanap  = $rateTahun->filter(fn($r) => empty($bulanDalam) || in_array($r->bulan, $bulanDalam));
        } catch (\Exception $e) {
            $dataRanap = collect();
        }

        return view('portal.pelayananpasien-ranap', compact(
            'dataRanap', 'tanggalMulai', 'tanggalSelesai'
        ));
    }

    // ══════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ══════════════════════════════════════════════════════════════

    /** Ambil array nomor bulan yang ada dalam rentang tanggal */
    private function getBulanDalamRentang(string $dari, string $sampai): array
    {
        $start = Carbon::parse($dari);
        $end   = Carbon::parse($sampai);
        $bulan = [];

        while ($start->lte($end)) {
            $bulan[] = $start->month;
            $start->addMonth();
        }

        return array_unique($bulan);
    }

    // ── Dummy data ────

    private function dummyRanap(): array
    {
        return [
            'total_masuk'     => 320,
            'total_keluar'    => 295,
            'masih_dirawat'   => 85,
            'total_meninggal' => 4,
        ];
    }

    private function dummyRajal(): \Illuminate\Support\Collection
    {
        return collect([
            (object)['nama_poli'=>'Poli Umum',          'total_kunjungan'=>412,'pasien_baru'=>120,'pasien_lama'=>292,'bpjs'=>310,'umum'=>102],
            (object)['nama_poli'=>'Poli Penyakit Dalam', 'total_kunjungan'=>340,'pasien_baru'=>95, 'pasien_lama'=>245,'bpjs'=>290,'umum'=>50],
            (object)['nama_poli'=>'Poli Anak',           'total_kunjungan'=>275,'pasien_baru'=>88, 'pasien_lama'=>187,'bpjs'=>210,'umum'=>65],
            (object)['nama_poli'=>'Poli Kandungan',      'total_kunjungan'=>198,'pasien_baru'=>54, 'pasien_lama'=>144,'bpjs'=>160,'umum'=>38],
            (object)['nama_poli'=>'Poli Bedah',          'total_kunjungan'=>156,'pasien_baru'=>42, 'pasien_lama'=>114,'bpjs'=>120,'umum'=>36],
            (object)['nama_poli'=>'Poli Mata',           'total_kunjungan'=>134,'pasien_baru'=>38, 'pasien_lama'=>96, 'bpjs'=>100,'umum'=>34],
            (object)['nama_poli'=>'Poli Gigi',           'total_kunjungan'=>112,'pasien_baru'=>60, 'pasien_lama'=>52, 'bpjs'=>80, 'umum'=>32],
            (object)['nama_poli'=>'Poli THT',            'total_kunjungan'=>98, 'pasien_baru'=>30, 'pasien_lama'=>68, 'bpjs'=>75, 'umum'=>23],
        ]);
    }

    private function dummyIGD(): array
    {
        return [
            'total'            => 187,
            'pulang'           => 102,
            'rawat_inap'       => 75,
            'meninggal'        => 3,
            'avg_waktu_tunggu' => 12,
        ];
    }

    private function dummyTrendHarian(): \Illuminate\Support\Collection
    {
        return collect(range(6, 0))->map(fn($i) => (object)[
            'tanggal' => Carbon::now()->subDays($i)->format('d/m'),
            'ranap'   => rand(28, 45),
            'rajal'   => rand(120, 200),
            'igd'     => rand(15, 35),
        ]);
    }

    private function dummyTriage(): \Illuminate\Support\Collection
    {
        return collect([
            (object)['kategori_triage' => 'Merah (P1)',  'jumlah' => 18],
            (object)['kategori_triage' => 'Kuning (P2)', 'jumlah' => 54],
            (object)['kategori_triage' => 'Hijau (P3)',  'jumlah' => 98],
            (object)['kategori_triage' => 'Hitam (P4)',  'jumlah' => 3],
            (object)['kategori_triage' => 'Biru (Obs)',  'jumlah' => 14],
        ]);
    }
}