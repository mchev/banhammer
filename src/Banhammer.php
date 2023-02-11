<?php

namespace Mchev\Banhammer;

use Mchev\Banhammer\Models\Ban;
use Carbon\Carbon;

class Banhammer {

	static public function ban(array $ips): void
	{
		$bannedIps = self::bannedIps();

		foreach ($ips as $ip) {
			if(!in_array($ip, $bannedIps)) {
			 	Ban::create([
			 		'ip' => $ip
			 	]);
			}
		}
	}

	static public function unban(array $ips): void
	{
		Ban::whereIn('ip', $ips)->delete();
	}

	static public function isIpBanned(string $ip): bool
	{
		return Ban::where('ip', $ip)
			->where('expired_at', '>', Carbon::now()->format('Y-m-d H:i:s'))
			->orWhereNull('expired_at')
			->exists();
	}

	static public function bannedIps(): array
	{
		return Ban::whereNotNull('ip')
			->where('expired_at', '>', Carbon::now()->format('Y-m-d H:i:s'))
			->orWhereNull('expired_at')
			->groupBy('ip')
			->pluck('ip')
			->toArray();
	}

	static public function unbanExpired(): void
	{
		Ban::query()
			->where('expired_at', '<=', Carbon::now()->format('Y-m-d H:i:s'))
			->delete();
	}

}