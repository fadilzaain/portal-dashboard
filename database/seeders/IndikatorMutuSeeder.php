<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IndikatorMutuSeeder extends Seeder
{
    public function run(): void
    {
        $indikators = [
            [
                'kode'            => 'IKN-01',
                'nama'            => 'Kepatuhan Identifikasi Pasien',
                'jenis_mutu'      => 'nasional',
                'target'          => 100.00,
                'is_lower_better' => false,
                'deskripsi'       => 'Persentase kepatuhan petugas dalam melakukan identifikasi pasien sesuai standar (minimal 2 identitas).',
            ],
            [
                'kode'            => 'IKN-02',
                'nama'            => 'Penundaan Operasi Elektif',
                'jenis_mutu'      => 'nasional',
                'target'          => 5.00,
                'is_lower_better' => true,
                'deskripsi'       => 'Persentase pasien operasi elektif yang mengalami penundaan > 24 jam dari jadwal.',
            ],
            [
                'kode'            => 'IKN-03',
                'nama'            => 'Kepatuhan Jam Visite Dokter Spesialis',
                'jenis_mutu'      => 'nasional',
                'target'          => 80.00,
                'is_lower_better' => false,
                'deskripsi'       => 'Persentase dokter spesialis yang melakukan visite pada pukul 06.00–14.00.',
            ],
            [
                'kode'            => 'IKP-01',
                'nama'            => 'Kepatuhan Upaya Pencegahan Risiko Cedera Akibat Pasien Jatuh pada Pasien Rawat Inap',
                'jenis_mutu'      => 'prioritas',
                'target'          => 100.00,
                'is_lower_better' => false,
                'deskripsi'       => 'Persentase kepatuhan penerapan langkah pencegahan jatuh pada pasien rawat inap berisiko jatuh.',
            ],
        ];

        foreach ($indikators as $indikator) {
            DB::table('indikator_mutu')->updateOrInsert(
                ['kode' => $indikator['kode']],
                array_merge($indikator, [
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // Seed capaian dummy untuk demo tampilan
        $this->seedCapaianDummy();
    }

    private function seedCapaianDummy(): void
    {
        $indikators = DB::table('indikator_mutu')->get();
        $tahun      = date('Y');

        // Data dummy per bulan untuk tahun berjalan
        $dummyData = [
            'IKN-01' => [95, 97, 98, 96, 99, 100, 98, 97, 99, 100, 98, 99], // num/denum ratio
            'IKN-02' => [4,  3,  5,  2,  4,  3,  5,  4,  3,  2,  4,  3],
            'IKN-03' => [75, 78, 80, 82, 79, 83, 85, 81, 80, 84, 82, 85],
            'IKP-01' => [90, 92, 95, 93, 96, 97, 98, 95, 97, 99, 98, 100],
        ];

        $denominators = [
            'IKN-01' => 200, 'IKN-02' => 80, 'IKN-03' => 120, 'IKP-01' => 150,
        ];

        foreach ($indikators as $ind) {
            $pctList = $dummyData[$ind->kode] ?? [];
            $denum   = $denominators[$ind->kode] ?? 100;

            foreach ($pctList as $bulanIdx => $pct) {
                $bulan    = $bulanIdx + 1;
                $triwulan = (int) ceil($bulan / 3);
                $num      = round(($pct / 100) * $denum, 2);

                DB::table('capaian_indikator')->updateOrInsert(
                    [
                        'indikator_mutu_id' => $ind->id,
                        'tahun'             => $tahun,
                        'bulan'             => $bulan,
                    ],
                    [
                        'triwulan'    => $triwulan,
                        'numerator'   => $num,
                        'denominator' => $denum,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]
                );
            }
        }
    }
}