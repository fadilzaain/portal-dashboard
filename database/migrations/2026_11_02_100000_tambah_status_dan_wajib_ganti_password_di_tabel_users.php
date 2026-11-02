<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Status akun: menunggu / aktif / ditolak / nonaktif.
            // Akun yang sudah ada otomatis "aktif" (lewat default), pendaftar baru nanti diisi "menunggu" oleh kode.
            $table->string('status', 12)->default('aktif')->after('role');

            // true = akun memakai password sementara dari admin, wajib diganti sebelum memakai menu lain.
            // Akun yang sudah ada otomatis false (lewat default).
            $table->boolean('wajib_ganti_password')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['status', 'wajib_ganti_password']);
        });
    }
};