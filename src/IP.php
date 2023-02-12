<?php

namespace Mchev\Banhammer;

use Carbon\Carbon;
use Mchev\Banhammer\Models\Ban;
use Illuminate\Database\Eloquent\Builder;

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
            ->where('expired_at', '>', Carbon::now()->format('Y-m-d H:i:s'))
            ->orWhereNull('expired_at')
            ->exists();
    }

    public static function banned(): Builder
    {
        return Ban::whereNotNull('ip')
            ->select('id', 'ip', 'updated_at as banned_at')
            ->where('expired_at', '>', Carbon::now()->format('Y-m-d H:i:s'))
            ->orWhereNull('expired_at');
    }

}
