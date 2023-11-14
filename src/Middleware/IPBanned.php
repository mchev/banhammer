<?php

namespace Mchev\Banhammer\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Mchev\Banhammer\Exceptions\BanhammerException;
use Mchev\Banhammer\IP;
use Symfony\Component\HttpFoundation\Response;

class IPBanned
{
    public function handle($request, Closure $next): Response
    {
        try {
            $bannedIPs = IP::getBannedIPsFromCache();

            if ($request->ip() && in_array($request->ip(), $bannedIPs)) {
                throw new BanhammerException(config('ban.messages.ip'));
            }
        } catch (\Exception $e) {
            // Log the exception
            Log::error('IPBanned Middleware Exception: '.$e->getMessage(), ['exception' => $e]);

            throw $e;
        }

        return $next($request);
    }
}
