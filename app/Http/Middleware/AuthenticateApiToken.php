<?php

namespace App\Http\Middleware;

use App\Models\Pengguna;
use Closure;
use Illuminate\Http\Request;

class AuthenticateApiToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user = Pengguna::where('api_token_hash', hash('sha256', $token))
            ->where('status', 'aktif')
            ->first();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $request->attributes->set('auth_user', $user);

        return $next($request);
    }
}
