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

    /**
     * Tampilkan halaman dashboard indikator mutu
     */
    public function index(Request $request): View
    {
        $filters = $this->getFilters($request);
        $tahunList = $this->service->getTahunTersedia();

        return view('portal.indikatormutu', compact('filters', 'tahunList'));
    }

    /**
     * API: Ambil data tabel + grafik (dipanggil via fetch/AJAX dari Blade)
     */
    public function getData(Request $request): JsonResponse
    {
        $request->validate([
            'tahun'     => 'nullable|integer|min:2000|max:2099',
            'triwulan'  => 'nullable|integer|min:1|max:4',
            'jenis_mutu' => 'nullable|in:nasional,prioritas',
        ]);

        $filters    = $this->getFilters($request);
        $indikators = $this->service->getIndikatorDenganCapaian($filters);

        $tabel  = $this->service->formatDataTabel($indikators, $filters['triwulan']);
        $grafik = $this->service->formatDataGrafik($indikators, $filters['triwulan']);

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
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function getFilters(Request $request): array
    {
        return [
            'tahun'      => (int) ($request->input('tahun', date('Y'))),
            'triwulan'   => $request->input('triwulan') ? (int) $request->input('triwulan') : null,
            'jenis_mutu' => $request->input('jenis_mutu'),
        ];
    }
}