<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IkuTindakLanjut extends IkuModel
{
    protected $table = 'iku_tindak_lanjut';

    protected $fillable = [
        'indikator_id',
        'tahun',
        'bulan',
        'catatan_evaluasi',
        'rencana_tindak_lanjut',
        'diinput_oleh',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'bulan' => 'integer',
    ];

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(IkuIndikator::class, 'indikator_id');
    }
}