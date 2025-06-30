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
        $userBanned = $request->user() && $request->user()->isBanned();
        $ipBanned = $request->ip() && in_array($request->ip(), $this->getBannedIPsFromCache());

        if ($userBanned || $ipBanned) {
            if ($userBanned) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                throw new BanhammerException(config('ban.messages.user'));
            } elseif ($ipBanned) {
                throw new BanhammerException(config('ban.messages.ip'));
            }
        }

        return $next($request);
    }

    protected function getBannedIPsFromCache()
    {
        return IP::getBannedIPsFromCache();
    }
}
