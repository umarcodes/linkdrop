<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-API-Key');

        if (! $key) {
            return response()->json(['error' => 'API key required.'], 401);
        }

        $hashed = hash_hmac('sha256', $key, config('app.key'));
        $user = User::where('api_key', $hashed)->first();

        if (! $user) {
            return response()->json(['error' => 'Invalid API key.'], 401);
        }

        $request->merge(['_api_user' => $user]);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
