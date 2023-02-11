<?php

namespace Mchev\Banhammer\Middleware;

use Closure;
use Mchev\Banhammer\Banhammer;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateBanned
{
    public function handle($request, Closure $next): Response
    {
        if ($request->user() && $request->user()->isBanned()
            || $request->ip() && Banhammer::isIpBanned($request->ip())) {

            if($request->user()) {
                $request->user()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return (config('ban.fallback_url'))
                ? redirect(config('ban.fallback_url'))
                : abort(403, config('ban.message'));
        }

        return $next($request);
    }
}
