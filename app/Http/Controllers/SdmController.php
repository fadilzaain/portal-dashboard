<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SdmController extends Controller
{
    public function index()
    {
        //Total Pegawai
        $totalPegawai = DB::connection('pegawai')->table('karyawan')->count();
        $totalAktif   = DB::connection('pegawai')->table('karyawan')->where('aktif', 'Aktif')->count();
        $totalPensiun = DB::connection('pegawai')->table('karyawan')->where('aktif', 'Pensiun')->count();
        $totalKeluar  = DB::connection('pegawai')->table('karyawan')->whereIn('aktif', ['Keluar', 'Pindah'])->count();

        // status pns atau honorer (yang aktif)
        $statusData = DB::connection('pegawai')->table('karyawan')
            ->where('aktif', 'Aktif')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        
        $totalPns     = $statusData['PNS']     ?? 0;
        $totalHonorer = $statusData['Honorer'] ?? 0;
        // $totalCpns    = $statusData['CPNS']    ?? 0;
        foreach ($statusData as $key => $val) {
            if (strtolower($key) === 'pns')     $totalPns     = $val;
            if (strtolower($key) === 'honorer') $totalHonorer = $val;
        }

        //distribusi per unit kerja
        $distribusiUnit = DB::connection('pegawai')->table('karyawan')
            ->where('aktif', 'Aktif')
            ->select(DB::raw('`unit kerja`'), DB::raw('COUNT(*) as total'))
            ->groupBy(DB::raw('`unit kerja`'))
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $unitLabels = $distribusiUnit->pluck('unit kerja')->toArray();
        $unitData   = $distribusiUnit->pluck('total')->toArray();

        //trend 6 bulan
        //Pakai created_at sebagai proxy tanggal masuk.
        //Kalau ada kolom tanggal_masuk di tabel karyawan, ganti created_at di bawah.
        // $trendLabels  = [];
        // $trendPns     = [];
        // $trendHonorer = [];

        // for ($i = 5; $i >= 0; $i--) {
        //     $bulan = Carbon::now()->subMonths($i);
        //     $trendLabels[] = $bulan->translatedFormat('M Y');

        //     $base = DB::connection('pegawai')->table('karyawan')
        //         ->whereYear(DB::raw('`TMT Masuk`'), $bulan->year)
        //         ->whereMonth(DB::raw('`TMT Masuk`'), $bulan->month);

        //     $trendPns[]     = (clone $base)->where('status', 'PNS')->count();
        //     $trendHonorer[] = (clone $base)->where('status', 'Honorer')->count();
        // }

        //distribusi gender
        $genderData = DB::connection('pegawai')->table('karyawan')
            ->where('aktif', 'Aktif')
            ->select('kelamin', DB::raw('COUNT(*) as total'))
            ->groupBy('kelamin')
            ->pluck('total', 'kelamin');

        $lakiLaki  = $genderData['Laki-Laki']  ?? 0;
        $perempuan = $genderData['Perempuan']  ?? 0;
        foreach ($genderData as $key => $val) {
            $k = strtolower($key);
            if (in_array($k, ['l', 'laki-laki', 'laki laki', 'pria'])) $lakiLaki  = $val;
            if (in_array($k, ['p', 'perempuan', 'wanita']))             $perempuan = $val;
        }

        return view('portal.sdm', compact(
            'totalPegawai', 'totalAktif', 'totalPensiun', 'totalKeluar',
            'totalPns', 'totalHonorer',
            'unitLabels', 'unitData',
            'lakiLaki', 'perempuan'
        ));
    }
}