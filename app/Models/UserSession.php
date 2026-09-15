<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'session_id', 'ip', 'user_agent',
        'device_type', 'country', 'city', 'last_active_at',
    ];

    protected $casts = [
        'last_active_at' => 'datetime',
        'created_at'     => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function invalidateOtherSessions(int $userId, string $currentSessionId): void
    {
        $others = static::where('user_id', $userId)
            ->where('session_id', '!=', $currentSessionId)
            ->pluck('session_id');

        foreach ($others as $sid) {
            \Illuminate\Support\Facades\DB::table(config('session.table', 'sessions'))
                ->where('id', $sid)
                ->delete();
        }

        static::where('user_id', $userId)
            ->where('session_id', '!=', $currentSessionId)
            ->delete();
    }

    public static function upsertForRequest(\Illuminate\Http\Request $request, int $userId): void
    {
        $sid = session()->getId();
        if (!$sid) return;

        static::updateOrCreate(
            ['session_id' => $sid],
            [
                'user_id'        => $userId,
                'ip'             => $request->ip(),
                'user_agent'     => substr($request->userAgent() ?? '', 0, 500),
                'device_type'    => self::detectDevice($request->userAgent() ?? ''),
                'last_active_at' => now(),
            ]
        );
    }

    private static function detectDevice(string $ua): string
    {
        $ua = strtolower($ua);
        if (str_contains($ua, 'mobile') || str_contains($ua, 'android') || str_contains($ua, 'iphone')) {
            return 'mobile';
        }
        if (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) {
            return 'tablet';
        }
        return 'desktop';
    }
}
