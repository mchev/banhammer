<?php

namespace Mchev\Banhammer;

use Carbon\Carbon;
use Mchev\Banhammer\Models\Ban;

class Banhammer
{
    public static function ban(array $ips): void
    {
        $bannedIps = self::bannedIps();

        foreach ($ips as $ip) {
            if (! in_array($ip, $bannedIps)) {
                Ban::create([
                    'ip' => $ip,
                ]);
            }
        }
    }

    public static function unban(array $ips): void
    {
        Ban::whereIn('ip', $ips)->delete();
    }

    public static function isIpBanned(string $ip): bool
    {
        return Ban::where('ip', $ip)
            ->where('expired_at', '>', Carbon::now()->format('Y-m-d H:i:s'))
            ->orWhereNull('expired_at')
            ->exists();
    }

    public static function bannedIps(): array
    {
        return Ban::whereNotNull('ip')
            ->where('expired_at', '>', Carbon::now()->format('Y-m-d H:i:s'))
            ->orWhereNull('expired_at')
            ->groupBy('ip')
            ->pluck('ip')
            ->toArray();
    }

    public static function unbanExpired(): void
    {
        Ban::query()
            ->where('expired_at', '<=', Carbon::now()->format('Y-m-d H:i:s'))
            ->delete();
    }

    public static function clear(): void
    {
        Ban::onlyTrashed()->forceDelete();
    }

}
