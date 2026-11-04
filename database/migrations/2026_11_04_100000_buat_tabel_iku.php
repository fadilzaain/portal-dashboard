<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Koneksi diambil dari config/iku.php (bawaan 'dashi')
    private function db(): \Illuminate\Database\Schema\Builder
    {
        return Schema::connection(config('iku.connection'));
    }

    public function up(): void
    {
        $db = $this->db();

        // 1) Sasaran strategis (kolom "KINERJA" di sheet IKU)
        if (! $db->hasTable('iku_sasaran')) {
            $db->create('iku_sasaran', function (Blueprint $t) {
                $t->id();
                $t->string('nama', 191)->unique();
                $t->unsignedSmallInteger('urutan')->default(0);
                $t->timestamps();
            });
        }

        // 2) Master indikator
        if (! $db->hasTable('iku_indikator')) {
            $db->create('iku_indikator', function (Blueprint $t) {
                $t->id();
                $t->foreignId('sasaran_id')->constrained('iku_sasaran')->restrictOnDelete();
                $t->string('nama', 191)->unique();
                $t->string('satuan', 12)->default('%');                 // contoh: %, ‰, skor
                $t->string('jenis_penilaian', 10)->default('higher');   // higher | lower | range
                $t->text('definisi')->nullable();
                $t->text('rumus')->nullable();
                $t->string('penanggung_jawab', 150)->nullable();
                $t->unsignedSmallInteger('urutan')->default(0);
                $t->boolean('is_active')->default(true);                // nonaktifkan, jangan hapus
                $t->timestamps();
            });
        }

        // 3) Target per indikator per tahun (target bisa berubah tiap tahun)
        if (! $db->hasTable('iku_target')) {
            $db->create('iku_target', function (Blueprint $t) {
                $t->id();
                $t->foreignId('indikator_id')->constrained('iku_indikator')->restrictOnDelete();
                $t->unsignedSmallInteger('tahun');
                $t->decimal('target', 15, 4);
                $t->decimal('batas_bawah', 15, 4)->nullable();          // khusus jenis "range"
                $t->decimal('batas_atas', 15, 4)->nullable();           // khusus jenis "range"
                $t->timestamps();
                $t->unique(['indikator_id', 'tahun']);
            });
        }

        // 4) Realisasi per periode. Tidak ada baris = "Belum Ada Data" (bukan 0).
        //    diinput_oleh = id user, sengaja TANPA foreign key karena tabel users bisa berada di database lain.
        if (! $db->hasTable('iku_realisasi')) {
            $db->create('iku_realisasi', function (Blueprint $t) {
                $t->id();
                $t->foreignId('indikator_id')->constrained('iku_indikator')->restrictOnDelete();
                $t->unsignedSmallInteger('tahun');
                $t->unsignedTinyInteger('bulan');                       // 3, 6, 9, 12 untuk triwulan
                $t->decimal('nilai', 15, 4);
                $t->unsignedBigInteger('diinput_oleh')->nullable();
                $t->timestamps();
                $t->unique(['indikator_id', 'tahun', 'bulan']);
                $t->index(['tahun', 'bulan']);
            });
        }

        // 5) Evaluasi dan rencana tindak lanjut per indikator per periode
        if (! $db->hasTable('iku_tindak_lanjut')) {
            $db->create('iku_tindak_lanjut', function (Blueprint $t) {
                $t->id();
                $t->foreignId('indikator_id')->constrained('iku_indikator')->restrictOnDelete();
                $t->unsignedSmallInteger('tahun');
                $t->unsignedTinyInteger('bulan');
                $t->text('catatan_evaluasi')->nullable();
                $t->text('rencana_tindak_lanjut')->nullable();
                $t->unsignedBigInteger('diinput_oleh')->nullable();
                $t->timestamps();
                $t->unique(['indikator_id', 'tahun', 'bulan']);
            });
        }
    }

    public function down(): void
    {
        $db = $this->db();

        // Urutan dibalik: tabel anak dulu, baru induk
        $db->dropIfExists('iku_tindak_lanjut');
        $db->dropIfExists('iku_realisasi');
        $db->dropIfExists('iku_target');
        $db->dropIfExists('iku_indikator');
        $db->dropIfExists('iku_sasaran');
    }
};