<?php

namespace Mchev\Banhammer\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Mchev\Banhammer\Exceptions\BanhammerException;
use Mchev\Banhammer\Services\IpApiService;

class BlockByCountry
{
    public function __construct(protected IpApiService $ipApiService)
    {
        //
    }

    public function handle(Request $request, Closure $next)
    {
        $blockedCountries = config('ban.blocked_countries');

        if ($blockedCountries && ! empty($blockedCountries)) {
            $ip = $request->ip();

            if (! is_null($ip)) {
                $cacheKey = 'banhammer_'.$ip;
                $cachedResult = Cache::get($cacheKey);

                if ($cachedResult === null) {
                    try {
                        $geolocationData = $this->ipApiService->getGeolocationData($ip);

                        if ($geolocationData['status'] === 'fail') {
                            Log::notice('Banhammer country check failure: '.$geolocationData['message'], [
                                'ip' => $ip,
                            ]);
                            Cache::put($cacheKey, 'allowed', now()->addMinutes(config('ban.cache_duration')));
                        } else {
                            if (in_array($geolocationData['countryCode'], $blockedCountries)) {
                                Cache::put($cacheKey, 'blocked', now()->addMinutes(config('ban.cache_duration')));
                                throw new BanhammerException(config('ban.messages.country'));
                            }
                        }

                    } catch (\Exception $e) {
                        Log::debug('Banhammer Exception: '.$e->getMessage(), [
                            'ip' => $ip,
                            'country' => $geolocationData['countryCode'] ?? null,
                        ]);
                        throw new BanhammerException(config('ban.messages.country'));
                    }
                } elseif ($cachedResult === 'blocked') {
                    throw new BanhammerException(config('ban.messages.country'));
                }
            }
        }

        return $next($request);
    }
}
