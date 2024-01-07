<?php

namespace Mchev\Banhammer\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IpApiService
{
    /**
     * Get geolocation data for an IP address from the IP-API service.
     */
    public function getGeolocationData(string $ip): ?array
    {
        // Cache key for storing geolocation data
        $cacheKey = 'ip_geolocation_'.$ip;

        try {
            // Try to retrieve geolocation data from cache
            $geolocationData = Cache::remember($cacheKey, now()->addDay(), function () use ($ip) {
                $response = Http::get("http://ip-api.com/json/{$ip}?fields=status,message,countryCode,query");

                return $response->json();
            });

            return $geolocationData;
        } catch (\Exception $e) {
            // Log the error
            Log::error('IP-API Service Error: '.$e->getMessage(), ['exception' => $e]);

            // Handle the error as needed
            return null;
        }
    }
}
