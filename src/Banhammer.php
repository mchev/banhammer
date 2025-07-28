<?php

namespace Mchev\Banhammer;

use Illuminate\Support\Facades\Cache;

class Banhammer
{
    public static function unbanExpired(): void
    {
        config('ban.model')::expired()->delete();
        Cache::forget('banned-ips');
        Cache::forget('banned-ips-with-expiration');
    }

    public static function cleanExpiredCache(): void
    {
        Cache::forget('banned-ips');
        Cache::forget('banned-ips-with-expiration');
    }

    public static function clear(): void
    {
        config('ban.model')::onlyTrashed()->forceDelete();
    }
}
