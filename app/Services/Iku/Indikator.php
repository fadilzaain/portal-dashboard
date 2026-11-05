<?php

namespace App\Services\Iku;

// Satu indikator IKU untuk ditampilkan: data dari SPM Track + metadata dari config/iku.php.
final class Indikator
{
    public function __construct(
        public readonly int $id,                 // id tabel `iku` di SPM Track
        public readonly string $nama,
        public readonly string $sasaran,
        public readonly string $frekuensi,       // bulanan | triwulan | tahunan
        public readonly string $satuan,
        public readonly string $jenis_penilaian, // higher | lower | range
        public readonly ?string $definisi = null,
        public readonly ?string $rumus = null,
        public readonly ?string $penanggung_jawab = null,
    ) {}

    // Teks jenis penilaian untuk tampilan (diambil dari config, bukan ditulis ulang)
    public function labelJenis(): string
    {
        return config('iku.jenis_penilaian.' . $this->jenis_penilaian, $this->jenis_penilaian);
    }
}