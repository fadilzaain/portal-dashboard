<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DaftarController extends Controller
{
    // Pesan kesalahan dalam bahasa Indonesia
    private const PESAN = [
        'required' => ':attribute wajib diisi.',
        'max' => ':attribute maksimal :max karakter.',
        'min' => ':attribute minimal :min karakter.',
        'email' => 'Format :attribute tidak valid.',
        'unique' => ':attribute sudah terdaftar. Kalau lupa password atau pendaftaran sebelumnya ditolak, hubungi admin.',
        'confirmed' => 'Konfirmasi password tidak sama.',
    ];

    // Nama kolom yang tampil di pesan kesalahan
    private const ATRIBUT = [
        'name' => 'Nama lengkap',
        'email' => 'Email',
        'password' => 'Password',
    ];

    // Halaman form daftar
    public function form(): View
    {
        return view('auth.register');
    }

    /**
     * Simpan pendaftaran baru. Akun berstatus "menunggu" dan belum bisa login
     * sampai admin menyetujuinya. Role selalu "staff": admin tidak bisa dibuat lewat sini,
     * dan pendaftar tidak bisa memilih role atau status sendiri.
     */
    public function proses(Request $request): RedirectResponse
    {
        // Email diseragamkan (huruf kecil, tanpa spasi) sebelum dicek unik
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], self::PESAN, self::ATRIBUT);

        // Hanya field yang ditulis eksplisit di sini yang disimpan (bukan $request->all()),
        // jadi role dan status tidak bisa dipalsukan lewat form.
        // Password otomatis di-hash oleh cast 'hashed' di model User.
        User::create([
            'name' => trim($data['name']),
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => User::ROLE_STAFF,
            'status' => User::STATUS_MENUNGGU,
        ]);

        return redirect()->route('login')
            ->with('status', 'Pendaftaran terkirim. Akun kamu bisa dipakai setelah disetujui admin.');
    }
}