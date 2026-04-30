<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CapaianIndikator extends Model
{
    use HasFactory;

    protected $table = 'capaian_indikator';

    protected $fillable = [
        'indikator_mutu_id',
        'tahun',
        'triwulan',
        'bulan',
        'numerator',
        'denominator',
        'keterangan',
    ];

    protected $casts = [
        'tahun'        => 'integer',
        'triwulan'     => 'integer',
        'bulan'        => 'integer',
        'numerator'    => 'float',
        'denominator'  => 'float',
    ];

    // ─── Relasi ───────────────────────────────────────────────────────────────

    public function indikatorMutu(): BelongsTo
    {
        return $this->belongsTo(IndikatorMutu::class, 'indikator_mutu_id');
    }

    // ─── Accessor ─────────────────────────────────────────────────────────────

    /**
     * Hitung capaian (%) secara manual jika virtual column tidak tersedia
     */
    public function getCapaianAttribute(): float
    {
        if (!$this->denominator || $this->denominator == 0) {
            return 0;
        }
        return round(($this->numerator / $this->denominator) * 100, 2);
    }

    public function getNamaBulanAttribute(): string
    {
        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April',   5 => 'Mei',       6 => 'Juni',
            7 => 'Juli',    8 => 'Agustus',   9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        return $bulan[$this->bulan] ?? '-';
    }

    public function getNamaBulanShortAttribute(): string
    {
        $bulan = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar',
            4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agt', 9 => 'Sep',
            10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];
        return $bulan[$this->bulan] ?? '-';
    }
}