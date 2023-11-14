<?php

namespace Mchev\Banhammer\Middleware;

use Closure;
use Mchev\Banhammer\Exceptions\BanhammerException;
use Symfony\Component\HttpFoundation\Response;

class AuthBanned
{
    public function handle($request, Closure $next): Response
    {
        if ($request->user() && $request->user()->isBanned()) {
            throw new BanhammerException(config('ban.messages.user'));
        }

        return $next($request);
    }
}
