<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PelayananPasien extends Model
{
    protected $connection = 'erm_rs';
    protected $table      = 'sensus_harian';
    public    $timestamps = false;

    /**
     * Ambil data harian sensus_harian untuk halaman detail ranap.
     * Semua kolom indikator tersedia di sini (bor, avlos, toi, dst).
     */
    public static function getDataRanap(string $dari, string $sampai)
    {
        return DB::connection('erm_rs')
            ->table('sensus_harian')
            ->whereBetween('tanggal', [$dari, $sampai])
            ->select([
                'tanggal',
                'pasien_awal',
                'pasien_masuk',
                'pasien_pindahan',
                'total_pasien_masuk',
                'total_pasien_keluar',
                'total_pasien_mati',
                'sisa_pasien',
                'lama_dirawat',
                'jumlah_tt',
                'bor',
                'avlos',
                'toi',
                'bto',
                'ndr',
                'gdr',
            ])
            ->orderBy('tanggal')
            ->get();
    }
}