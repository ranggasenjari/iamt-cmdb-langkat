<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = Pengguna::where('email', $credentials['email'])->first();

        if (! $user || $user->status !== 'aktif' || ! Hash::check($credentials['password'], $user->password ?? '')) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password tidak sesuai.'],
            ]);
        }

        $token = Str::random(64);
        $user->forceFill([
            'api_token_hash' => hash('sha256', $token),
            'last_login_at' => now(),
        ])->save();

        return [
            'token' => $token,
            'user' => $this->userPayload($user->fresh()),
        ];
    }

    public function me(Request $request)
    {
        return ['user' => $this->userPayload($request->attributes->get('auth_user'))];
    }

    public function logout(Request $request)
    {
        $request->attributes->get('auth_user')?->forceFill(['api_token_hash' => null])->save();

        return response()->noContent();
    }

    private function userPayload(Pengguna $user): array
    {
        return [
            'id' => $user->id,
            'nama' => $user->nama,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
            'can_write' => $user->canWrite(),
        ];
    }
}
