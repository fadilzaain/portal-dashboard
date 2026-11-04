<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IkuTarget extends IkuModel
{
    protected $table = 'iku_target';

    protected $fillable = [
        'indikator_id',
        'tahun',
        'target',
        'batas_bawah',
        'batas_atas',
    ];

    protected $casts = [
        'tahun'       => 'integer',
        'target'      => 'float',
        'batas_bawah' => 'float',
        'batas_atas'  => 'float',
    ];

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(IkuIndikator::class, 'indikator_id');
    }
}