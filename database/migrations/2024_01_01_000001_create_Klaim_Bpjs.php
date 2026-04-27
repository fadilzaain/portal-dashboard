<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bpjs_klaim', function (Blueprint $table) {
            $table->id();

            // ── Identitas klaim ──
            $table->string('no_sep', 30)->unique()->comment('Nomor SEP BPJS');
            $table->string('no_kartu_bpjs', 20)->nullable()->index();

            // ── Data pasien ──
            $table->string('nama_pasien', 150);

            // ── Diagnosa ──
            $table->string('kode_diagnosa', 10)->nullable()->comment('Kode ICD-10');
            $table->string('diagnosa', 255)->nullable();

            // ── Tanggal ──
            $table->date('tgl_pengajuan')->index()->comment('Tanggal pengajuan klaim ke BPJS');
            $table->date('tgl_pelayanan')->nullable()->comment('Tanggal pasien dilayani');

            // ── Status klaim ──
            $table->enum('status', ['terbayar', 'pending', 'tidak_layak', 'diproses'])
                  ->default('diproses')
                  ->index();

            // ── Finansial ──
            $table->decimal('nominal', 15, 2)->default(0)->comment('Nominal pengajuan klaim (Rp)');
            $table->decimal('terbayar', 15, 2)->nullable()->comment('Nominal yang dibayarkan BPJS (Rp)');

            // ── Informasi tambahan ──
            $table->string('poli', 100)->nullable()->comment('Nama poli / unit layanan');
            $table->string('kelas_rawat', 10)->nullable()->comment('Kelas 1 / 2 / 3');
            $table->string('dokter', 150)->nullable();
            $table->text('keterangan')->nullable()->comment('Catatan atau alasan tidak layak');

            $table->timestamps();
            $table->softDeletes();

            // ── Composite indexes untuk query dashboard ──
            $table->index(['tgl_pengajuan', 'status'], 'idx_tgl_status');
            $table->index(['status', 'nominal'],       'idx_status_nominal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bpjs_klaim');
    }
};