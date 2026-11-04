<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class IkuSasaran extends IkuModel
{
    protected $table = 'iku_sasaran';

    protected $fillable = ['nama', 'urutan'];

    protected $casts = [
        'urutan' => 'integer',
    ];

    // Satu sasaran strategis punya banyak indikator
    public function indikator(): HasMany
    {
        return $this->hasMany(IkuIndikator::class, 'sasaran_id');
    }
}