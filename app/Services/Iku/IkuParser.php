<?php

namespace App\Services\Iku;

// Mengubah teks isian SPM Track ("37,21‰", "100.00%", "81,93", "-") menjadi angka.
// Satuan TIDAK dibaca dari teks (isian di SPM bisa tidak konsisten); satuan diambil dari config/iku.php.
class IkuParser
{
    public static function angka(?string $teks): ?float
    {
        // Buang satuan dan spasi, sisakan angka, koma, titik, dan minus
        $bersih = preg_replace('/[^\d,.\-]/u', '', (string) $teks);

        // Kosong atau "-" = belum ada data (bukan 0)
        if ($bersih === '' || ! preg_match('/\d/', $bersih)) {
            return null;
        }

        $koma  = substr_count($bersih, ',');
        $titik = substr_count($bersih, '.');

        if ($koma > 0 && $titik > 0) {
            // Dua-duanya ada: yang muncul paling akhir adalah desimal, yang lain pemisah ribuan ("1.234,5")
            $desimal = strrpos($bersih, ',') > strrpos($bersih, '.') ? ',' : '.';
            $ribuan  = $desimal === ',' ? '.' : ',';
            $bersih  = str_replace($desimal, '.', str_replace($ribuan, '', $bersih));
        } elseif ($koma > 0) {
            // "37,21" = desimal; "1,234,567" = ribuan
            $bersih = str_replace(',', $koma === 1 ? '.' : '', $bersih);
        } elseif ($titik > 1) {
            $bersih = str_replace('.', '', $bersih); // "1.234.567" = ribuan
        }
        // Satu titik saja dianggap desimal ("100.00", "82.5")

        return is_numeric($bersih) ? (float) $bersih : null;
    }
}