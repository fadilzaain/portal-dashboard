<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KeuanganController extends Controller
{
    private $bulanLabel = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

   //API View
    public function index()
    {
        $tahun     = request('tahun', now()->year);
        $tahunList = range(now()->year, now()->year - 4);
        return view('portal.keuangan', compact('tahun', 'tahunList'));
    }

    //API Trend
    public function apiTrend(Request $request)
    {
        $tahun    = (int) ($request->tahun ?? now()->year);
        $bulanIni = (int) ($request->bulan ?? now()->month);         
        $tahunYoY = $tahun - 1;                                      

        //Belanja dari mysql2 (Cheque)
        $targetPerBulan = DB::connection('mysql2')->table('transaksi')
            ->selectRaw('MONTH(tanggal) as bulan, SUM(jumlah) as total')
            ->whereYear('tanggal', $tahun)->where('status', 9)
            ->groupByRaw('MONTH(tanggal)')
            ->pluck('total', 'bulan')
            ->map(fn($v) => (float)$v);

        $realisasiPerBulan = DB::connection('mysql2')->table('cheque')
            ->selectRaw('MONTH(tanggal) as bulan, SUM(jumlah) as total')
            ->whereYear('tanggal', $tahun)
            ->groupByRaw('MONTH(tanggal)')
            ->pluck('total', 'bulan')
            ->map(fn($v) => (float)$v);

        //Pendapatan dari mysql3 (Belanja)
        $pendapatanPerBulan = DB::connection('mysql3')->table('tr_mutasirekbank')
            ->selectRaw('MONTH(effective_date) as bulan, SUM(credit) as total')
            ->whereYear('effective_date', $tahun)
            ->whereNotNull('credit')->where('credit', '>', 0)
            ->groupByRaw('MONTH(effective_date)')
            ->pluck('total', 'bulan')
            ->map(fn($v) => (float)$v);

        $belanja = collect(range(1, 12))->map(fn($i) => [
            'label'     => $this->bulanLabel[$i - 1],
            'target'    => $targetPerBulan[$i]    ?? 0.0,
            'realisasi' => $realisasiPerBulan[$i] ?? 0.0,
        ]);

        $pendapatan = collect(range(1, 12))->map(fn($i) => [
            'label'     => $this->bulanLabel[$i - 1],
            'target'    => 0.0,
            'realisasi' => $pendapatanPerBulan[$i] ?? 0.0,
        ]);

        // MOM perbandingan bulan ditahun terbaru v bulan di tahun sebelumnya
        $mom = [
            'pendapatan_bulan_ini'  => $pendapatanPerBulan[$bulanIni] ?? 0.0,
            'pendapatan_bulan_lalu' => (float) DB::connection('mysql3')->table('tr_mutasirekbank')
                ->whereYear('effective_date', $tahunYoY)                 
                ->whereMonth('effective_date', $bulanIni)                
                ->whereNotNull('credit')->where('credit', '>', 0)
                ->sum('credit'),
            'belanja_bulan_ini'     => $realisasiPerBulan[$bulanIni]   ?? 0.0,
            'belanja_bulan_lalu'    => (float) DB::connection('mysql2')->table('cheque')
                ->whereYear('tanggal', $tahunYoY)                        
                ->whereMonth('tanggal', $bulanIni)                       
                ->sum('jumlah'),
        ];

        return response()->json(compact('pendapatan', 'belanja', 'mom'));
    }

    //API Harian
    public function apiHarian(Request $request)
    {
        $tahun = $request->tahun ?? now()->year;
        $bulan = $request->bulan ?? now()->month;

        $realisasiHarian = DB::connection('mysql2')->table('cheque')
            ->selectRaw('DAY(tanggal) as hari, SUM(jumlah) as total')
            ->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan)
            ->groupByRaw('DAY(tanggal)')
            ->pluck('total', 'hari');

        $pendapatanHarian = DB::connection('mysql3')->table('tr_mutasirekbank')
            ->selectRaw('DAY(effective_date) as hari, SUM(credit) as total')
            ->whereYear('effective_date', $tahun)->whereMonth('effective_date', $bulan)
            ->whereNotNull('credit')->where('credit', '>', 0)
            ->groupByRaw('DAY(effective_date)')
            ->pluck('total', 'hari');

        $semuaHari = $realisasiHarian->keys()
            ->merge($pendapatanHarian->keys())
            ->unique()->sort();

        $hari = $semuaHari->map(fn($h) => [
            'label'      => (string)$h,
            'pendapatan' => (float)($pendapatanHarian[$h] ?? 0),
            'belanja'    => (float)($realisasiHarian[$h]  ?? 0),
        ])->values();

        return response()->json([
            'hari'  => $hari,
            'bulan' => (int)$bulan,
            'tahun' => (int)$tahun,
        ]);
    }

    //API Unit
    public function apiUnit(Request $request)
    {
        $tahun = $request->tahun ?? now()->year;
        $bulan = $request->bulan ?? now()->month;

        $rows = DB::connection('mysql2')->table('cheque')
            ->selectRaw('keterangan as unit, SUM(jumlah) as realisasi')
            ->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan)
            ->groupBy('keterangan')->orderByDesc('realisasi')
            ->get();

        $total = $rows->sum('realisasi');

        $units = $rows->map(fn($r) => [
            'unit'          => $r->unit,
            'realisasi'     => (float)$r->realisasi,
            'pct_dari_total'=> $total > 0 ? round(($r->realisasi / $total) * 100, 1) : 0,
        ])->values();

        return response()->json([
            'units'      => $units,
            'total_bulan'=> (float)$total,
        ]);
    }
}