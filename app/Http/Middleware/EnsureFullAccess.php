<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureFullAccess
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->attributes->get('auth_user')?->canWrite()) {
            return response()->json(['message' => 'Akses read only tidak dapat mengubah data.'], 403);
        }

        return $next($request);
    }
}
