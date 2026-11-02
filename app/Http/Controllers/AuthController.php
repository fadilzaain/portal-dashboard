<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Hanya akun berstatus "aktif" yang bisa masuk
        if (Auth::attempt($credentials + ['status' => User::STATUS_AKTIF], $request->boolean('remember'))) {
            $request->session()->regenerate();

            if (Auth::user()->isDirektur()) {
                $user = Auth::user();
                $user->setRememberToken(\Illuminate\Support\Str::random(60));
                $user->save();
                Auth::login($user, remember: true);
            }

            return redirect()->intended(route('dashboard'));
        }

        // Gagal: kalau password-nya benar tapi akun belum aktif, beri tahu alasannya.
        // Pesan khusus ini hanya muncul untuk yang tahu password, jadi tidak membocorkan data.
        $user = User::where('email', $credentials['email'])->first();
        $pesan = ($user && Hash::check($credentials['password'], $user->password))
            ? (User::PESAN_STATUS[$user->status] ?? null)
            : null;

        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => $pesan ?? 'Email atau password yang kamu masukkan salah.',
            ]);
    }

    /** Logout user.*/
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}