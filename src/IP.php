<?php

namespace Mchev\Banhammer;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class IP
{
    public static function ban(string|array $ips, array $metas = [], ?string $date = null): void
    {
        foreach ((array) $ips as $ip) {
            // Check if IP is already banned and not expired by querying database directly
            $existingBan = config('ban.model')::where('ip', $ip)
                ->notExpired()
                ->first();

            if (! $existingBan) {
                config('ban.model')::create([
                    'ip' => $ip,
                    'metas' => count($metas) ? $metas : null,
                    'expired_at' => $date,
                ]);
            }
        }
    }

    public static function unban(string|array $ips): void
    {
        $ips = (array) $ips;
        config('ban.model')::whereIn('ip', $ips)->delete();
        Cache::forget('banned-ips');
        Cache::forget('banned-ips-with-expiration');
    }

    public static function isBanned(string $ip): bool
    {
        // For now, use database query directly to ensure accuracy
        // TODO: Optimize with proper cache implementation
        return config('ban.model')::where('ip', $ip)
            ->notExpired()
            ->exists();
    }

    public static function banned(): Builder
    {
        return config('ban.model')::whereNotNull('ip')
            ->with('createdBy')
            ->notExpired();
    }

    public static function getBannedIPsFromCache(): array
    {
        return Cache::remember('banned-ips', now()->addMinutes(5), function () {
            return self::banned()->pluck('ip')->unique()->toArray();
        });
    }

    public static function getBannedIPsWithExpiration(): array
    {
        return Cache::remember('banned-ips-with-expiration', now()->addMinutes(5), function () {
            return config('ban.model')::whereNotNull('ip')
                ->select('ip', 'expired_at')
                ->get()
                ->mapWithKeys(function ($ban) {
                    return [$ban->ip => $ban->expired_at?->timestamp];
                })
                ->toArray();
        });
    }
}
