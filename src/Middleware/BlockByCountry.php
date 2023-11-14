<?php

namespace Mchev\Banhammer\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mchev\Banhammer\Exceptions\BanhammerException;
use Mchev\Banhammer\Services\IpApiService;

class BlockByCountry
{
    protected IpApiService $ipApiService;

    public function __construct(IpApiService $ipApiService)
    {
        $this->ipApiService = $ipApiService;
    }

    public function handle(Request $request, Closure $next)
    {
        $blockedCountries = config('ban.blocked_countries');

        if ($blockedCountries && ! empty($blockedCountries)) {
            $ip = $request->ip();

            if (! is_null($ip)) {
                try {
                    // Get geolocation data using the IpApiService
                    $geolocationData = $this->ipApiService->getGeolocationData($ip);

                    if ($geolocationData['status'] === 'fail') {
                        Log::notice('Banhammer country check failure: '.$message, [
                            'ip' => $ip,
                        ]);
                    }

                    if ($this->isCountryBlocked($geolocationData, $blockedCountries)) {
                        throw new BanhammerException(config('ban.messages.country'));
                    }
                } catch (\Exception $e) {
                    Log::debug('Banhammer Exception: '.$e->getMessage(), [
                        'ip' => $ip,
                        'country' => $geolocationData['countryCode'] ?? null,
                    ]);

                    // Rethrow the exception to ensure the ban is enforced
                    throw new BanhammerException(config('ban.messages.country'));
                }
            }
        }

        return $next($request);
    }

    protected function isCountryBlocked(array $geolocationData, array $blockedCountries): bool
    {
        return isset($geolocationData['countryCode']) &&
            $this->ipApiService->isCountryBlocked($geolocationData['countryCode'], $blockedCountries);
    }
}
