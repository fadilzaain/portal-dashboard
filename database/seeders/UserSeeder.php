<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Email dan password admin awal diambil dari .env (SEED_ADMIN_EMAIL, SEED_ADMIN_PASSWORD).
        // Tidak ada lagi password bawaan yang tertulis di kode.
        $email = env('SEED_ADMIN_EMAIL', 'admin@example.com');

        // Jangan menimpa akun yang sudah ada
        if (User::where('email', $email)->exists()) {
            $this->command?->info("Akun {$email} sudah ada, dilewati.");

            return;
        }

        // Kalau SEED_ADMIN_PASSWORD kosong, buat password acak lalu tampilkan sekali di terminal
        $password = env('SEED_ADMIN_PASSWORD') ?: Str::password(16, symbols: false);

        // Password otomatis di-hash oleh cast 'hashed' di model User
        User::create([
            'name'     => 'Admin',
            'email'    => $email,
            'password' => $password,
            'role'     => User::ROLE_ADMIN,
            'status'   => User::STATUS_AKTIF,
        ]);

        $this->command?->warn("Admin dibuat: {$email} | password: {$password}");
        $this->command?->warn('Simpan password ini sekarang, tidak akan ditampilkan lagi.');
    }
}