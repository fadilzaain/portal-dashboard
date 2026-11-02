<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GantiPasswordController extends Controller
{
    // Pesan kesalahan dalam bahasa Indonesia
    private const PESAN = [
        'required' => ':attribute wajib diisi.',
        'min' => ':attribute minimal :min karakter.',
        'confirmed' => 'Konfirmasi password baru tidak sama.',
        'current_password' => 'Password lama salah.',
        'different' => 'Password baru harus berbeda dari password lama.',
    ];

    // Nama kolom yang tampil di pesan kesalahan
    private const ATRIBUT = [
        'password_lama' => 'Password lama',
        'password' => 'Password baru',
    ];

    // Halaman form ganti password (semua role yang sudah login)
    public function form(): View
    {
        return view('akun.password');
    }

    // Simpan password baru
    public function simpan(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'password_lama' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:password_lama'],
        ], self::PESAN, self::ATRIBUT);

        // Password otomatis di-hash oleh cast 'hashed' di model User.
        // Penanda "wajib ganti" dimatikan supaya akun bisa membuka menu lain lagi.
        $request->user()->update([
            'password' => $data['password'],
            'wajib_ganti_password' => false,
        ]);

        // Ganti ID sesi setelah perubahan sensitif
        $request->session()->regenerate();

        return redirect()->route('akun.password')->with('status', 'Password berhasil diganti.');
    }
}