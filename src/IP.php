<?php

namespace Mchev\Banhammer;

use Illuminate\Database\Eloquent\Builder;
use Mchev\Banhammer\Models\Ban;

class IP
{
    public static function ban(string|array $ips): void
    {
        $bannedIps = self::banned()->pluck('ip')->toArray();

        foreach ((array) $ips as $ip) {
            if (! in_array($ip, $bannedIps)) {
                Ban::create([
                    'ip' => $ip,
                ]);
            }
        }
    }

    public static function unban(string|array $ips): void
    {
        $ips = (array) $ips;
        Ban::whereIn('ip', $ips)->delete();
    }

    public static function isBanned(string $ip): bool
    {
        return Ban::where('ip', $ip)
            ->notExpired()
            ->exists();
    }

    public static function banned(): Builder
    {
        return Ban::whereNotNull('ip')
            ->select('id', 'ip', 'updated_at as banned_at')
            ->notExpired();
    }
}
