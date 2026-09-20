<?php

namespace App\Services;

class GeoIpService
{
    public static function lookup(string $ip): array
    {
        try {
            $location = geoip($ip);

            if ($location->default) {
                return ['city' => null, 'country' => null];
            }

            return [
                'city'    => $location->city    ?: null,
                'country' => $location->iso_code ?: null,
            ];
        } catch (\Throwable) {
            return ['city' => null, 'country' => null];
        }
    }
}
