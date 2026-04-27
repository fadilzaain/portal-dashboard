<?php

return [
    'apps' => [
        [
            'id'          => 'pelayanan',
            'name'        => 'Data Pelayanan Pasien',
            'description' => 'Rawat Jalan, Rawat Inap, IGD — BOR, LOS, TOI, BTO',
            'icon'        => 'hospital',
            'color'       => 'blue',
            'auth_type'   => 'internal',
            'url'         => '/pelayanan',
        ],
        [
            'id'          => 'keuangan',
            'name'        => 'Data Keuangan',
            'description' => 'Laporan keuangan rumah sakit',
            'icon'        => 'money',
            'color'       => 'green',
            'auth_type'   => 'redirect',
            'url'         => 'http://keuangan.rs.local', // ganti ke dashboard monitoring keuangan 
        ],
        [
            'id'          => 'sdm',
            'name'        => 'Data SDM',
            'description' => 'Pegawai & kehadiran',
            'icon'        => 'users',
            'color'       => 'purple',
            'auth_type'   => 'internal',
            'url'         => '/sdm',
        ],
        [
            'id'          => 'mutu',
            'name'        => 'Indikator Mutu',
            'description' => 'Indikator mutu rumah sakit',
            'icon'        => 'chart',
            'color'       => 'rose',
            'auth_type'   => 'internal',
            'url'         => '/mutu',
        ],
        [
            'id'          => 'bpjs',
            'name'        => 'Klaim BPJS',
            'description' => 'Nominal & data pasien klaim BPJS',
            'icon'        => 'shield',
            'color'       => 'amber',
            'auth_type'   => 'internal',
            'url'         => '/bpjs',
        ],
    ],
];