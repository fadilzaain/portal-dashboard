<?php

namespace App\Services;

/**
 * Resolusi kategori besar (Dokter/Perawat/Farmasi/Medis Lainnya/Lainnya) dari nama jabatan.
 * Dipakai bareng oleh BezettingService & SdmPerJenisService biar mapping-nya cuma didefinisikan
 * sekali di satu tempat (sebelumnya duplikat di masing-masing service).
 */
class JabatanKategori
{
    // Kata kunci per kategori (lowercase, dicocokkan pakai str_contains)
    private const MAP = [
        'Dokter'        => ['dokter', 'dr.'],
        'Perawat'       => ['perawat', 'bidan', 'penata anest', 'asisten penata'],
        'Farmasi'       => ['apoteker', 'asisten apoteker'],
        'Medis Lainnya' => [
            'teknisi', 'nutrisionis', 'fisioterapi', 'analis', 'radiografer',
            'perekam', 'sanitarian', 'terapis', 'okupasi', 'ortosis',
            'refraksionis', 'fisikawan', 'psikologi',
        ],
    ];

    // Urutan tampil kategori di UI (dipakai controller/service biar konsisten di semua tempat)
    public const URUTAN = ['Dokter', 'Perawat', 'Farmasi', 'Medis Lainnya', 'Lainnya'];

    public static function resolve(string $jabatan): string
    {
        $lower = strtolower($jabatan);

        foreach (self::MAP as $kategori => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($lower, $kw)) {
                    return $kategori;
                }
            }
        }

        return 'Lainnya';
    }
}