<?php

namespace App\Domains\Identity\Services;

use App\Mail\PasswordResetMail;
use App\Mail\PasswordResetOtpMail;
use App\Mail\UsernameReminderMail;
use App\Mail\VerifyRecoveryEmailMail;
use App\Models\LoggingAnalytics\OtpCode;
use App\Models\UserEngagement\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AccountRecoveryService
{
    public function findByLogin(string $login): ?User
    {
        $hasRecoveryCol = Schema::hasColumn('users', 'recovery_email');

        $query = User::where('email', $login)->orWhere('username', $login);
        if ($hasRecoveryCol) {
            $query->orWhere('recovery_email', $login);
        }

        return $query->first();
    }

    public function sendUsernameReminder(string $email): void
    {
        $query = User::where('email', $email);
        if (Schema::hasColumn('users', 'recovery_email')) {
            $query->orWhere('recovery_email', $email);
        }
        $user = $query->first();

        if (!$user) return;

        Mail::to($email)->send(new UsernameReminderMail($user));
    }

    public function sendPasswordResetLink(string $login): void
    {
        if (!Schema::hasColumn('users', 'recovery_token')) return;

        $hasRecoveryCol = Schema::hasColumn('users', 'recovery_email');

        $query = User::where('email', $login)->orWhere('username', $login);
        if ($hasRecoveryCol) {
            $query->orWhere('recovery_email', $login);
        }
        $user = $query->first();

        if (!$user) return;

        $token = Str::random(64);
        $user->update([
            'recovery_token'            => hash('sha256', $token),
            'recovery_token_expires_at' => now()->addHour(),
        ]);

        $sendTo = $user->email;
        if ($hasRecoveryCol
            && $user->recovery_email
            && $user->recovery_email_verified_at
            && strtolower($login) === strtolower($user->recovery_email)
        ) {
            $sendTo = $user->recovery_email;
        }

        $resetUrl = url('/reset-password?token=' . $token . '&email=' . urlencode($user->email));

        Mail::to($sendTo)->send(new PasswordResetMail($user, $resetUrl));
    }

    /**
     * Generate a 6-digit OTP and send it to the given email address for password reset.
     * Returns the destination address the OTP was sent to.
     */
    public function sendPasswordResetOtp(User $user, string $destination): void
    {
        OtpCode::where('user_id', $user->id)->where('type', 'password_reset')->delete();

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpCode::create([
            'user_id'    => $user->id,
            'code'       => $code,
            'type'       => 'password_reset',
            'expires_at' => now()->addMinutes(15),
        ]);

        Mail::to($destination)->send(new PasswordResetOtpMail($user, $code));
    }

    public function verifyPasswordResetOtp(int $userId, string $code): bool
    {
        $otp = OtpCode::where('user_id', $userId)
            ->where('type', 'password_reset')
            ->first();

        if (!$otp || $otp->isExpired() || $otp->code !== $code) {
            return false;
        }

        $otp->delete();
        return true;
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

    /**
     * Issue a one-time reset token for a user (used after OTP verification).
     */
    public function issueResetToken(User $user): string
    {
        $token = Str::random(64);
        $user->update([
            'recovery_token'            => hash('sha256', $token),
            'recovery_token_expires_at' => now()->addMinutes(30),
        ]);
        return $token;
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

        Mail::to($recoveryEmail)->send(new VerifyRecoveryEmailMail($user, $verifyUrl));
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
