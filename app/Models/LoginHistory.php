<?php

namespace App\Models;

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
            'Mac'     => 'macOS',
            'Linux'   => 'Linux',
            'Android' => 'Android',
            'iOS'     => 'iOS',
            'iPhone'  => 'iOS',
        ] as $key => $name) {
            if (str_contains($ua, $key)) { $platform = $name; break; }
        }

        static::create([
            'user_id'     => $userId,
            'provider'    => $provider,
            'ip_address'  => request()->ip(),
            'user_agent'  => substr($ua, 0, 500),
            'device_type' => $deviceType,
            'browser'     => $browser,
            'platform'    => $platform,
            'success'     => $success,
            'created_at'  => now(),
        ]);
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
