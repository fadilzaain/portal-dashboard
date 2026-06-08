<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PelayananPasien extends Model
{
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
    // Query ke DB dashi — borlostoiall_thn
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
}