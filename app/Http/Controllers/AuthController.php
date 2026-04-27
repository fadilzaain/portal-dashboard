<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Validasi input
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Coba login
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        // Gagal login
        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => 'Email atau password yang kamu masukkan salah.',
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

    /**
 * Tampilkan halaman register.
 */
public function showRegister()
{
    return view('auth.register');
}

/**
 * Proses form register.
 */
public function register(Request $request)
{
    $request->validate([
        'name'                  => ['required', 'string', 'max:255'],
        'email'                 => ['required', 'email', 'unique:users,email'],
        'password'              => ['required', 'min:8', 'confirmed'],
    ]);

    $user = \App\Models\User::create([
        'name'     => $request->name,
        'email'    => $request->email,
        'password' => \Illuminate\Support\Facades\Hash::make($request->password),
    ]);

    Auth::login($user);

    return redirect()->route('dashboard');
}
}