<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckIpBan
{
    public function handle(Request $request, Closure $next): mixed
    {
        $settings = [];
        $path = storage_path('app/site_settings.json');
        if (file_exists($path)) {
            $settings = json_decode(file_get_contents($path), true) ?? [];
        }

        $bannedIps = $settings['security']['banned_ips'] ?? [];

        if (empty($bannedIps)) {
            return $next($request);
        }

        $clientIp = $request->ip();

        foreach ($bannedIps as $entry) {
            $entry = trim($entry);
            if (!$entry) continue;

            // CIDR range check
            if (str_contains($entry, '/')) {
                if ($this->ipInCidr($clientIp, $entry)) {
                    abort(403, 'Access denied.');
                }
            } elseif ($entry === $clientIp) {
                abort(403, 'Access denied.');
            }
        }

        return $next($request);
    }

    private function ipInCidr(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = explode('/', $cidr, 2);
        $bits = (int) $bits;

        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        $ip     = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask   = ~((1 << (32 - $bits)) - 1);

        return ($ip & $mask) === ($subnet & $mask);
    }
}
