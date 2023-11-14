<?php

namespace Mchev\Banhammer\Middleware;

use Closure;
use Mchev\Banhammer\Exceptions\BanhammerException;
use Mchev\Banhammer\IP;
use Symfony\Component\HttpFoundation\Response;

class LogoutBanned
{
    public function handle($request, Closure $next): Response
    {
        if ($request->user() && $request->user()->isBanned()
            || $request->ip() && in_array($request->ip(), IP::getBannedIPsFromCache())) {
            if ($request->user()) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            throw new BanhammerException(config('ban.messages.user'));
        }

        return $next($request);
    }
}
