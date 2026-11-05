<?php

// Dashboard IKU (Indikator Kinerja Utama).

return [

    // Sumber data IKU sekarang: koneksi ke database SPM Track read ONLY
    'sumber' => env('IKU_SUMBER_CONNECTION', 'spm'),

    // Data yang tidak disimpan di SPM Track, dicocokkan lewat NAMA indikator (harus sama persis dengan
    // kolom nama_indikator di SPM Track). Indikator baru tanpa entri di sini memakai 'metadata_bawaan'
    // dan dicatat sebagai peringatan di storage/logs.
    'metadata' => [
        'Nilai NDR' => [
            'satuan'           => '‰',
            'jenis_penilaian'  => 'lower', // semakin kecil semakin baik
            'definisi'         => null,
            'rumus'            => null,
            'penanggung_jawab' => null,
        ],
        'Persentase Capaian Indikator Nasional Mutu (INM) RS' => [
            'satuan'          => '%',
            'jenis_penilaian' => 'higher',
        ],
        'Status Akreditasi' => [
            'satuan'          => '%',
            'jenis_penilaian' => 'higher',
        ],
        'Indeks Reformasi Birokrasi Perangkat Daerah (IRB PD)' => [
            'satuan'          => 'skor',
            'jenis_penilaian' => 'higher',
        ],
    ],

    'metadata_bawaan' => [
        'satuan'          => '%',
        'jenis_penilaian' => 'higher',
    ],

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

    // Cara indikator diisi di SPM Track dan bagaimana dashboard menampilkannya (kunci = kolom `frekuensi` di tabel iku)
    'frekuensi' => [
        'triwulan' => 'Diisi per triwulan, kumulatif tahun berjalan',
        'bulanan'  => 'Diisi per bulan, ditampilkan pada akhir triwulan (Mar, Jun, Sep, Des)',
        'tahunan'  => 'Diisi sekali setahun, ditampilkan pada triwulan IV',
    ],

    // Bulan akhir tiap triwulan. Realisasi IKU diisi per triwulan dan bersifat kumulatif tahun berjalan,
    // jadi capaian tahunan = nilai pada periode terakhir yang sudah terisi.
    'bulan_triwulan' => [3, 6, 9, 12],
];