<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IkuRealisasi extends IkuModel
{
    protected $table = 'iku_realisasi';

    protected $fillable = [
        'indikator_id',
        'tahun',
        'bulan',
        'nilai',
        'diinput_oleh',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'bulan' => 'integer',
        'nilai' => 'float',
    ];

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(IkuIndikator::class, 'indikator_id');
    }

    // Nomor triwulan dari bulan akhir periode (3→1, 6→2, 9→3, 12→4)
    public function getTriwulanAttribute(): int
    {
        return (int) ceil($this->bulan / 3);
    }
}