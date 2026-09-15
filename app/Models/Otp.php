<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $fillable = ['user_id', 'email', 'code', 'purpose', 'expires_at', 'attempts', 'verified_at'];
    protected $casts = ['expires_at' => 'datetime', 'verified_at' => 'datetime'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool {
        return now()->isAfter($this->expires_at);
    }

    public function isVerified(): bool {
        return $this->verified_at !== null;
    }

    public function isValid(): bool {
        return !$this->isExpired() && !$this->isVerified() && $this->attempts < 5;
    }

    public function verify(): bool {
        if (!$this->isValid()) {
            return false;
        }
        $this->update(['verified_at' => now()]);
        return true;
    }

    public function incrementAttempts(): void {
        $this->increment('attempts');
    }

    public static function generate(string $email, string $purpose = 'email_verification', ?int $userId = null): self {
        self::where('email', $email)->where('purpose', $purpose)->delete();
        return self::create([
            'email' => $email,
            'code' => str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'purpose' => $purpose,
            'user_id' => $userId,
            'expires_at' => now()->addMinutes(10),
        ]);
    }

    public static function verify(string $email, string $code, string $purpose = 'email_verification'): ?self {
        $otp = self::where('email', $email)
            ->where('code', $code)
            ->where('purpose', $purpose)
            ->first();

        if ($otp && $otp->isValid()) {
            $otp->verify();
            return $otp;
        }

        if ($otp) {
            $otp->incrementAttempts();
        }

        return null;
    }
}
