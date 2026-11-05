<?php

namespace App\Services\Iku;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Satu-satunya tempat yang membaca database SPM Track (koneksi 'spm', hanya BACA).
// Bentuk keluarannya sengaja sederhana (Indikator, array angka) supaya IkuService tidak tahu skema SPM Track.
class IkuSumber
{
    // Realisasi satu tahun disimpan sementara agar tidak di-query dua kali dalam satu request
    private array $ingatan = [];

    private function tabel(string $nama): Builder
    {
        return DB::connection(config('iku.sumber'))->table($nama);
    }

    // ─── Indikator ────────────────────────────────────────────────────────────

    // Semua indikator, berurutan per sasaran lalu per indikator (sama seperti halaman Input IKU di SPM Track)
    public function indikator(): Collection
    {
        return $this->kueriIndikator()->get()->map(fn ($r) => $this->bentuk($r))->values();
    }

    public function cari(int $id): ?Indikator
    {
        $r = $this->kueriIndikator()->where('i.id', $id)->first();

        return $r ? $this->bentuk($r) : null;
    }

    private function kueriIndikator(): Builder
    {
        return $this->tabel('iku as i')
            ->join('sasaran_iku as s', 's.id', '=', 'i.sasaran_iku_id')
            ->orderBy('s.urutan')->orderBy('s.id')
            ->orderBy('i.urutan')->orderBy('i.id')
            ->select('i.id', 'i.nama_indikator', 'i.frekuensi', 's.nama as sasaran');
    }

    // Gabungkan data SPM dengan metadata di config/iku.php (dicocokkan lewat nama indikator)
    private function bentuk(object $r): Indikator
    {
        $meta = config('iku.metadata', [])[$r->nama_indikator] ?? null;

        if ($meta === null) {
            Log::warning('Indikator IKU belum punya metadata di config/iku.php: ' . $r->nama_indikator);
        }

        $meta = ($meta ?? []) + config('iku.metadata_bawaan');

        return new Indikator(
            id: (int) $r->id,
            nama: $r->nama_indikator,
            sasaran: $r->sasaran,
            frekuensi: $r->frekuensi,
            satuan: $meta['satuan'],
            jenis_penilaian: $meta['jenis_penilaian'],
            definisi: $meta['definisi'] ?? null,
            rumus: $meta['rumus'] ?? null,
            penanggung_jawab: $meta['penanggung_jawab'] ?? null,
        );
    }

    // ─── Target & realisasi ───────────────────────────────────────────────────

    // Daftar tahun yang punya target atau realisasi, terbaru di depan
    public function tahunTersedia(): array
    {
        return $this->tabel('iku_tahunan')->distinct()->pluck('tahun')
            ->merge($this->tabel('iku_nilai')->distinct()->pluck('tahun'))
            ->map(fn ($t) => (int) $t)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();
    }

    // target[iku_id] = angka. Target yang kosong atau tidak terbaca sebagai angka tidak dimasukkan.
    public function target(int $tahun): array
    {
        return $this->tabel('iku_tahunan')
            ->where('tahun', $tahun)
            ->get(['iku_id', 'target'])
            ->mapWithKeys(fn ($r) => [(int) $r->iku_id => IkuParser::angka($r->target)])
            ->filter(fn ($v) => $v !== null)
            ->all();
    }

    // realisasi[iku_id][bulan akhir triwulan] = angka. Tidak ada entri = "Belum Ada Data".
    public function realisasi(int $tahun): array
    {
        return $this->ingatan[$tahun] ??= $this->bacaRealisasi($tahun);
    }

    private function bacaRealisasi(int $tahun): array
    {
        // Hanya nilai yang jenis periodenya sama dengan frekuensi indikator (aturan yang sama dengan SPM Track)
        $baris = $this->tabel('iku_nilai as n')
            ->join('iku as i', fn ($j) => $j->on('i.id', '=', 'n.iku_id')->on('n.periode_tipe', '=', 'i.frekuensi'))
            ->where('n.tahun', $tahun)
            ->get(['n.iku_id', 'n.periode_tipe', 'n.periode_no', 'n.nilai']);

        $hasil = [];

        foreach ($baris as $r) {
            $bulan = $this->bulanAkhir($r->periode_tipe, (int) $r->periode_no);
            $nilai = IkuParser::angka($r->nilai);

            if ($bulan !== null && $nilai !== null) {
                $hasil[(int) $r->iku_id][$bulan] = $nilai;
            }
        }

        return $hasil;
    }

    // Isian SPM Track dipetakan ke bulan akhir triwulan (3, 6, 9, 12) yang dipakai dashboard:
    //  triwulan : nomor 1-4 jadi bulan 3, 6, 9, 12
    //  bulanan  : hanya Mar, Jun, Sep, Des yang dipakai (realisasi bersifat kumulatif)
    //  tahunan  : dianggap nilai akhir tahun (triwulan IV)
    private function bulanAkhir(string $tipe, int $no): ?int
    {
        return match ($tipe) {
            'triwulan' => $no >= 1 && $no <= 4 ? $no * 3 : null,
            'bulanan'  => in_array($no, config('iku.bulan_triwulan'), true) ? $no : null,
            'tahunan'  => 12,
            default    => null,
        };
    }
}