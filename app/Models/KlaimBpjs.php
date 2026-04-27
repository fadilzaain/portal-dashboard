<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class KlaimBpjs extends Model
{
    use HasFactory;

    protected $table = 'Klaim_Bpjs';

    protected $fillable = [
        'no_sep',
        'nama_pasien',
        'no_kartu_bpjs',
        'diagnosa',
        'kode_diagnosa',
        'tgl_pengajuan',
        'tgl_pelayanan',
        'status',        // terbayar | pending | tidak_layak | diproses
        'nominal',
        'terbayar',
        'keterangan',
        'poli',
        'kelas_rawat',
        'dokter',
    ];

    protected $casts = [
        'tgl_pengajuan'  => 'date',
        'tgl_pelayanan'  => 'date',
        'nominal'        => 'float',
        'terbayar'       => 'float',
    ];


    //Filter status terbayar
    public function scopeTerbayar($query)
    {
        return $query->where('status', 'terbayar');
    }

    //Filter status pending
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    //Filter status tidak_layak
    public function scopeTidakLayak($query)
    {
        return $query->where('status', 'tidak_layak');
    }

    //Filter status diproses
    public function scopeDiproses($query)
    {
        return $query->where('status', 'diproses');
    }

    //Filter berdasarkan rentang tanggal pengajuan
    public function scopeDateRange($query, Carbon $from, Carbon $to)
    {
        return $query->whereBetween('tgl_pengajuan', [$from, $to]);
    }

    //label status
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'terbayar'    => 'Terbayar',
            'pending'     => 'Pending',
            'tidak_layak' => 'Tidak Layak',
            'diproses'    => 'Diproses',
            default       => ucfirst($this->status ?? '–'),
        };
    }

    public function getNominalRupiahAttribute(): string
    {
        return 'Rp ' . number_format($this->nominal ?? 0, 0, ',', '.');
    }

    public function getTerbayarRupiahAttribute(): string
    {
        return 'Rp ' . number_format($this->terbayar ?? 0, 0, ',', '.');
    }
}