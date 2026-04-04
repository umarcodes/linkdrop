<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomDomainProfile
{
    /**
     * Handle an incoming request.
     *
     * If a request arrives on a custom domain, inject the resolved username
     * as a request attribute so the frontend can discover it via the API.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);

        if ($host !== $appHost) {
            $user = User::where('custom_domain', $host)->first();
            if ($user) {
                $request->attributes->set('custom_domain_user', $user);
            }
        }

        return $next($request);
    }
}
