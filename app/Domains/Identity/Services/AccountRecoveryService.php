<?php

namespace App\Domains\Identity\Services;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AccountRecoveryService
{
    public function sendUsernameReminder(string $email): void
    {
        $query = User::where('email', $email);
        if (Schema::hasColumn('users', 'recovery_email')) {
            $query->orWhere('recovery_email', $email);
        }
        $user = $query->first();

        if (!$user) return;

        Mail::raw(
            "Hi {$user->name},\n\nYour username on " . config('app.name') . " is: {$user->username}\n\nIf you didn't request this, ignore this email.\n\n— " . config('app.name'),
            fn($m) => $m->to($email)->subject('Your ' . config('app.name') . ' username')
        );
    }

    public function sendPasswordResetLink(string $login): void
    {
        if (!Schema::hasColumn('users', 'recovery_token')) return;

        $query = User::where('email', $login)->orWhere('username', $login);
        if (Schema::hasColumn('users', 'recovery_email')) {
            $query->orWhere('recovery_email', $login);
        }
        $user = $query->first();

        if (!$user) return;

        $token = Str::random(64);
        $user->update([
            'recovery_token'            => hash('sha256', $token),
            'recovery_token_expires_at' => now()->addHour(),
        ]);

        $resetUrl = url('/reset-password?token=' . $token . '&email=' . urlencode($user->email));

        Mail::raw(
            "Hi {$user->name},\n\nClick the link below to reset your password (expires in 1 hour):\n\n{$resetUrl}\n\nIf you didn't request this, ignore this email.\n\n— " . config('app.name'),
            fn($m) => $m->to($user->email)->subject('Reset your ' . config('app.name') . ' password')
        );
    }

    public function resetPassword(string $email, string $token, string $password): bool
    {
        if (!Schema::hasColumn('users', 'recovery_token')) return false;

        $user = User::where('email', $email)
            ->whereNotNull('recovery_token')
            ->where('recovery_token_expires_at', '>', now())
            ->first();

        if (!$user || !hash_equals($user->recovery_token, hash('sha256', $token))) {
            return false;
        }

        $user->update([
            'password'                  => bcrypt($password),
            'recovery_token'            => null,
            'recovery_token_expires_at' => null,
        ]);

        return true;
    }

    public function saveRecoveryEmail(User $user, string $recoveryEmail): void
    {
        $token = Str::random(64);

        $user->update([
            'recovery_email'             => $recoveryEmail,
            'recovery_email_verified_at' => null,
            'recovery_token'             => hash('sha256', $token),
            'recovery_token_expires_at'  => now()->addHours(24),
        ]);

        $verifyUrl = url('/account/recovery/verify-email?token=' . $token);

        Mail::raw(
            "Hi {$user->name},\n\nVerify this email as your recovery address for " . config('app.name') . ":\n\n{$verifyUrl}\n\nThis link expires in 24 hours.\n\n— " . config('app.name'),
            fn($m) => $m->to($recoveryEmail)->subject('Verify your recovery email — ' . config('app.name'))
        );
    }

    public function verifyRecoveryEmail(User $user, string $token): bool
    {
        if (!$user->recovery_token || !$user->recovery_token_expires_at?->isFuture()) {
            return false;
        }

        if (!hash_equals($user->recovery_token, hash('sha256', $token))) {
            return false;
        }

        $user->update([
            'recovery_email_verified_at' => now(),
            'recovery_token'             => null,
            'recovery_token_expires_at'  => null,
        ]);

        return true;
    }
}
