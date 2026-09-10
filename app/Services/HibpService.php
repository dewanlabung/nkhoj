<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class HibpService
{
    public function isBreached(string $password): bool
    {
        try {
            $hash   = strtoupper(sha1($password));
            $prefix = substr($hash, 0, 5);
            $suffix = substr($hash, 5);

            $response = Http::withHeaders(['Add-Padding' => 'true'])
                ->timeout(3)
                ->get("https://api.pwnedpasswords.com/range/{$prefix}");

            if (!$response->successful()) {
                return false; // fail open — don't block on API error
            }

            foreach (explode("\n", $response->body()) as $line) {
                [$s, $count] = explode(':', trim($line)) + [null, 0];
                if ($s === $suffix && (int)$count > 0) {
                    return true;
                }
            }
        } catch (\Throwable) {
            return false; // fail open
        }

        return false;
    }
}
