<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PelayananPasien extends Model
{
    //dummy
    protected $connection = 'erm_rs';
    protected $table      = 'sensus_harian';
    public    $timestamps = false;

    // ============================================================
    // Ambil data harian sensus_harian untuk halaman detail ranap.
    // ============================================================
    public static function getDataRanap(string $dari, string $sampai)
    {
        return DB::connection('erm_rs')
            ->table('sensus_harian')
            ->whereBetween('tanggal', [$dari, $sampai])
            ->select(['tanggal','pasien_awal','pasien_masuk','pasien_pindahan',
                'total_pasien_masuk','total_pasien_keluar','total_pasien_mati',
                'sisa_pasien','lama_dirawat','jumlah_tt','bor','avlos',
                'toi','bto','ndr','gdr'])
            ->orderBy('tanggal')
            ->get();
    }

    // ============================================================
    // borlostoiall_thn dashi
    // ============================================================
    public static function getByTahun(int $tahun)
    {
        return DB::connection('dashi')
            ->table('borlosttoiall_thn')
            ->where('tahun', $tahun)
            ->orderBy('bulan')
            ->get();
    }

    public static function getByRentang(int $tahun, array $bulanList)
    {
        return DB::connection('dashi')
            ->table('borlosttoiall_thn')
            ->where('tahun', $tahun)
            ->whereIn('bulan', $bulanList)
            ->orderBy('bulan')
            ->get();
    }

    public static function getYTD(int $tahun, int $sampaibulan)
    {
        return DB::connection('dashi')
            ->table('borlosttoiall_thn')
            ->where('tahun', $tahun)
            ->where('bulan', '<=', $sampaibulan)
            ->where('bor', '>', 0)
            ->orderBy('bulan')
            ->get();
    }

    // ============================================================
    // Kunjungan Hari ini db dashi
    // ============================================================
    public static function getKunjunganHariIni(): array
    {
        $today = now()->format('Y-m-d');

        // Coba ambil data hari ini dulu
        $row = DB::connection('dashi')
            ->table('borlosttoiall')
            ->whereDate('tanggalAll', $today)
            ->first();

        // Kalau belum ada (data belum di-input hari ini), ambil tanggal terakhir
        if (!$row) {
            $row = DB::connection('dashi')
                ->table('borlosttoiall')
                ->orderByDesc('tanggalAll')
                ->first();
        }

        if (!$row) {
            return ['total' => 0, 'krs_hidup' => 0, 'mati' => 0, 'krs_mrs' => 0, 'tanggal' => null];
        }

        $krsHidup = (int) ($row->pasien_krs_hidul ?? 0);
        $mati     = (int) ($row->pasien_mati_tot  ?? 0);
        $krsMrs   = (int) ($row->pasien_krs_mrs   ?? 0);

        return [
            'total'     => $krsHidup + $mati + $krsMrs,
            'krs_hidup' => $krsHidup,
            'mati'      => $mati,
            'krs_mrs'   => $krsMrs,
            'tanggal'   => $row->tanggalAll,
        ];
    }

    //==============================================
    // Monitoring IGD 
    // Todo : ganti query dummy dengan tabel IGD yang sudah terintegrasi
    //==============================================
    public static function getMonitoringIGD(): array
    {
        try {
            // persiapan struktur:
        //
        // $today = now()->format('Y-m-d');
        // $rows  = DB::connection('dashi')
        //     ->table('igd_kunjungan')
        //     ->whereDate('tgl_masuk', $today)
        //     ->get();
        //
        // $triage = $rows->whereNotNull('kode_triage')->groupBy('kode_triage')
        //     ->map(fn($g) => $g->count());
        //
        // return [
        //     'terisi'  => $rows->whereIn('status', ['aktif','observasi'])->count(),
        //     'antri'   => $rows->where('kode_triage', null)->count(),
        //     'masuk'   => $rows->count(),
        //     'triage'  => [
        //         'p1' => $triage['P1'] ?? 0,
        //         'p2' => $triage['P2'] ?? 0,
        //         'p3' => $triage['P3'] ?? 0,
        //         'p4' => $triage['P4'] ?? 0,
        //         'p5' => $triage['P5'] ?? 0,
        //     ],
        //     'pasien'  => $rows->map(fn($r) => [
        //         'nama'      => $r->nama_pasien,
        //         'jam_masuk' => \Carbon\Carbon::parse($r->tgl_masuk)->format('H:i'),
        //         'triage'    => $r->kode_triage ?? 'Antri',
        //         'status'    => $r->status_pasien,
        //         'outcome'   => $r->outcome ?? 'Proses',
        //     ])->values()->toArray(),
        // ];

        //Data dummy sementara
        return [
            'terisi'    => 0,
            'antri'     => 0,
            'masuk'     => 0,
            'triage'    => ['p1' => 0, 'p2' => 0, 'p3' => 0, 'p4' => 0, 'p5' => 0,],
            'pasien'    => [],
        ];
        } catch (\Exception $e) {
            return [
                'terisi' => 0,
                'antri'  => 0,
                'masuk'  => 0,
                'triage' => ['p1' => 0, 'p2' => 0, 'p3' => 0, 'p4' => 0, 'p5' => 0],
                'pasien' => [],
            ];
        }
    }
}