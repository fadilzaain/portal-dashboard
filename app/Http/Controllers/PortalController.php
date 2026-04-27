<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class PortalController extends Controller
{
    public function redirect(string $appId)
    {
        //konfigurasi app berdasarkan ID
        $app = $this->findApp($appId);

        if (! $app) {
            abort(404, 'Aplikasi tidak ditemukan.');
        }

        if ($app['auth_type'] === 'redirect') {
            return redirect($app['url']);
        }

        //Generate token sementara untuk SSO sederhana
        $token = $this->generateToken($app, auth()->user());

        //Buat URL tujuan dengan token sebagai query param
        $separator = str_contains($app['url'], '?') ? '&' : '?';
        $targetUrl  = $app['url'] . $separator . $app['token_param'] . '=' . $token;

        return redirect($targetUrl);
    }

   
    public function verifyToken(Request $request)
    {
        $token   = $request->query('token');
        $payload = $this->getTokenPayload($token);

        if (! $payload) {
            return response()->json(['valid' => false, 'message' => 'Token tidak valid atau sudah kadaluarsa.'], 401);
        }

        return response()->json([
            'valid'   => true,
            'user'    => [
                'id'    => $payload['user_id'],
                'name'  => $payload['name'],
                'email' => $payload['email'],
            ],
            'app_id'  => $payload['app_id'],
        ]);
    }

    
    public function apiVerify(Request $request)
    {
        $request->validate([
            'token'  => 'required|string',
            'app_id' => 'required|string',
        ]);

        $app = $this->findApp($request->app_id);

        if (! $app) {
            return response()->json(['valid' => false, 'message' => 'App tidak dikenal.'], 400);
        }

        //Opsional: validasi secret kalau dikonfigurasi
        if (! empty($app['api_secret'])) {
            $providedSecret = $request->header('X-Portal-Secret') ?? $request->input('secret');
            if ($providedSecret !== $app['api_secret']) {
                return response()->json(['valid' => false, 'message' => 'Secret tidak cocok.'], 403);
            }
        }

        $payload = $this->getTokenPayload($request->token);

        if (! $payload || $payload['app_id'] !== $request->app_id) {
            return response()->json(['valid' => false, 'message' => 'Token tidak valid atau sudah kadaluarsa.'], 401);
        }

        //Hapus token setelah dipakai
        Cache::forget('portal_token_' . $request->token);

        return response()->json([
            'valid' => true,
            'user'  => [
                'id'    => $payload['user_id'],
                'name'  => $payload['name'],
                'email' => $payload['email'],
            ],
        ]);
    }

    //PRIVATE HELPERS

    private function findApp(string $appId): ?array
    {
        $apps = config('portal.apps', []);

        foreach ($apps as $app) {
            if ($app['id'] === $appId) {
                return $app;
            }
        }

        return null;
    }

    private function generateToken(array $app, $user): string
    {
        $token = Str::random(64);

        Cache::put('portal_token_' . $token, [
            'user_id' => $user->id,
            'name'    => $user->name,
            'email'   => $user->email,
            'app_id'  => $app['id'],
        ], now()->addMinutes(5));

        return $token;
    }

    // Ambil payload dari cache berdasarkan token.
    private function getTokenPayload(string $token): ?array
    {
        return Cache::get('portal_token_' . $token);
    }
}