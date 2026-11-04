<?php

namespace Database\Seeders;

use App\Models\IkuIndikator;
use App\Models\IkuRealisasi;
use App\Models\IkuSasaran;
use App\Models\IkuTarget;
use Illuminate\Database\Seeder;

// Mengisi data IKU awal dari Google Sheet "IKU" (tab 2025 dan 2026). sesuai data sheet
class IkuSeeder extends Seeder
{
    private const SASARAN_1 = 'Terwujudnya Pelayanan Kesehatan yang Unggul, Aman, dan Berorientasi pada Keselamatan Pasien';
    private const SASARAN_2 = 'Meningkatnya Kualitas Pelayanan Kesehatan di RSUD Kabupaten Jombang';
    private const SASARAN_3 = 'Terwujudnya Tata Kelola Birokrasi Perangkat Daerah yang bersih dan efektif';

    // target[tahun] = target; realisasi[tahun][bulan akhir triwulan] = nilai
    // Periode yang di sheet berisi "-" atau kosong sengaja TIDAK dimasukkan (artinya "Belum Ada Data")
    private const DATA = [
        [
            'sasaran'         => self::SASARAN_1,
            'nama'            => 'Nilai NDR',
            'satuan'          => '‰',
            'jenis_penilaian' => 'lower', // NDR: semakin kecil semakin baik
            'target'          => [2025 => 40, 2026 => 38],
            'realisasi'       => [
                2025 => [3 => 37.21, 6 => 39.27, 9 => 44.54, 12 => 46.62],
                2026 => [3 => 44.17, 6 => 38.61],
            ],
        ],
        [
            'sasaran'         => self::SASARAN_2,
            'nama'            => 'Persentase Capaian Indikator Nasional Mutu (INM) RS',
            'satuan'          => '%',
            'jenis_penilaian' => 'higher',
            'target'          => [2025 => 80, 2026 => 82],
            'realisasi'       => [
                2025 => [3 => 76.92, 6 => 84.62, 9 => 84.62, 12 => 84.62],
                2026 => [3 => 100, 6 => 100],
            ],
        ],
        [
            'sasaran'         => self::SASARAN_2,
            'nama'            => 'Status Akreditasi',
            'satuan'          => '%',
            'jenis_penilaian' => 'higher',
            'target'          => [2025 => 100, 2026 => 100],
            'realisasi'       => [
                2025 => [3 => 100, 6 => 100, 9 => 100, 12 => 100],
                2026 => [3 => 100, 6 => 100],
            ],
        ],
        [
            'sasaran'         => self::SASARAN_3,
            'nama'            => 'Indeks Reformasi Birokrasi Perangkat Daerah (IRB PD)',
            'satuan'          => 'skor',
            'jenis_penilaian' => 'higher',
            'target'          => [2025 => 82, 2026 => 82.5],
            'realisasi'       => [
                2025 => [6 => 81.93, 9 => 81.93],
                2026 => [6 => 80.74],
            ],
        ],
    ];

    public function run(): void
    {
        foreach (self::DATA as $i => $d) {
            // Sasaran strategis: dibuat kalau belum ada, urutannya mengikuti urutan kemunculan
            $sasaran = IkuSasaran::firstOrNew(['nama' => $d['sasaran']]);
            if (! $sasaran->exists) {
                $sasaran->urutan = IkuSasaran::count() + 1;
                $sasaran->save();
            }

            $indikator = IkuIndikator::firstOrCreate(
                ['nama' => $d['nama']],
                [
                    'sasaran_id'      => $sasaran->id,
                    'satuan'          => $d['satuan'],
                    'jenis_penilaian' => $d['jenis_penilaian'],
                    'urutan'          => $i + 1,
                    'is_active'       => true,
                ]
            );

            foreach ($d['target'] as $tahun => $nilai) {
                IkuTarget::firstOrCreate(
                    ['indikator_id' => $indikator->id, 'tahun' => $tahun],
                    ['target' => $nilai]
                );
            }

            foreach ($d['realisasi'] as $tahun => $perBulan) {
                foreach ($perBulan as $bulan => $nilai) {
                    IkuRealisasi::firstOrCreate(
                        ['indikator_id' => $indikator->id, 'tahun' => $tahun, 'bulan' => $bulan],
                        ['nilai' => $nilai]
                    );
                }
            }
        }

        $this->command?->info('IkuSeeder selesai: ' . IkuIndikator::count() . ' indikator, ' . IkuRealisasi::count() . ' data realisasi.');
    }
}