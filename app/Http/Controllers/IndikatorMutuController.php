<?php

namespace App\Http\Controllers;

use App\Services\IndikatorMutuService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class IndikatorMutuController extends Controller
{
    public function __construct(
        protected IndikatorMutuService $service
    ) {}

    public function index(Request $request): View
    {
        $tahunList = $this->service->getTahunTersedia();
        $filters   = $this->getFilters($request);

        return view('portal.indikatormutu', compact('filters', 'tahunList'));
    }

    /**
     * data tabel PMKP + grafik 
     */
    public function getData(Request $request): JsonResponse
    {
        $request->validate([
            'tahun'      => 'nullable|integer|min:2000|max:2099',
            'triwulan'   => 'nullable|integer|min:1|max:4',
            'jenis_mutu' => 'nullable|in:nasional,prioritas',
        ]);

        $filters = $this->getFilters($request);

        try {
            $pmkpRaw   = $this->service->fetchPmkp($filters['triwulan'], $filters['tahun']);
            $tabel     = $this->service->formatTabel($pmkpRaw, $filters['jenis_mutu']);
            $grafik    = $this->service->formatGrafik($tabel, $filters['triwulan']);

            return response()->json([
                'success' => true,
                'filters' => $filters,
                'tabel'   => $tabel,
                'grafik'  => $grafik,
                'meta'    => [
                    'total_indikator' => count($tabel),
                    'tercapai'        => collect($tabel)->where('status', 'tercapai')->count(),
                    'belum_tercapai'  => collect($tabel)->where('status', 'belum')->count(),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * data NDR per ruangan
     */
    public function getNdr(Request $request): JsonResponse
    {
        $request->validate([
            'triwulan' => 'nullable|integer|min:1|max:4',
            'tahun'    => 'nullable|integer|min:2000|max:2099',
        ]);

        $triwulan = $request->input('triwulan', 1);
        $tahun    = $request->input('tahun', date('Y'));

        try {
            $ndrRaw  = $this->service->fetchNdr($triwulan, $tahun);
            $grafik  = $this->service->formatNdrGrafik($ndrRaw, $triwulan);

            return response()->json([
                'success' => true,
                'grafik'  => $grafik,
                'ruangan' => collect($ndrRaw)->pluck('RUANGAN')->filter()->values(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data NDR: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ─── Private Helpers ─────────────────────────────────────────────────────

    private function getFilters(Request $request): array
    {
        return [
            'tahun'      => (int) ($request->input('tahun', date('Y'))),
            'triwulan'   => $request->input('triwulan') ? (int) $request->input('triwulan') : 1,
            'jenis_mutu' => $request->input('jenis_mutu'),
        ];
    }
}