<?php

namespace App\Models\LoggingAnalytics;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OtpCode extends Model
{
    protected $fillable = ['user_id', 'code', 'type', 'expires_at'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public static function createForEmailVerification(User|int $user): self
    {
        $userId = $user instanceof User ? $user->id : $user;
        // Invalidate any existing OTP for this user + type before creating a new one
        self::where('user_id', $userId)->where('type', 'email_verification')->delete();

        return self::create([
            'user_id'    => $userId,
            'code'       => str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'type'       => 'email_verification',
            'expires_at' => now()->addMinutes(30),
        ]);
    }
}
