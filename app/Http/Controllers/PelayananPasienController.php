<?php

namespace App\Http\Controllers;

use App\Models\PelayananPasien;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PelayananPasienController extends Controller
{
    public function index(Request $request)
    {
        $tanggalMulai   = $request->get('dari', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $tanggalSelesai = $request->get('sampai', Carbon::now()->format('Y-m-d'));
        $tahun          = $request->get('tahun', Carbon::now()->year);

        $isDummy = false;

        try {
            $bor = PelayananPasien::hitungBOR($tanggalMulai, $tanggalSelesai);
            $los = PelayananPasien::hitungLOS($tanggalMulai, $tanggalSelesai);
            $toi = PelayananPasien::hitungTOI($tanggalMulai, $tanggalSelesai);
            $bto = PelayananPasien::hitungBTO($tanggalMulai, $tanggalSelesai);

            $ringkasanRanap = PelayananPasien::getRingkasanRanap($tanggalMulai, $tanggalSelesai);
            $ringkasanRajal = PelayananPasien::getRingkasanRajal($tanggalMulai, $tanggalSelesai);
            $ringkasanIGD   = PelayananPasien::getRingkasanIGD($tanggalMulai, $tanggalSelesai);

            $trendHarian = PelayananPasien::getTrendHarian($tanggalMulai, $tanggalSelesai);
            $trendBOR    = PelayananPasien::getTrendBORBulanan($tahun);
            $triageIGD   = PelayananPasien::getIGDPerTriage($tanggalMulai, $tanggalSelesai);

        } catch (\Exception $e) {
            $isDummy = true;

            // ── DUMMY: Indikator Mutu ──
            $bor = 72.5;
            $los = 7.2;
            $toi = 2.1;
            $bto = 45.0;

            // ── DUMMY: Ringkasan Ranap ──
            $ringkasanRanap = [
                'total_masuk'    => 320,
                'total_keluar'   => 295,
                'masih_dirawat'  => 85,
                'total_meninggal'=> 4,
            ];

            // ── DUMMY: Ringkasan Rajal ──
            $ringkasanRajal = collect([
                (object)['nama_poli'=>'Poli Umum',       'total_kunjungan'=>412, 'pasien_baru'=>120, 'pasien_lama'=>292, 'bpjs'=>310, 'umum'=>102],
                (object)['nama_poli'=>'Poli Anak',        'total_kunjungan'=>275, 'pasien_baru'=>88,  'pasien_lama'=>187, 'bpjs'=>210, 'umum'=>65],
                (object)['nama_poli'=>'Poli Kandungan',   'total_kunjungan'=>198, 'pasien_baru'=>54,  'pasien_lama'=>144, 'bpjs'=>160, 'umum'=>38],
                (object)['nama_poli'=>'Poli Penyakit Dalam','total_kunjungan'=>340,'pasien_baru'=>95, 'pasien_lama'=>245, 'bpjs'=>290, 'umum'=>50],
                (object)['nama_poli'=>'Poli Bedah',       'total_kunjungan'=>156, 'pasien_baru'=>42,  'pasien_lama'=>114, 'bpjs'=>120, 'umum'=>36],
                (object)['nama_poli'=>'Poli Mata',        'total_kunjungan'=>134, 'pasien_baru'=>38,  'pasien_lama'=>96,  'bpjs'=>100, 'umum'=>34],
                (object)['nama_poli'=>'Poli Gigi',        'total_kunjungan'=>112, 'pasien_baru'=>60,  'pasien_lama'=>52,  'bpjs'=>80,  'umum'=>32],
                (object)['nama_poli'=>'Poli THT',         'total_kunjungan'=>98,  'pasien_baru'=>30,  'pasien_lama'=>68,  'bpjs'=>75,  'umum'=>23],
            ]);

            // ── DUMMY: Ringkasan IGD ──
            $ringkasanIGD = [
                'total'           => 187,
                'pulang'          => 102,
                'rawat_inap'      => 75,
                'meninggal'       => 3,
                'avg_waktu_tunggu'=> 12,
            ];

            // ── DUMMY: Trend Harian (7 hari terakhir) ──
            $trendHarian = collect(
                collect(range(6, 0))->map(fn($i) => (object)[
                    'tanggal' => Carbon::now()->subDays($i)->format('d/m'),
                    'ranap'   => rand(28, 45),
                    'rajal'   => rand(120, 200),
                    'igd'     => rand(15, 35),
                ])
            );

            // ── DUMMY: Trend BOR Bulanan ──
            $bulanLabel = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
            $trendBOR = collect(
                collect(range(0, 11))->map(fn($i) => (object)[
                    'bulan' => $bulanLabel[$i],
                    'bor'   => round(rand(55, 88) + (rand(0,9)/10), 1),
                ])
            );

            // ── DUMMY: Triage IGD ──
            $triageIGD = collect([
                (object)['kategori_triage' => 'Merah (P1)',   'jumlah' => 18],
                (object)['kategori_triage' => 'Kuning (P2)',  'jumlah' => 54],
                (object)['kategori_triage' => 'Hijau (P3)',   'jumlah' => 98],
                (object)['kategori_triage' => 'Hitam (P4)',   'jumlah' => 3],
                (object)['kategori_triage' => 'Biru (Obs)',   'jumlah' => 14],
            ]);
        }

        $chartTrend  = $trendHarian->toJson();
        $chartBOR    = $trendBOR->toJson();
        $chartRajal  = $ringkasanRajal->toJson();
        $chartTriage = $triageIGD->toJson();

        $standar = [
            'bor_min' => 60, 'bor_max' => 85,
            'los_min' => 6,  'los_max' => 9,
            'toi_min' => 1,  'toi_max' => 3,
            'bto_min' => 40, 'bto_max' => 50,
        ];

        return view('portal.pelayananpasien', compact(
            'bor', 'los', 'toi', 'bto',
            'ringkasanRanap', 'ringkasanRajal', 'ringkasanIGD',
            'chartTrend', 'chartBOR', 'chartRajal', 'chartTriage',
            'tanggalMulai', 'tanggalSelesai', 'tahun', 'standar',
            'isDummy'
        ));
    }

    public function detailRanap(Request $request)
    {
        $tanggalMulai   = $request->get('dari', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $tanggalSelesai = $request->get('sampai', Carbon::now()->format('Y-m-d'));

        try {
            $dataRanap = PelayananPasien::getDataRanap($tanggalMulai, $tanggalSelesai);
        } catch (\Exception $e) {
            $dataRanap = collect();
        }

        return view('portal.pelayananpasien-ranap', compact(
            'dataRanap', 'tanggalMulai', 'tanggalSelesai'
        ));
    }
}