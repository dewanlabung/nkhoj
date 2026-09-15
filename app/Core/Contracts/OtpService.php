<?php

namespace App\Core\Contracts;

interface OtpService
{
    public function generate(string $email, string $purpose = 'email_verification');
    public function verify(string $email, string $code, string $purpose);
    public function resend(string $email, string $purpose = 'email_verification');
    public function send(string $email, $otp): void;
}
