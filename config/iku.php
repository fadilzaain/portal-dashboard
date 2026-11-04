<?php

// Dashboard IKU (Indikator Kinerja Utama).
// Semua angka batas disini biar gak ditulis hardcode di Blade/JS.

return [

    // Koneksi database untuk semua tabel iku_*.
    'connection' => env('IKU_DB_CONNECTION', 'dashi'),

    // Batas status capaian persenan
    //  capaian >= tercapai                 - "Tercapai"
    //  capaian >= perhatian dan < tercapai - "Perlu Perhatian"
    //  capaian <  perhatian                - "Belum Tercapai"
    //  belum ada realisasi                 - "Belum Ada Data"
    'ambang_status' => [
        'tercapai'  => (float) env('IKU_AMBANG_TERCAPAI', 100),
        'perhatian' => (float) env('IKU_AMBANG_PERHATIAN', 90),
    ],

    // Jenis penilaian indikator (dipakai di form dan perhitungan capaian)
    'jenis_penilaian' => [
        'higher' => 'Higher is Better (semakin tinggi semakin baik)',
        'lower'  => 'Lower is Better (semakin rendah semakin baik)',
        'range'  => 'Target Range (harus berada dalam rentang)',
    ],

    // Bulan akhir tiap triwulan. Realisasi IKU diisi per triwulan dan bersifat kumulatif tahun berjalan,
    // jadi capaian tahunan = nilai pada periode terakhir yang sudah terisi.
    'bulan_triwulan' => [3, 6, 9, 12],
];