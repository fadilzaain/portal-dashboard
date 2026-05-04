<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IndikatorMutu extends Model
{
    use HasFactory;

    protected $table = 'indikator_mutu';

    protected $fillable = [
        'kode',
        'nama',
        'jenis_mutu',
        'target',
        'is_lower_better',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'target'          => 'float',
        'is_lower_better' => 'boolean',
        'is_active'       => 'boolean',
    ];

    // ─── Relasi ───────────────────────────────────────────────────────────────

    public function capaian(): HasMany
    {
        return $this->hasMany(CapaianIndikator::class, 'indikator_mutu_id');
    }

    // ─── Scope ────────────────────────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeJenisMutu($query, string $jenis)
    {
        return $query->where('jenis_mutu', $jenis);
    }

    // ─── Accessor ─────────────────────────────────────────────────────────────

    public function getLabelJenisMutuAttribute(): string
    {
        return match ($this->jenis_mutu) {
            'nasional'  => 'Nasional',
            'prioritas' => 'Prioritas',
            default     => ucfirst($this->jenis_mutu),
        };
    }
}