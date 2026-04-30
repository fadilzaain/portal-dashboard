<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KlaimBpjsController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ══════════════════════════════════════════════════════════════

    private function db()
    {
        return DB::connection('klaim_bpjs');
    }

    /**
     * Resolve date range dari request.
     * Priority: custom (from/to) → period (weekly/monthly/yearly)
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
     * Resolve previous date range untuk delta %.
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

    /**
     * Derive period string dari custom range (untuk format label/group).
     */
    private function derivePeriod(Request $request): string
    {
        if ($request->filled('from') && $request->filled('to')) {
            $diffDays = Carbon::parse($request->from)->diffInDays(Carbon::parse($request->to));
            return $diffDays > 90 ? 'yearly' : 'monthly';
        }
        return $request->get('period', 'monthly');
    }

    /**
     * Hitung delta persen antara current dan previous.
     */
    private function calcDelta(int|float $current, int|float $prev): float
    {
        if ($prev == 0) return $current > 0 ? 100.0 : 0.0;
        return round((($current - $prev) / $prev) * 100, 1);
    }

    /**
     * Merge 2 stdClass objects dengan menjumlahkan semua field numerik.
     */
    private function merge(?object $a, ?object $b): object
    {
        $keys = array_unique(array_merge(
            array_keys((array) $a),
            array_keys((array) $b)
        ));
        $result = [];
        foreach ($keys as $key) {
            $va = $a->$key ?? 0;
            $vb = $b->$key ?? 0;
            $result[$key] = (is_numeric($va) ? (float) $va : 0)
                          + (is_numeric($vb) ? (float) $vb : 0);
        }
        return (object) $result;
    }

    /**
     * Fill gap periode tanpa data → nilai 0.
     * Dipakai untuk chart komposisi status (field: terbayar, pending, tidak_layak, diproses).
     */
    private function fillGaps(
        \Illuminate\Support\Collection $rows,
        Carbon $from,
        Carbon $to,
        string $period,
        string $groupFormat
    ): \Illuminate\Support\Collection {
        $map    = $rows->keyBy('period_key');
        $cursor = $from->copy();
        $fmt    = str_contains($groupFormat, 'd') ? 'Y-m-d' : 'Y-m';
        $keys   = collect();

        while ($cursor->lte($to)) {
            $keys->push($cursor->format($fmt));
            $period === 'yearly' ? $cursor->addMonth() : $cursor->addDay();
        }

        return $keys->map(fn($key) => $map->has($key) ? $map[$key] : (object) [
            'period_key'  => $key,
            'terbayar'    => 0,
            'pending'     => 0,
            'tidak_layak' => 0,
            'diproses'    => 0,
        ]);
    }

    /**
     * Fill gap periode tanpa data → nilai 0.
     * Dipakai untuk chartJenis (field: pengajuan, terbayar_count, nominal).
     */
    private function fillGapsChart(
        \Illuminate\Support\Collection $rows,
        Carbon $from,
        Carbon $to,
        string $period,
        string $groupFormat
    ): \Illuminate\Support\Collection {
        $map    = $rows->keyBy('period_key');
        $cursor = $from->copy();
        $fmt    = str_contains($groupFormat, 'd') ? 'Y-m-d' : 'Y-m';
        $keys   = collect();

        while ($cursor->lte($to)) {
            $keys->push($cursor->format($fmt));
            $period === 'yearly' ? $cursor->addMonth() : $cursor->addDay();
        }

        return $keys->map(fn($key) => $map->has($key) ? $map[$key] : (object) [
            'period_key'     => $key,
            'pengajuan'      => 0,
            'terbayar_count' => 0,
            'nominal'        => 0,
        ]);
    }

    /**
     * Map label format berdasarkan period.
     */
    private function labelFormat(string $period): \Closure
    {
        return match ($period) {
            'weekly' => fn($d) => Carbon::parse($d)->translatedFormat('D, d M'),
            'yearly' => fn($d) => Carbon::parse($d . '-01')->translatedFormat('M Y'),
            default  => fn($d) => Carbon::parse($d)->translatedFormat('d M'),
        };
    }

    // ══════════════════════════════════════════════════════════════
    //  KETERANGAN STATUS BPJS:
    //    LIKE '1%' → Diproses  (Proses Verifikasi)
    //    LIKE '2%' → Pending   (Klaim Pending)
    //    LIKE '3%' → Terbayar  (Klaim Terbayar)
    //    LIKE '4%' → Tidak Layak
    // ══════════════════════════════════════════════════════════════

    // ══════════════════════════════════════════════════════════════
    //  PUBLIC ENDPOINTS
    // ══════════════════════════════════════════════════════════════

    /**
     * GET /bpjs — Tampilkan halaman Blade dashboard.
     */
    public function index()
    {
        return view('bpjs.klaim');
    }

    /**
     * GET /bpjs/meta
     * Return: default date range (bulan dari tanggal data terbaru).
     */
    public function meta(): JsonResponse
    {
        $maxRinap  = $this->db()->selectOne("SELECT MAX(tglPulang) AS max_tgl FROM mon_klaim_rinap");
        $maxRjalan = $this->db()->selectOne("SELECT MAX(tglSep)    AS max_tgl FROM mon_klaim_rjalan");

        $maxDate = max(
            $maxRinap->max_tgl  ?? '2024-01-01',
            $maxRjalan->max_tgl ?? '2024-01-01',
        );

        $dt = Carbon::parse($maxDate);

        return response()->json([
            'max_date'     => $maxDate,
            'default_from' => $dt->copy()->startOfMonth()->toDateString(),
            'default_to'   => $dt->copy()->endOfMonth()->toDateString(),
        ]);
    }

    /**
     * GET /bpjs/summary
     * Return: agregasi nominal & count per status (+ delta vs periode sebelumnya).
     */
    public function summary(Request $request): JsonResponse
    {
        [$from, $to]         = $this->resolveDateRange($request);
        [$prevFrom, $prevTo] = $this->resolvePrevDateRange($request);

        $aggRinap = "
            SELECT
                COUNT(*)                                                                AS total_count,
                SUM(biaya_byPengajuan)                                                  AS total_nominal,
                SUM(CASE WHEN status LIKE '3%' THEN 1            ELSE 0 END)            AS terbayar_count,
                SUM(CASE WHEN status LIKE '3%' THEN biaya_bySetujui   ELSE 0 END)       AS terbayar_nominal,
                SUM(CASE WHEN status LIKE '2%' THEN 1            ELSE 0 END)            AS pending_count,
                SUM(CASE WHEN status LIKE '2%' THEN biaya_byPengajuan ELSE 0 END)       AS pending_nominal,
                SUM(CASE WHEN status LIKE '4%' THEN 1            ELSE 0 END)            AS tidak_layak_count,
                SUM(CASE WHEN status LIKE '4%' THEN biaya_byPengajuan ELSE 0 END)       AS tidak_layak_nominal,
                SUM(CASE WHEN status LIKE '1%' THEN 1            ELSE 0 END)            AS diproses_count,
                SUM(CASE WHEN status LIKE '1%' THEN biaya_byPengajuan ELSE 0 END)       AS diproses_nominal
            FROM mon_klaim_rinap
            WHERE tglPulang BETWEEN ? AND ?
        ";

        $aggRjalan = "
            SELECT
                COUNT(*)                                                                AS total_count,
                SUM(biaya_byPengajuan)                                                  AS total_nominal,
                SUM(CASE WHEN status LIKE '3%' THEN 1            ELSE 0 END)            AS terbayar_count,
                SUM(CASE WHEN status LIKE '3%' THEN biaya_bySetujui   ELSE 0 END)       AS terbayar_nominal,
                SUM(CASE WHEN status LIKE '2%' THEN 1            ELSE 0 END)            AS pending_count,
                SUM(CASE WHEN status LIKE '2%' THEN biaya_byPengajuan ELSE 0 END)       AS pending_nominal,
                SUM(CASE WHEN status LIKE '4%' THEN 1            ELSE 0 END)            AS tidak_layak_count,
                SUM(CASE WHEN status LIKE '4%' THEN biaya_byPengajuan ELSE 0 END)       AS tidak_layak_nominal,
                SUM(CASE WHEN status LIKE '1%' THEN 1            ELSE 0 END)            AS diproses_count,
                SUM(CASE WHEN status LIKE '1%' THEN biaya_byPengajuan ELSE 0 END)       AS diproses_nominal
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
                'count'   => (int)   ($cur->total_count          ?? 0),
                'nominal' => (float) ($cur->total_nominal         ?? 0),
                'delta'   => $this->calcDelta($cur->total_count ?? 0, $prev->total_count ?? 0),
            ],
            'terbayar' => [
                'count'   => (int)   ($cur->terbayar_count        ?? 0),
                'nominal' => (float) ($cur->terbayar_nominal       ?? 0),
                'delta'   => $this->calcDelta($cur->terbayar_count ?? 0, $prev->terbayar_count ?? 0),
            ],
            'pending' => [
                'count'   => (int)   ($cur->pending_count         ?? 0),
                'nominal' => (float) ($cur->pending_nominal        ?? 0),
                'delta'   => $this->calcDelta($cur->pending_count ?? 0, $prev->pending_count ?? 0),
            ],
            'tidak_layak' => [
                'count'   => (int)   ($cur->tidak_layak_count     ?? 0),
                'nominal' => (float) ($cur->tidak_layak_nominal    ?? 0),
                'delta'   => $this->calcDelta($cur->tidak_layak_count ?? 0, $prev->tidak_layak_count ?? 0),
            ],
            'diproses' => [
                'count'   => (int)   ($cur->diproses_count        ?? 0),
                'nominal' => (float) ($cur->diproses_nominal       ?? 0),
                'delta'   => $this->calcDelta($cur->diproses_count ?? 0, $prev->diproses_count ?? 0),
            ],
        ]);
    }

    /**
     * GET /bpjs/chart-jenis
     * Return: data chart terpisah rinap vs rjalan + summary donut.
     * Format: {
     *   rinap:   { labels, pengajuan[], terbayar_count[], nominal[] },
     *   rjalan:  { labels, pengajuan[], terbayar_count[], nominal[] },
     *   summary: { terbayar, pending, tidak_layak, diproses }
     * }
     */
    public function chartJenis(Request $request): JsonResponse
    {
        [$from, $to] = $this->resolveDateRange($request);
        $period      = $this->derivePeriod($request);

        $groupFormat = match ($period) {
            'yearly' => '%Y-%m',
            default  => '%Y-%m-%d',
        };

        $fmt = $this->labelFormat($period);

        // ── RINAP ──
        $rinapRows = collect($this->db()->select("
            SELECT
                DATE_FORMAT(tglPulang, '{$groupFormat}')             AS period_key,
                COUNT(*)                                             AS pengajuan,
                SUM(CASE WHEN status LIKE '3%' THEN 1 ELSE 0 END)   AS terbayar_count,
                SUM(CASE WHEN status LIKE '3%'
                    THEN biaya_bySetujui ELSE 0 END)                 AS nominal
            FROM mon_klaim_rinap
            WHERE tglPulang BETWEEN ? AND ?
            GROUP BY period_key
            ORDER BY period_key
        ", [$from, $to]));

        // ── RJALAN ──
        $rjalanRows = collect($this->db()->select("
            SELECT
                DATE_FORMAT(tglSep, '{$groupFormat}')                AS period_key,
                COUNT(*)                                             AS pengajuan,
                SUM(CASE WHEN status LIKE '3%' THEN 1 ELSE 0 END)   AS terbayar_count,
                SUM(CASE WHEN status LIKE '3%'
                    THEN biaya_bySetujui ELSE 0 END)                 AS nominal
            FROM mon_klaim_rjalan
            WHERE tglSep BETWEEN ? AND ?
            GROUP BY period_key
            ORDER BY period_key
        ", [$from, $to]));

        $rinapFilled  = $this->fillGapsChart($rinapRows,  $from, $to, $period, $groupFormat);
        $rjalanFilled = $this->fillGapsChart($rjalanRows, $from, $to, $period, $groupFormat);

        // Labels (sama untuk kedua chart karena fill gaps pakai range yang sama)
        $labels = $rinapFilled->map(fn($r) => $fmt($r->period_key))->values();

        // ── SUMMARY untuk donut ──
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
            'rinap' => [
                'labels'         => $labels,
                'pengajuan'      => $rinapFilled->pluck('pengajuan')->map(fn($v) => (int)   $v)->values(),
                'terbayar_count' => $rinapFilled->pluck('terbayar_count')->map(fn($v) => (int) $v)->values(),
                'nominal'        => $rinapFilled->pluck('nominal')->map(fn($v) => (float) $v)->values(),
            ],
            'rjalan' => [
                'labels'         => $labels,
                'pengajuan'      => $rjalanFilled->pluck('pengajuan')->map(fn($v) => (int)   $v)->values(),
                'terbayar_count' => $rjalanFilled->pluck('terbayar_count')->map(fn($v) => (int) $v)->values(),
                'nominal'        => $rjalanFilled->pluck('nominal')->map(fn($v) => (float) $v)->values(),
            ],
            'summary' => [
                'terbayar'    => (int) ($summary->terbayar    ?? 0),
                'pending'     => (int) ($summary->pending     ?? 0),
                'tidak_layak' => (int) ($summary->tidak_layak ?? 0),
                'diproses'    => (int) ($summary->diproses    ?? 0),
            ],
        ]);
    }

    /**
     * GET /bpjs/list
     * Return: daftar klaim gabungan rinap+rjalan, support filter status & search.
     */
    public function list(Request $request): JsonResponse
    {
        [$from, $to] = $this->resolveDateRange($request);

        $rows = collect($this->db()->select("
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
        ", [$from, $to, $from, $to]));

        $statusLabel = function (string $s): string {
            if (str_starts_with($s, '3')) return 'terbayar';
            if (str_starts_with($s, '2')) return 'pending';
            if (str_starts_with($s, '4')) return 'tidak_layak';
            if (str_starts_with($s, '1')) return 'diproses';
            return 'unknown';
        };

        // Filter opsional
        if ($request->filled('status')) {
            $filterStatus = $request->status;
            $rows = $rows->filter(fn($r) => $statusLabel((string) $r->status) === $filterStatus);
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
                'status'        => $statusLabel((string) $r->status),
                'nominal'       => (float) ($r->nominal  ?? 0),
                'terbayar'      => (float) ($r->terbayar ?? 0),
                'jenis'         => $r->jenis,
            ])->values()
        );
    }
}