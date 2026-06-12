<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class PelayananPasien extends Model
{
    protected $connection = 'erm_rs';
    protected $table      = 'sensus_harian';
    public    $timestamps = false;

    // =========================================================
    // RAWAT INAP DATA SEMENTARA
    // =========================================================
    public static function getDataRanap(string $dari, string $sampai)
    {
        return DB::connection('erm_rs')
            ->table('sensus_harian')
            ->whereBetween('tanggal', [$dari, $sampai])
            ->select([
                'tanggal', 'pasien_awal', 'pasien_masuk', 'pasien_pindahan',
                'total_pasien_masuk', 'total_pasien_keluar', 'total_pasien_mati',
                'sisa_pasien', 'lama_dirawat', 'jumlah_tt',
                'bor', 'avlos', 'toi', 'bto', 'ndr', 'gdr',
            ])
            ->orderBy('tanggal')
            ->get();
    }

    // =========================================================
    // INDIKATOR TAHUNAN — borlosttoiall_thn (dashi)
    // =========================================================

    //tahun
    public static function getByTahun(int $tahun)
    {
        return DB::connection('dashi')
            ->table('borlosttoiall_thn')
            ->where('tahun', $tahun)
            ->orderBy('bulan')
            ->get();
    }

    //bulan
    public static function getByRentang(int $tahun, array $bulanList)
    {
        return DB::connection('dashi')
            ->table('borlosttoiall_thn')
            ->where('tahun', $tahun)
            ->whereIn('bulan', $bulanList)
            ->orderBy('bulan')
            ->get();
    }

   //year to date
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

    // =========================================================
    // KUNJUNGAN HARIAN — borlosttoiall (dashi)
    // =========================================================

    //Data kunjungan hari ini. Fallback ke tanggal terakhir jika belum ada input hari ini.
    public static function getKunjunganHariIni(): array
    {
        $today = now()->format('Y-m-d');

        $row = DB::connection('dashi')
            ->table('borlosttoiall')
            ->whereDate('tanggalAll', $today)
            ->first()
            ?? DB::connection('dashi')
                ->table('borlosttoiall')
                ->orderByDesc('tanggalAll')
                ->first();

        if (!$row) {
            return self::emptyKunjungan();
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

    private static function emptyKunjungan(): array
    {
        return ['total' => 0, 'krs_hidup' => 0, 'mati' => 0, 'krs_mrs' => 0, 'tanggal' => null];
    }

    // =========================================================
    // KUNJUNGAN REKAP — kunjungan_rekap (dashi)
    // =========================================================
 
    public static function getKunjunganRekap(int $tahun)
    {
        return DB::connection('dashi')
            ->table('kunjungan_rekap')
            ->where('tahun', $tahun)
            ->orderBy('bulan')
            ->get();
    }
 

    // =========================================================
    // MONITORING IGD — igd_rekap + igd_pasien (dashi / erm_rs)
    // =========================================================

    public static function getMonitoringIGD(): array
    {
        try {
            $row = DB::connection('dashi')
                ->table('igd_rekap')
                ->orderByDesc('tanggal')
                ->first();

            if (!$row) return self::emptyIGD();

            return [
                'terisi'     => 0,   
                'masuk'      => 0,   
                'antri'      => (int) ($row->triage ?? 0),  
                'triage'     => [
                    'p1' => (int) ($row->p1 ?? 0),
                    'p2' => (int) ($row->p2 ?? 0),
                    'p3' => (int) ($row->p3 ?? 0),
                    'p4' => 0,  
                    'p5' => 0,  
                ],
                'pasien'     => [],  
                'diperbarui' => $row->tanggal ?? null,
            ];

        } catch (\Exception $e) {
            \Log::warning('[IGD] getMonitoringIGD gagal: ' . $e->getMessage());
            return self::emptyIGD();
        }
    }

    private static function emptyIGD(): array
    {
        return [
            'terisi'     => 0,
            'masuk'      => 0,
            'antri'      => 0,
            'triage'     => ['p1' => 0, 'p2' => 0, 'p3' => 0, 'p4' => 0, 'p5' => 0],
            'pasien'     => [],
            'diperbarui' => null,
        ];
    }
}