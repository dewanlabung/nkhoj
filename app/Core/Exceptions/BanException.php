<?php

namespace App\Core\Exceptions;

use Exception;

class BanException extends Exception
{
    public static function userBanned(): self
    {
        return new self('User is banned and cannot perform this action', 403);
    }

    public static function banFailed(string $reason): self
    {
        return new self("Failed to ban user: {$reason}", 500);
    }

    public static function unbanFailed(string $reason): self
    {
        return new self("Failed to unban user: {$reason}", 500);
    }
}
