<?php

namespace App\Core\Services\Auth;

use App\Core\Contracts\OtpService;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class OtpAuthService implements OtpService
{
    public function generate(string $email, string $purpose = 'email_verification')
    {
        return Otp::generate($email, $purpose);
    }

    public function verify(string $email, string $code, string $purpose)
    {
        $otp = Otp::verify($email, $code, $purpose);

        if ($otp && $purpose === 'email_verification') {
            User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => explode('@', $email)[0],
                    'email_verified_at' => now(),
                ]
            );
        }

        return $otp;
    }

    public function resend(string $email, string $purpose = 'email_verification')
    {
        $otp = $this->generate($email, $purpose);
        $this->send($email, $otp);
        return $otp;
    }

    public function send(string $email, $otp): void
    {
        Mail::raw(
            "Your OTP verification code is: {$otp->code}\n\nThis code expires in 10 minutes.",
            function ($message) use ($email) {
                $message->to($email)->subject('Email Verification - OTP Code');
            }
        );
    }
}
