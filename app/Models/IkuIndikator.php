<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IkuIndikator extends IkuModel
{
    protected $table = 'iku_indikator';

    protected $fillable = [
        'sasaran_id',
        'nama',
        'satuan',
        'jenis_penilaian',
        'definisi',
        'rumus',
        'penanggung_jawab',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'urutan'    => 'integer',
        'is_active' => 'boolean',
    ];

    // ─── Relasi ───────────────────────────────────────────────────────────────

    public function sasaran(): BelongsTo
    {
        return $this->belongsTo(IkuSasaran::class, 'sasaran_id');
    }

    public function target(): HasMany
    {
        return $this->hasMany(IkuTarget::class, 'indikator_id');
    }

    public function realisasi(): HasMany
    {
        return $this->hasMany(IkuRealisasi::class, 'indikator_id');
    }

    public function tindakLanjut(): HasMany
    {
        return $this->hasMany(IkuTindakLanjut::class, 'indikator_id');
    }

    // ─── Scope ────────────────────────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeUrut($query)
    {
        return $query->orderBy('urutan')->orderBy('id');
    }

    // ─── Accessor ─────────────────────────────────────────────────────────────

    // Teks jenis penilaian untuk tampilan (diambil dari config, bukan ditulis ulang)
    public function getLabelJenisPenilaianAttribute(): string
    {
        return config('iku.jenis_penilaian.' . $this->jenis_penilaian, $this->jenis_penilaian);
    }
}