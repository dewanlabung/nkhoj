<?php

namespace App\Core\Helpers;

class OtpGenerator
{
    public static function generate(int $length = 6): string
    {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= random_int(0, 9);
        }
        return $code;
    }

    public static function isValid(string $code, int $expectedLength = 6): bool
    {
        return strlen($code) === $expectedLength && ctype_digit($code);
    }
}
