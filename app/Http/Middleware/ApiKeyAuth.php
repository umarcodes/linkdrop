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
        $key = $request->header('X-API-Key') ?? $request->query('api_key');

        if (! $key) {
            return response()->json(['error' => 'API key required.'], 401);
        }

        $user = User::where('api_key', $key)->first();

        if (! $user) {
            return response()->json(['error' => 'Invalid API key.'], 401);
        }

        $request->merge(['_api_user' => $user]);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
