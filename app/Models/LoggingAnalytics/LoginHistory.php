<?php

namespace App\Models\LoggingAnalytics;

use App\Services\GeoIpService;
use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'provider', 'ip_address', 'user_agent',
        'device_type', 'browser', 'platform', 'country', 'city', 'success',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'success'    => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function record(int $userId, string $provider = 'email', bool $success = true): void
    {
        $ua = request()->userAgent() ?? '';

        $deviceType = 'desktop';
        if (preg_match('/Mobile|Android|iPhone|iPad/i', $ua)) {
            $deviceType = preg_match('/iPad/i', $ua) ? 'tablet' : 'mobile';
        }

        $browser = 'Unknown';
        foreach ([
            'Edg'     => 'Edge',
            'Chrome'  => 'Chrome',
            'Firefox' => 'Firefox',
            'Safari'  => 'Safari',
            'Opera'   => 'Opera',
        ] as $key => $name) {
            if (str_contains($ua, $key)) { $browser = $name; break; }
        }

        $platform = 'Unknown';
        foreach ([
            'Windows' => 'Windows',
            'Android' => 'Android',
            'iPhone'  => 'iOS',
            'iPad'    => 'iPadOS',
            'Mac'     => 'macOS',
            'Linux'   => 'Linux',
        ] as $key => $name) {
            if (str_contains($ua, $key)) { $platform = $name; break; }
        }

        $ip  = request()->ip();
        $geo = GeoIpService::lookup($ip);

        static::create([
            'user_id'     => $userId,
            'provider'    => $provider,
            'ip_address'  => $ip,
            'user_agent'  => substr($ua, 0, 500),
            'device_type' => $deviceType,
            'browser'     => $browser,
            'platform'    => $platform,
            'city'        => $geo['city'],
            'country'     => $geo['country'],
            'success'     => $success,
            'created_at'  => now(),
        ]);
    }

    public function parsedBrowser(): string
    {
        $ua = $this->user_agent ?? '';
        foreach (['Edg' => 'Edge', 'Chrome' => 'Chrome', 'Firefox' => 'Firefox', 'Safari' => 'Safari', 'Opera' => 'Opera'] as $key => $name) {
            if (str_contains($ua, $key)) return $name;
        }
        return 'Browser';
    }

    public function parsedPlatform(): string
    {
        $ua = $this->user_agent ?? '';
        foreach (['Windows' => 'Windows', 'Android' => 'Android', 'iPhone' => 'iOS', 'iPad' => 'iPadOS', 'Mac' => 'macOS', 'Linux' => 'Linux'] as $key => $name) {
            if (str_contains($ua, $key)) return $name;
        }
        return 'Unknown';
    }

    public function deviceIcon(): string
    {
        return match($this->device_type) {
            'mobile'  => '📱',
            'tablet'  => '📟',
            default   => '💻',
        };
    }

    public function providerIcon(): string
    {
        return match($this->provider) {
            'google'   => '🔵',
            'facebook' => '🔷',
            default    => '🔑',
        };
    }
}
