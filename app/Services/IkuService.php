<?php

namespace App\Services;

use App\Models\IkuIndikator;
use App\Models\IkuRealisasi;
use App\Models\IkuTarget;
use App\Models\IkuTindakLanjut;

// Semua perhitungan Dashboard IKU ada di sini
// ambil data, hitung capaian sesuai jenis indikator, tentukan status, susun data grafik, dan format angka tampilan.
class IkuService
{
    // Nomor triwulan angka romawi
    public const ROMAWI = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];

    // Tanggal akhir tiap triwulan (tidak bergantung tahun kabisat)
    public const AKHIR_PERIODE = [1 => '31 Maret', 2 => '30 Juni', 3 => '30 September', 4 => '31 Desember'];

    // Teks status untuk badge
    public const LABEL_STATUS = [
        'tercapai'  => 'Tercapai',
        'perhatian' => 'Perlu Perhatian',
        'belum'     => 'Belum Tercapai',
        'kosong'    => 'Belum Ada Data',
    ];

    // ─── Filter ───────────────────────────────────────────────────────────────

    // Daftar tahun yang punya target atau realisasi, terbaru di depan. Kalau belum ada data sama sekali: tahun ini.
    public function tahunTersedia(): array
    {
        $tahun = IkuTarget::query()->distinct()->pluck('tahun')
            ->merge(IkuRealisasi::query()->distinct()->pluck('tahun'))
            ->map(fn ($t) => (int) $t)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        return $tahun ?: [(int) now()->year];
    }

    // Triwulan terakhir yang sudah ada realisasinya di tahun itu (default filter). Kalau belum ada: triwulan 1.
    public function triwulanTerakhir(int $tahun): int
    {
        $bulan = IkuRealisasi::where('tahun', $tahun)->max('bulan');

        return $bulan ? (int) ceil($bulan / 3) : 1;
    }

    // ─── Halaman utama ────────────────────────────────────────────────────────

    // data untuk halaman utama: baris tabel, ringkasan KPI, rekap per sasaran, dan data grafik.
    // Realisasi bersifat kumulatif tahun berjalan, jadi yang dipakai adalah nilai PADA akhir triwulan terpilih.
    // Kalau pada periode itu belum ada nilai, indikator ditampilkan "Belum Ada Data" (bukan 0, bukan nilai periode lain).
    public function dataDashboard(int $tahun, int $triwulan): array
    {
        $m     = $this->muat($tahun);
        $bulan = $this->bulanTriwulan($triwulan);

        $baris = $m['daftar']->map(fn (IkuIndikator $i) => $this->susunBaris(
            $i,
            $m['target']->get($i->id),
            $m['realisasi']->get($i->id)?->get($bulan),
            $tahun,
            $triwulan
        ))->all();

        return $this->periode($tahun, $triwulan) + [
            'baris'     => $baris,
            'ringkasan' => $this->hitungRingkasan($baris),
            'sasaran'   => $this->rekapSasaran($baris),
            'grafik'    => $this->susunGrafik($m, $baris),
        ];
    }

    // ─── Halaman detail satu indikator ────────────────────────────────────────

    public function detail(IkuIndikator $i, int $tahun, int $triwulan): array
    {
        $i->loadMissing('sasaran');

        $target    = IkuTarget::where('indikator_id', $i->id)->where('tahun', $tahun)->first();
        $realisasi = IkuRealisasi::where('indikator_id', $i->id)->where('tahun', $tahun)->get()->keyBy('bulan');
        $bulan     = $this->bulanTriwulan($triwulan);

        // Evaluasi & tindak lanjut untuk periode yang sedang dipilih
        $tl = IkuTindakLanjut::where('indikator_id', $i->id)->where('tahun', $tahun)->where('bulan', $bulan)->first();

        $riwayat = [];
        $label   = [];
        $nilai   = [];
        $nilaiFmt = [];

        foreach (array_keys(self::ROMAWI) as $n) {
            $r = $realisasi->get($this->bulanTriwulan($n));
            $c = $this->capaianPada($i, $target, $r);
            $s = $this->tentukanStatus($c);

            $riwayat[] = [
                'periode'       => 'TW ' . self::ROMAWI[$n] . ' ' . $tahun,
                'realisasi_fmt' => $this->formatNilai($r?->nilai, $i->satuan),
                'capaian_fmt'   => $c === null ? '–' : $this->angka($c, 1) . '%',
                'status'        => $s,
                'status_label'  => self::LABEL_STATUS[$s],
                'terpilih'      => $n === $triwulan,
            ];

            $label[]    = 'TW ' . self::ROMAWI[$n];
            $nilai[]    = $r?->nilai;
            $nilaiFmt[] = $this->formatNilai($r?->nilai, $i->satuan);
        }

        return $this->periode($tahun, $triwulan) + [
            'indikator' => [
                'id'               => $i->id,
                'nama'             => $i->nama,
                'sasaran'          => $i->sasaran?->nama ?? '–',
                'satuan'           => $i->satuan,
                'jenis_label'      => $i->label_jenis_penilaian,
                'definisi'         => $i->definisi,
                'rumus'            => $i->rumus,
                'penanggung_jawab' => $i->penanggung_jawab,
            ],
            'baris'    => $this->susunBaris($i, $target, $realisasi->get($bulan), $tahun, $triwulan),
            'riwayat'  => $riwayat,
            'grafik'   => [
                'nama'          => $i->nama,
                'labels'        => $label,
                'realisasi'     => $nilai,
                'realisasi_fmt' => $nilaiFmt,
                'target'        => $target?->target,
                'target_fmt'    => $this->formatNilai($target?->target, $i->satuan),
                'ada_data'      => collect($nilai)->contains(fn ($v) => $v !== null),
            ],
            'evaluasi' => [
                'catatan' => $tl?->catatan_evaluasi,
                'rencana' => $tl?->rencana_tindak_lanjut,
            ],
        ];
    }

    // ─── Perhitungan capaian & status ─────────────────────────────────────────

    // Capaian dalam persen, menyesuaikan jenis indikator. Hasil TIDAK dibatasi 100 (121% artinya melampaui target).
    //  higher : realisasi / target × 100
    //  lower  : target / realisasi × 100   (realisasi lebih kecil dari target = di atas 100%)
    //  range  : 100% kalau realisasi di dalam [batas_bawah, batas_atas]; di luar rentang berkurang sesuai jaraknya
    // Mengembalikan null kalau tidak bisa dihitung (mis. jenis range tapi batas rentang belum diisi).
    public function hitungCapaian(string $jenis, float $target, float $realisasi, ?float $batasBawah = null, ?float $batasAtas = null): ?float
    {
        return match ($jenis) {
            'lower' => $this->capaianLower($target, $realisasi),
            'range' => $this->capaianRange($target, $realisasi, $batasBawah, $batasAtas),
            default => $this->capaianHigher($target, $realisasi),
        };
    }

    // Status dari capaian, memakai batas di config/iku.php (bukan angka tetap di sini)
    public function tentukanStatus(?float $capaian): string
    {
        if ($capaian === null) {
            return 'kosong';
        }

        $ambang = config('iku.ambang_status');
        $nilai  = round($capaian, 2); // hindari selisih desimal seperti 99,9999

        if ($nilai >= $ambang['tercapai']) {
            return 'tercapai';
        }

        return $nilai >= $ambang['perhatian'] ? 'perhatian' : 'belum';
    }

    private function capaianHigher(float $target, float $realisasi): float
    {
        if ($target <= 0) {
            return $realisasi >= $target ? 100.0 : 0.0;
        }

        return $realisasi / $target * 100;
    }

    private function capaianLower(float $target, float $realisasi): float
    {
        if ($realisasi <= 0) {
            return 100.0; // nilai 0 (atau lebih kecil) = hasil terbaik untuk indikator "semakin kecil semakin baik"
        }

        return $target / $realisasi * 100;
    }

    private function capaianRange(float $target, float $realisasi, ?float $bawah, ?float $atas): ?float
    {
        if ($bawah === null || $atas === null) {
            return null;
        }

        if ($realisasi >= $bawah && $realisasi <= $atas) {
            return 100.0;
        }

        $jarak = $realisasi < $bawah ? $bawah - $realisasi : $realisasi - $atas;
        $acuan = abs($target) > 0 ? abs($target) : (($atas - $bawah) ?: 1.0);

        return max(0.0, 100 - ($jarak / $acuan * 100));
    }

    // Capaian dari sepasang target + realisasi (null kalau salah satunya belum ada)
    private function capaianPada(IkuIndikator $i, ?IkuTarget $t, ?IkuRealisasi $r): ?float
    {
        if ($t === null || $r === null) {
            return null;
        }

        return $this->hitungCapaian($i->jenis_penilaian, $t->target, $r->nilai, $t->batas_bawah, $t->batas_atas);
    }

    // ─── Pengambilan data ─────────────────────────────────────────────────────

    // 3 query saja untuk satu tahun (tidak ada N+1): indikator + sasaran, target, dan seluruh realisasi tahun itu.
    // realisasi dipetakan [indikator_id][bulan] supaya tiap triwulan bisa diambil tanpa query tambahan.
    private function muat(int $tahun): array
    {
        return [
            'daftar'    => IkuIndikator::aktif()->urut()->with('sasaran')->get(),
            'target'    => IkuTarget::where('tahun', $tahun)->get()->keyBy('indikator_id'),
            'realisasi' => IkuRealisasi::where('tahun', $tahun)->get()
                ->groupBy('indikator_id')
                ->map(fn ($grup) => $grup->keyBy('bulan')),
        ];
    }

    // Bulan akhir sebuah triwulan (1→3, 2→6, 3→9, 4→12), dari config
    private function bulanTriwulan(int $triwulan): int
    {
        return (int) config('iku.bulan_triwulan')[$triwulan - 1];
    }

    private function periode(int $tahun, int $triwulan): array
    {
        return [
            'tahun'           => $tahun,
            'triwulan'        => $triwulan,
            'periode_label'   => 'Triwulan ' . self::ROMAWI[$triwulan] . ' ' . $tahun,
            'periode_singkat' => 'TW ' . self::ROMAWI[$triwulan] . ' ' . $tahun,
            'akhir_periode'   => self::AKHIR_PERIODE[$triwulan] . ' ' . $tahun,
        ];
    }

    // ─── Penyusunan baris, ringkasan, rekap, grafik ───────────────────────────

    private function susunBaris(IkuIndikator $i, ?IkuTarget $t, ?IkuRealisasi $r, int $tahun, int $triwulan): array
    {
        $target = $t?->target;
        $nilai  = $r?->nilai;

        $capaian = $this->capaianPada($i, $t, $r);
        $status  = $this->tentukanStatus($capaian);

        // Catatan kecil di bawah nama indikator kalau datanya belum lengkap
        $catatan = null;
        if ($nilai !== null && $target === null) {
            $catatan = 'Target tahun ' . $tahun . ' belum diatur';
        } elseif ($nilai !== null && $capaian === null) {
            $catatan = 'Rentang target belum diatur';
        }

        return [
            'id'            => $i->id,
            'nama'          => $i->nama,
            'sasaran'       => $i->sasaran?->nama ?? '–',
            'satuan'        => $i->satuan,
            'jenis'         => $i->jenis_penilaian,
            'target'        => $target,
            'target_fmt'    => $this->formatNilai($target, $i->satuan),
            'realisasi'     => $nilai,
            'realisasi_fmt' => $this->formatNilai($nilai, $i->satuan),
            'capaian'       => $capaian,
            'capaian_fmt'   => $capaian === null ? '–' : $this->angka($capaian, 1) . '%',
            'bar'           => $capaian === null ? 0 : round(max(0, min(100, $capaian)), 1), // lebar progress bar, maksimal penuh
            'status'        => $status,
            'status_label'  => self::LABEL_STATUS[$status],
            'periode'       => 'TW ' . self::ROMAWI[$triwulan] . ' ' . $tahun,
            'catatan'       => $catatan,
        ];
    }

    private function hitungRingkasan(array $baris): array
    {
        $kolom = collect($baris);

        $total     = $kolom->count();
        $tercapai  = $kolom->where('status', 'tercapai')->count();
        $perhatian = $kolom->where('status', 'perhatian')->count();
        $belum     = $kolom->where('status', 'belum')->count();
        $kosong    = $kolom->where('status', 'kosong')->count();

        // Persentase Capaian IKU = rata-rata capaian indikator yang punya data; tiap indikator dibatasi 0–100%
        // supaya satu indikator yang melampaui target jauh tidak menutupi indikator lain yang belum tercapai.
        $persen = $this->rataCapaian($kolom);

        return [
            'total'              => $total,
            'berdata'            => $total - $kosong,
            'tercapai'           => $tercapai,
            'belum_tercapai'     => $perhatian + $belum, // "Perlu Perhatian" juga belum mencapai target
            'perlu_perhatian'    => $perhatian,
            'belum_ada_data'     => $kosong,
            'persen_capaian'     => $persen,
            'persen_capaian_fmt' => $persen === null ? '–' : $this->angka($persen, 1) . '%',
        ];
    }

    // Rata-rata capaian (tiap indikator dibatasi 0–100%) dari kumpulan baris; null kalau tidak ada yang berdata
    private function rataCapaian($kolom): ?float
    {
        $capaian = $kolom->pluck('capaian')->filter(fn ($c) => $c !== null);

        return $capaian->isEmpty() ? null : $capaian->map(fn ($c) => max(0, min(100, $c)))->avg();
    }

    // Rekap capaian per sasaran strategis pada periode terpilih
    private function rekapSasaran(array $baris): array
    {
        return collect($baris)
            ->groupBy('sasaran')
            ->map(function ($rows, $nama) {
                $kolom  = collect($rows);
                $persen = $this->rataCapaian($kolom);

                return [
                    'nama'       => $nama,
                    'total'      => $kolom->count(),
                    'berdata'    => $kolom->where('status', '!=', 'kosong')->count(),
                    'tercapai'   => $kolom->where('status', 'tercapai')->count(),
                    'persen_fmt' => $persen === null ? '–' : $this->angka($persen, 1) . '%',
                    'bar'        => $persen === null ? 0 : round($persen, 1),
                    'status'     => $this->tentukanStatus($persen),
                ];
            })
            ->values()
            ->all();
    }

    // Data untuk 2 grafik halaman utama. Karena satuan tiap indikator berbeda (‰, %, skor), grafik memakai
    // CAPAIAN (%) terhadap target, bukan nilai mentah; nilai mentah ditampilkan di tooltip.
    private function susunGrafik(array $m, array $baris): array
    {
        // Grafik 1: capaian per indikator pada periode terpilih (indikator tanpa capaian tidak digambar)
        $capaian = ['labels' => [], 'nilai' => [], 'status' => [], 'tip' => []];
        foreach ($baris as $b) {
            if ($b['capaian'] === null) {
                continue;
            }
            $capaian['labels'][] = $b['nama'];
            $capaian['nilai'][]  = round($b['capaian'], 1);
            $capaian['status'][] = $b['status'];
            $capaian['tip'][]    = 'Target ' . $b['target_fmt'] . ' · Realisasi ' . $b['realisasi_fmt'];
        }

        // Grafik 2: tren capaian tiap triwulan sepanjang tahun (triwulan tanpa data = celah, bukan 0)
        $seri = [];
        foreach ($m['daftar'] as $i) {
            $t    = $m['target']->get($i->id);
            $data = [];
            $tip  = [];

            foreach (array_keys(self::ROMAWI) as $n) {
                $r = $m['realisasi']->get($i->id)?->get($this->bulanTriwulan($n));
                $c = $this->capaianPada($i, $t, $r);

                $data[] = $c === null ? null : round($c, 1);
                $tip[]  = $c === null ? null : 'Target ' . $this->formatNilai($t->target, $i->satuan) . ' · Realisasi ' . $this->formatNilai($r->nilai, $i->satuan);
            }

            if (collect($data)->contains(fn ($v) => $v !== null)) {
                $seri[] = ['nama' => $this->namaSingkat($i->nama), 'data' => $data, 'tip' => $tip];
            }
        }

        return [
            'ada_data' => count($capaian['nilai']) > 0,
            'capaian'  => $capaian,
            'tren'     => [
                'labels' => array_map(fn ($n) => 'TW ' . self::ROMAWI[$n], array_keys(self::ROMAWI)),
                'seri'   => $seri,
            ],
        ];
    }

    // Nama pendek untuk legenda grafik: isi tanda kurung kalau ada ("... (INM) RS" → "INM"), kalau tidak nama lengkap
    private function namaSingkat(string $nama): string
    {
        return preg_match('/\(([^)]+)\)/u', $nama, $cocok) ? $cocok[1] : $nama;
    }

    // ─── Format angka (koma desimal gaya Indonesia, nol di belakang dibuang) ──

    public function angka(float $nilai, int $desimal = 2): string
    {
        return rtrim(rtrim(number_format($nilai, $desimal, ',', '.'), '0'), ',');
    }

    // Nilai + satuan. "%" dan "‰" menempel ke angka, satuan lain (mis. skor) diberi spasi.
    public function formatNilai(?float $nilai, string $satuan): string
    {
        if ($nilai === null) {
            return '–';
        }

        $teks = $this->angka($nilai);

        return in_array($satuan, ['%', '‰'], true) ? $teks . $satuan : $teks . ' ' . $satuan;
    }
}