<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Master indikator mutu
        Schema::create('indikator_mutu', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->string('nama');
            $table->enum('jenis_mutu', ['nasional', 'prioritas']);
            $table->decimal('target', 8, 2)->comment('Target capaian (%)');
            $table->boolean('is_lower_better')->default(false)->comment('True jika nilai lebih rendah = lebih baik (misal: penundaan operasi)');
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Capaian per bulan
        Schema::create('capaian_indikator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indikator_mutu_id')->constrained('indikator_mutu')->onDelete('cascade');
            $table->year('tahun');
            $table->tinyInteger('triwulan')->comment('1-4');
            $table->tinyInteger('bulan')->comment('1-12');
            $table->decimal('numerator', 10, 2)->comment('Pembilang');
            $table->decimal('denominator', 10, 2)->comment('Penyebut');
            $table->decimal('capaian', 8, 2)->virtualAs('ROUND((numerator / NULLIF(denominator, 0)) * 100, 2)')->comment('Capaian otomatis (%)');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['indikator_mutu_id', 'tahun', 'bulan'], 'unique_capaian_per_bulan');
            $table->index(['tahun', 'triwulan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capaian_indikator');
        Schema::dropIfExists('indikator_mutu');
    }
};