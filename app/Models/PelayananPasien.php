<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PelayananPasien extends Model
{
    protected $connection = 'erm_rs';
    protected $table      = 'sensus_harian'; // janlup sesuaikan nama tabel di DB
    public    $timestamps = false;

    // ══════════════════════════════════════════════════════════════
    // HELPER PRIVATE
    // ══════════════════════════════════════════════════════════════

    // Base query sensus_harian dengan filter tanggal
    private static function baseQuery(string $dari, string $sampai)
    {
        return DB::connection('erm_rs')
            ->table('sensus_harian')
            ->whereBetween('tanggal', [$dari, $sampai]);
    }

    // ══════════════════════════════════════════════════════════════
    // INDIKATOR MUTU
    // ══════════════════════════════════════════════════════════════

    // BOR (Bed Occupancy Rate) — rata-rata dari kolom bor harian
    // Rumus: AVG(bor) selama periode
  
    public static function hitungBOR(string $dari, string $sampai): float
    {
        $result = self::baseQuery($dari, $sampai)
            ->selectRaw('AVG(bor) as nilai')
            ->first();

        return round($result->nilai ?? 0, 2);
    }

    
    //  LOS (Length of Stay / AVLOS) — rata-rata dari kolom avlos harian
    //  Bisa pakai kolom avlos atau avlos_rumus sesuai kebutuhan

    public static function hitungLOS(string $dari, string $sampai): float
    {
        $result = self::baseQuery($dari, $sampai)
            ->selectRaw('AVG(avlos) as nilai') // ganti avlos_rumus jika akan pakai rumus
            ->first();

        return round($result->nilai ?? 0, 2);
    }

    
    // TOI (Turn Over Interval) — rata-rata dari kolom toi harian
    public static function hitungTOI(string $dari, string $sampai): float
    {
        $result = self::baseQuery($dari, $sampai)
            ->selectRaw('AVG(toi) as nilai')
            ->first();

        return round($result->nilai ?? 0, 2);
    }

    
    // BTO (Bed Turn Over) — SUM(total_pasien_keluar) / AVG(jumlah_tt)
    // Dari data: BTO per hari = pasien keluar / TT, lalu diakumulasi
    public static function hitungBTO(string $dari, string $sampai): float
    {
        $result = self::baseQuery($dari, $sampai)
            ->selectRaw('SUM(total_pasien_keluar) as total_keluar, AVG(jumlah_tt) as rata_tt')
            ->first();

        $tt = $result->rata_tt ?? 0;
        if ($tt == 0) return 0;

        return round($result->total_keluar / $tt, 2);
    }

    // ══════════════════════════════════════════════════════════════
    // RINGKASAN UNIT
    // ══════════════════════════════════════════════════════════════

    public static function getRingkasanRanap(string $dari, string $sampai): array
    {
        $result = self::baseQuery($dari, $sampai)
            ->selectRaw('
                SUM(pasien_masuk)       as total_masuk,
                SUM(total_pasien_keluar) as total_keluar,
                SUM(total_pasien_mati)  as total_meninggal,
                AVG(sisa_pasien)        as masih_dirawat
            ')
            ->first();

        return [
            'total_masuk'     => (int)   ($result->total_masuk     ?? 0),
            'total_keluar'    => (int)   ($result->total_keluar    ?? 0),
            'total_meninggal' => (int)   ($result->total_meninggal ?? 0),
            'masih_dirawat'   => (int) round($result->masih_dirawat ?? 0),
        ];
    }
    
    //  Ringkasan Rawat Jalan per Poli
    //  Sesuaikan nama tabel dan kolom di bawah ini
    //  Return: Collection of objects
    public static function getRingkasanRajal(string $dari, string $sampai)
    {
        return DB::connection('erm_rs')
            ->table('kunjungan_rajal')          
            ->whereBetween('tanggal', [$dari, $sampai])
            ->selectRaw('
                nama_poli,
                SUM(total_kunjungan) as total_kunjungan,
                SUM(pasien_baru)     as pasien_baru,
                SUM(pasien_lama)     as pasien_lama,
                SUM(bpjs)            as bpjs,
                SUM(umum)            as umum
            ')
            ->groupBy('nama_poli')
            ->orderByDesc('total_kunjungan')
            ->get();
    }

    
    public static function getRingkasanIGD(string $dari, string $sampai): array
    {
        $result = DB::connection('erm_rs')
            ->table('kunjungan_igd')           
            ->whereBetween('tanggal', [$dari, $sampai])
            ->selectRaw('
                COUNT(*)                as total,
                SUM(pulang)             as pulang,
                SUM(rawat_inap)         as rawat_inap,
                SUM(meninggal)          as meninggal,
                AVG(waktu_tunggu_menit) as avg_waktu_tunggu
            ')
            ->first();

        return [
            'total'            => (int)   ($result->total            ?? 0),
            'pulang'           => (int)   ($result->pulang           ?? 0),
            'rawat_inap'       => (int)   ($result->rawat_inap       ?? 0),
            'meninggal'        => (int)   ($result->meninggal        ?? 0),
            'avg_waktu_tunggu' => (int) round($result->avg_waktu_tunggu ?? 0),
        ];
    }

    // ══════════════════════════════════════════════════════════════
    // DATA CHART
    // ══════════════════════════════════════════════════════════════

    // Trend Kunjungan Harian (Ranap, Rajal, IGD)
    // Untuk Ranap: ambil dari sensus_harian (sisa_pasien = pasien yang dirawat hari itu)
    // Untuk Rajal & IGD: join atau subquery ke tabel masing-masing
    // Return: Collection, field: tanggal, ranap, rajal, igd
    public static function getTrendHarian(string $dari, string $sampai)
    {
        $ranap = self::baseQuery($dari, $sampai)
            ->selectRaw("
                DATE_FORMAT(tanggal, '%d/%m') as tgl,
                tanggal,
                sisa_pasien as jumlah
            ")
            ->orderBy('tanggal')
            ->get()
            ->keyBy('tgl');

        $rajal = DB::connection('erm_rs')
            ->table('kunjungan_rajal')      
            ->whereBetween('tanggal', [$dari, $sampai])
            ->selectRaw("DATE_FORMAT(tanggal, '%d/%m') as tgl, SUM(total_kunjungan) as jumlah")
            ->groupBy('tanggal')
            ->get()
            ->keyBy('tgl');

        $igd = DB::connection('erm_rs')
            ->table('kunjungan_igd')          
            ->whereBetween('tanggal', [$dari, $sampai])
            ->selectRaw("DATE_FORMAT(tanggal, '%d/%m') as tgl, COUNT(*) as jumlah")
            ->groupBy('tanggal')
            ->get()
            ->keyBy('tgl');

        return $ranap->map(function ($row) use ($rajal, $igd) {
            return (object) [
                'tanggal' => $row->tgl,
                'ranap'   => (int) $row->jumlah,
                'rajal'   => (int) ($rajal[$row->tgl]->jumlah ?? 0),
                'igd'     => (int) ($igd[$row->tgl]->jumlah   ?? 0),
            ];
        })->values();
    }

    public static function getTrendBORBulanan(int $tahun)
    {
        $bulanLabel = [
            1=>'Jan', 2=>'Feb', 3=>'Mar', 4=>'Apr',
            5=>'Mei', 6=>'Jun', 7=>'Jul', 8=>'Ags',
            9=>'Sep', 10=>'Okt', 11=>'Nov', 12=>'Des',
        ];

        $data = DB::connection('erm_rs')
            ->table('sensus_harian')
            ->whereYear('tanggal', $tahun)
            ->selectRaw('MONTH(tanggal) as bulan_angka, AVG(bor) as bor')
            ->groupByRaw('MONTH(tanggal)')
            ->orderByRaw('MONTH(tanggal)')
            ->get()
            ->keyBy('bulan_angka');

        return collect(range(1, 12))->map(function ($m) use ($data, $bulanLabel) {
            return (object) [
                'bulan' => $bulanLabel[$m],
                'bor'   => round($data[$m]->bor ?? 0, 2),
            ];
        });
    }

    // Distribusi IGD per Triage
    // Sesuaikan nama tabel & kolom triage di DB erm_rs
    // Return: Collection, field: kategori_triage, jumlah
    public static function getIGDPerTriage(string $dari, string $sampai)
    {
        return DB::connection('erm_rs')
            ->table('kunjungan_igd')           
            ->whereBetween('tanggal', [$dari, $sampai])
            ->selectRaw('kategori_triage, COUNT(*) as jumlah') 
            ->groupBy('kategori_triage')
            ->orderByDesc('jumlah')
            ->get();
    }

    // ══════════════════════════════════════════════════════════════
    // DETAIL RANAP 
    // ══════════════════════════════════════════════════════════════

    public static function getDataRanap(string $dari, string $sampai)
    {
        return self::baseQuery($dari, $sampai)
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