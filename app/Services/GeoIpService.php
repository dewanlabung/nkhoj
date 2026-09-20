<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeoIpService
{
    public static function lookup(string $ip): array
    {
        // Skip private/loopback/reserved ranges — no point querying
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return ['city' => null, 'country' => null];
        }

        return Cache::remember("geoip:{$ip}", now()->addHours(24), function () use ($ip) {
            try {
                $r = Http::timeout(3)->get("http://ip-api.com/json/{$ip}?fields=status,city,countryCode");
                if ($r->ok() && $r->json('status') === 'success') {
                    return [
                        'city'    => $r->json('city')        ?: null,
                        'country' => $r->json('countryCode') ?: null,
                    ];
                }
            } catch (\Throwable) {}

            return ['city' => null, 'country' => null];
        });
    }
}
