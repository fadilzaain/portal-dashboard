<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KlaimBpjsController extends Controller
{
    private function db()
    {
        return DB::connection('klaim_bpjs');
    }

    /**
     * Resolve date range dari request
     */
    private function resolveDateRange(Request $request): array
    {
        $now = Carbon::now();
        if ($request->filled('from') && $request->filled('to')) {
            return [
                Carbon::parse($request->from)->startOfDay(),
                Carbon::parse($request->to)->endOfDay(),
            ];
        }
        return match ($request->get('period', 'monthly')) {
            'weekly' => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()],
            'yearly' => [$now->copy()->startOfYear(),            $now->copy()->endOfYear()],
            default  => [$now->copy()->startOfMonth(),           $now->copy()->endOfMonth()],
        };
    }

    /**
     * Resolve previous date range (untuk delta %)
     */
    private function resolvePrevDateRange(Request $request): array
    {
        $now = Carbon::now();
        if ($request->filled('from') && $request->filled('to')) {
            $from = Carbon::parse($request->from);
            $to   = Carbon::parse($request->to);
            $diff = $from->diffInDays($to) + 1;
            return [
                $from->copy()->subDays($diff)->startOfDay(),
                $from->copy()->subDays(1)->endOfDay(),
            ];
        }
        return match ($request->get('period', 'monthly')) {
            'weekly' => [$now->copy()->subDays(13)->startOfDay(), $now->copy()->subDays(7)->endOfDay()],
            'yearly' => [$now->copy()->subYear()->startOfYear(),  $now->copy()->subYear()->endOfYear()],
            default  => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
        };
    }


    //  Hitung delta persen
    private function calcDelta(int|float $current, int|float $prev): float
    {
        if ($prev == 0) return $current > 0 ? 100.0 : 0.0;
        return round((($current - $prev) / $prev) * 100, 1);
    }

    // Merge 2 stdClass objects dengan menjumlahkan field numerik
    private function merge(?object $a, ?object $b): object
    {
        $result = [];
        $keys = array_unique(array_merge(
            array_keys((array) $a),
            array_keys((array) $b)
        ));
        foreach ($keys as $key) {
            $va = $a->$key ?? 0;
            $vb = $b->$key ?? 0;
            $result[$key] = (is_numeric($va) ? (float) $va : 0)
                          + (is_numeric($vb) ? (float) $vb : 0);
        }
        return (object) $result;
    }

    //  Merge chart rows dari 2 tabel berdasarkan period_key
   
    private function mergeChartRows($rinap, $rjalan): \Illuminate\Support\Collection
    {
        $map = [];

        foreach ($rinap as $r) {
            $map[$r->period_key] = [
                'period_key'  => $r->period_key,
                'terbayar'    => (int) $r->terbayar,
                'pending'     => (int) $r->pending,
                'tidak_layak' => (int) $r->tidak_layak,
                'diproses'    => (int) $r->diproses,
            ];
        }

        foreach ($rjalan as $r) {
            if (isset($map[$r->period_key])) {
                $map[$r->period_key]['terbayar']    += (int) $r->terbayar;
                $map[$r->period_key]['pending']     += (int) $r->pending;
                $map[$r->period_key]['tidak_layak'] += (int) $r->tidak_layak;
                $map[$r->period_key]['diproses']    += (int) $r->diproses;
            } else {
                $map[$r->period_key] = [
                    'period_key'  => $r->period_key,
                    'terbayar'    => (int) $r->terbayar,
                    'pending'     => (int) $r->pending,
                    'tidak_layak' => (int) $r->tidak_layak,
                    'diproses'    => (int) $r->diproses,
                ];
            }
        }

        return collect(array_values($map))->map(fn($r) => (object) $r);
    }

    // Fill gap periode tanpa data = 0
     
    private function fillGaps($rows, Carbon $from, Carbon $to, string $period, string $groupFormat): \Illuminate\Support\Collection
    {
        $map     = $rows->keyBy('period_key');
        $allKeys = collect();
        $cursor  = $from->copy();
        $fmt     = str_contains($groupFormat, 'd') ? 'Y-m-d' : 'Y-m';

        while ($cursor->lte($to)) {
            $allKeys->push($cursor->format($fmt));
            match ($period) {
                'yearly' => $cursor->addMonth(),
                default  => $cursor->addDay(),
            };
        }

        return $allKeys->map(fn($key) => $map->has($key) ? $map[$key] : (object) [
            'period_key'  => $key,
            'terbayar'    => 0,
            'pending'     => 0,
            'tidak_layak' => 0,
            'diproses'    => 0,
        ]);
    }

    /* ══════════════════════════════════════════
      keterangan status : 
         LIKE '1%' → Diproses (Proses Verifikasi)
         LIKE '2%' → Pending  (Klaim Pending)
         LIKE '3%' → Terbayar (Klaim)
         LIKE '4%' → Tidak Layak
    ══════════════════════════════════════════ */

    public function summary(Request $request): JsonResponse
    {
        [$from, $to]         = $this->resolveDateRange($request);
        [$prevFrom, $prevTo] = $this->resolvePrevDateRange($request);

        // Rinap (filter dari tglPulang)
        $aggRinap = "
            SELECT
                COUNT(*) AS total_count,
                SUM(biaya_byPengajuan) AS total_nominal,
                SUM(CASE WHEN status LIKE '3%' THEN 1 ELSE 0 END) AS terbayar_count,
                SUM(CASE WHEN status LIKE '3%' THEN biaya_bySetujui ELSE 0 END) AS terbayar_nominal,
                SUM(CASE WHEN status LIKE '2%' THEN 1 ELSE 0 END) AS pending_count,
                SUM(CASE WHEN status LIKE '2%' THEN biaya_byPengajuan ELSE 0 END) AS pending_nominal,
                SUM(CASE WHEN status LIKE '4%' THEN 1 ELSE 0 END) AS tidak_layak_count,
                SUM(CASE WHEN status LIKE '4%' THEN biaya_byPengajuan ELSE 0 END) AS tidak_layak_nominal,
                SUM(CASE WHEN status LIKE '1%' THEN 1 ELSE 0 END) AS diproses_count,
                SUM(CASE WHEN status LIKE '1%' THEN biaya_byPengajuan ELSE 0 END) AS diproses_nominal
            FROM mon_klaim_rinap
            WHERE tglPulang BETWEEN ? AND ?
        ";

        // Rjalan (filter dari tgl Sep)
        $aggRjalan = "
            SELECT
                COUNT(*) AS total_count,
                SUM(biaya_byPengajuan) AS total_nominal,
                SUM(CASE WHEN status LIKE '3%' THEN 1 ELSE 0 END) AS terbayar_count,
                SUM(CASE WHEN status LIKE '3%' THEN biaya_bySetujui ELSE 0 END) AS terbayar_nominal,
                SUM(CASE WHEN status LIKE '2%' THEN 1 ELSE 0 END) AS pending_count,
                SUM(CASE WHEN status LIKE '2%' THEN biaya_byPengajuan ELSE 0 END) AS pending_nominal,
                SUM(CASE WHEN status LIKE '4%' THEN 1 ELSE 0 END) AS tidak_layak_count,
                SUM(CASE WHEN status LIKE '4%' THEN biaya_byPengajuan ELSE 0 END) AS tidak_layak_nominal,
                SUM(CASE WHEN status LIKE '1%' THEN 1 ELSE 0 END) AS diproses_count,
                SUM(CASE WHEN status LIKE '1%' THEN biaya_byPengajuan ELSE 0 END) AS diproses_nominal
            FROM mon_klaim_rjalan
            WHERE tglSep BETWEEN ? AND ?
        ";

        $cur  = $this->merge(
            $this->db()->selectOne($aggRinap,  [$from, $to]),
            $this->db()->selectOne($aggRjalan, [$from, $to])
        );
        $prev = $this->merge(
            $this->db()->selectOne($aggRinap,  [$prevFrom, $prevTo]),
            $this->db()->selectOne($aggRjalan, [$prevFrom, $prevTo])
        );

        return response()->json([
            'pengajuan' => [
                'count'   => (int)   ($cur->total_count       ?? 0),
                'nominal' => (float) ($cur->total_nominal      ?? 0),
                'delta'   => $this->calcDelta($cur->total_count ?? 0, $prev->total_count ?? 0),
            ],
            'terbayar' => [
                'count'   => (int)   ($cur->terbayar_count    ?? 0),
                'nominal' => (float) ($cur->terbayar_nominal   ?? 0),
                'delta'   => $this->calcDelta($cur->terbayar_count ?? 0, $prev->terbayar_count ?? 0),
            ],
            'pending' => [
                'count'   => (int)   ($cur->pending_count     ?? 0),
                'nominal' => (float) ($cur->pending_nominal    ?? 0),
                'delta'   => $this->calcDelta($cur->pending_count ?? 0, $prev->pending_count ?? 0),
            ],
            'tidak_layak' => [
                'count'   => (int)   ($cur->tidak_layak_count    ?? 0),
                'nominal' => (float) ($cur->tidak_layak_nominal   ?? 0),
                'delta'   => $this->calcDelta($cur->tidak_layak_count ?? 0, $prev->tidak_layak_count ?? 0),
            ],
            'diproses' => [
                'count'   => (int)   ($cur->diproses_count    ?? 0),
                'nominal' => (float) ($cur->diproses_nominal   ?? 0),
                'delta'   => $this->calcDelta($cur->diproses_count ?? 0, $prev->diproses_count ?? 0),
            ],
        ]);
    }

    /* ═════════ BPJS Chart ════════════════ */
    public function chart(Request $request): JsonResponse
    {
        [$from, $to] = $this->resolveDateRange($request);
        $period      = $request->get('period', 'monthly');

        $groupFormat = match ($period) {
            'yearly' => '%Y-%m',
            default  => '%Y-%m-%d',
        };

        $labelFormat = match ($period) {
            'weekly' => fn($d) => Carbon::parse($d)->translatedFormat('D, d M'),
            'yearly' => fn($d) => Carbon::parse($d . '-01')->translatedFormat('M Y'),
            default  => fn($d) => Carbon::parse($d)->translatedFormat('d M'),
        };

        // Rinap (dari tgl pulang)
        $chartRinap = "
            SELECT
                DATE_FORMAT(tglPulang, '{$groupFormat}')             AS period_key,
                SUM(CASE WHEN status LIKE '3%' THEN 1 ELSE 0 END)   AS terbayar,
                SUM(CASE WHEN status LIKE '2%' THEN 1 ELSE 0 END)   AS pending,
                SUM(CASE WHEN status LIKE '4%' THEN 1 ELSE 0 END)   AS tidak_layak,
                SUM(CASE WHEN status LIKE '1%' THEN 1 ELSE 0 END)   AS diproses
            FROM mon_klaim_rinap
            WHERE tglPulang BETWEEN ? AND ?
            GROUP BY period_key
            ORDER BY period_key
        ";

        // Rjalan (dari tgl Sep)
        $chartRjalan = "
            SELECT
                DATE_FORMAT(tglSep, '{$groupFormat}')                AS period_key,
                SUM(CASE WHEN status LIKE '3%' THEN 1 ELSE 0 END)   AS terbayar,
                SUM(CASE WHEN status LIKE '2%' THEN 1 ELSE 0 END)   AS pending,
                SUM(CASE WHEN status LIKE '4%' THEN 1 ELSE 0 END)   AS tidak_layak,
                SUM(CASE WHEN status LIKE '1%' THEN 1 ELSE 0 END)   AS diproses
            FROM mon_klaim_rjalan
            WHERE tglSep BETWEEN ? AND ?
            GROUP BY period_key
            ORDER BY period_key
        ";

        $rinapRows  = collect($this->db()->select($chartRinap,  [$from, $to]));
        $rjalanRows = collect($this->db()->select($chartRjalan, [$from, $to]));
        $merged     = $this->mergeChartRows($rinapRows, $rjalanRows);
        $filled     = $this->fillGaps($merged, $from, $to, $period, $groupFormat);

        // Summary untuk donut chart
        $sumRinap = $this->db()->selectOne("
            SELECT
                SUM(CASE WHEN status LIKE '3%' THEN 1 ELSE 0 END) AS terbayar,
                SUM(CASE WHEN status LIKE '2%' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status LIKE '4%' THEN 1 ELSE 0 END) AS tidak_layak,
                SUM(CASE WHEN status LIKE '1%' THEN 1 ELSE 0 END) AS diproses
            FROM mon_klaim_rinap
            WHERE tglPulang BETWEEN ? AND ?
        ", [$from, $to]);

        $sumRjalan = $this->db()->selectOne("
            SELECT
                SUM(CASE WHEN status LIKE '3%' THEN 1 ELSE 0 END) AS terbayar,
                SUM(CASE WHEN status LIKE '2%' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status LIKE '4%' THEN 1 ELSE 0 END) AS tidak_layak,
                SUM(CASE WHEN status LIKE '1%' THEN 1 ELSE 0 END) AS diproses
            FROM mon_klaim_rjalan
            WHERE tglSep BETWEEN ? AND ?
        ", [$from, $to]);

        $summary = $this->merge($sumRinap, $sumRjalan);

        return response()->json([
            'labels'      => $filled->map(fn($r) => $labelFormat($r->period_key))->values(),
            'terbayar'    => $filled->pluck('terbayar')->map(fn($v) => (int) $v)->values(),
            'pending'     => $filled->pluck('pending')->map(fn($v) => (int) $v)->values(),
            'tidak_layak' => $filled->pluck('tidak_layak')->map(fn($v) => (int) $v)->values(),
            'diproses'    => $filled->pluck('diproses')->map(fn($v) => (int) $v)->values(),
            'summary'     => [
                'terbayar'    => (int) ($summary->terbayar    ?? 0),
                'pending'     => (int) ($summary->pending     ?? 0),
                'tidak_layak' => (int) ($summary->tidak_layak ?? 0),
                'diproses'    => (int) ($summary->diproses    ?? 0),
            ],
        ]);
    }

    /* ═══════════════ BPJS List ═══════════════════════ */
    public function list(Request $request): JsonResponse
    {
        [$from, $to] = $this->resolveDateRange($request);

        $listSql = "
            SELECT
                noSEP               AS no_sep,
                peserta_nama        AS nama_pasien,
                Inacbg_nama         AS diagnosa,
                tglPulang           AS tgl_pengajuan,
                status,
                biaya_byPengajuan   AS nominal,
                biaya_bySetujui     AS terbayar,
                'rinap'             AS jenis
            FROM mon_klaim_rinap
            WHERE tglPulang BETWEEN ? AND ?

            UNION ALL

            SELECT
                noSEP               AS no_sep,
                peserta_nama        AS nama_pasien,
                Inacbg_nama         AS diagnosa,
                tglSep              AS tgl_pengajuan,
                status,
                biaya_byPengajuan   AS nominal,
                biaya_bySetujui     AS terbayar,
                'rjalan'            AS jenis
            FROM mon_klaim_rjalan
            WHERE tglSep BETWEEN ? AND ?

            ORDER BY tgl_pengajuan DESC
        ";

        $rows = collect($this->db()->select($listSql, [$from, $to, $from, $to]));

        // Map status
        $statusLabel = function ($s) {
            $s = (string) $s;
            if (str_starts_with($s, '3')) return 'terbayar';
            if (str_starts_with($s, '2')) return 'pending';
            if (str_starts_with($s, '4')) return 'tidak_layak';
            if (str_starts_with($s, '1')) return 'diproses';
            return 'unknown';
        };

        // Filter opsional
        if ($request->filled('status')) {
            $filterStatus = $request->status; // terbayar | pending | tidak_layak | diproses
            $rows = $rows->filter(fn($r) => $statusLabel($r->status) === $filterStatus);
        }

        if ($request->filled('search')) {
            $q = strtolower($request->search);
            $rows = $rows->filter(fn($r) =>
                str_contains(strtolower($r->no_sep      ?? ''), $q) ||
                str_contains(strtolower($r->nama_pasien ?? ''), $q)
            );
        }

        return response()->json(
            $rows->map(fn($r) => [
                'no_sep'        => $r->no_sep,
                'nama_pasien'   => $r->nama_pasien,
                'diagnosa'      => $r->diagnosa,
                'tgl_pengajuan' => $r->tgl_pengajuan,
                'status'        => $statusLabel($r->status),
                'nominal'       => (float) ($r->nominal  ?? 0),
                'terbayar'      => (float) ($r->terbayar ?? 0),
                'jenis'         => $r->jenis,
            ])->values()
        );
    }

    public function meta(): JsonResponse
    {
        $maxRinap  = $this->db()->selectOne("SELECT MAX(tglPulang) AS max_tgl FROM mon_klaim_rinap");
        $maxRjalan = $this->db()->selectOne("SELECT MAX(tglSep) AS max_tgl FROM mon_klaim_rjalan");

        $maxDate = max(
            $maxRinap->max_tgl  ?? '2024-01-01',
            $maxRjalan->max_tgl ?? '2024-01-01',
        );

        $dt       = Carbon::parse($maxDate);
        $fromDate = $dt->copy()->startOfMonth()->toDateString();
        $toDate   = $dt->copy()->endOfMonth()->toDateString();

        return response()->json([
            'max_date'  => $maxDate,
            'default_from' => $fromDate,
            'default_to'   => $toDate,
        ]);
    }
}