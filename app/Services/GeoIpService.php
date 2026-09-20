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
                $r = Http::timeout(4)->get("https://ipapi.co/{$ip}/json/");
                if ($r->ok() && !$r->json('error')) {
                    return [
                        'city'    => $r->json('city')         ?: null,
                        'country' => $r->json('country_code') ?: null,
                    ];
                }
            } catch (\Throwable) {}

            return ['city' => null, 'country' => null];
        });
    }
}
