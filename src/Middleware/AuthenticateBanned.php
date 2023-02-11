<?php

namespace Mchev\Banhammer\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateBanned
{
    public function handle($request, Closure $next): Response
    {
        if ($request->user() && $request->user()->isBanned()) {
            return (config('ban.fallback_url'))
                ? redirect(config('ban.fallback_url'))
                : redirect()->back();
        }

        return $next($request);
    }
}
