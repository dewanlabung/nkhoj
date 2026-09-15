<?php

namespace App\Core\Exceptions;

use Exception;

class OtpException extends Exception
{
    public static function invalidCode(): self
    {
        return new self('Invalid or expired OTP code', 422);
    }

    public static function sendFailed(string $email): self
    {
        return new self("Failed to send OTP to {$email}", 500);
    }

    public static function tooManyAttempts(): self
    {
        return new self('Too many verification attempts. Please try again later.', 429);
    }
}
