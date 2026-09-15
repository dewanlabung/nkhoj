<?php

namespace Tests\Unit\Core\Services;

use App\Core\Contracts\OtpService;
use App\Core\Services\Auth\OtpAuthService;
use App\Models\Otp;
use App\Models\User;
use Tests\TestCase;

class OtpAuthServiceTest extends TestCase
{
    private OtpService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(OtpService::class);
    }

    public function test_can_generate_otp(): void
    {
        $otp = $this->service->generate('test@example.com', 'email_verification');

        $this->assertNotNull($otp);
        $this->assertNotNull($otp->code);
        $this->assertEqual($otp->email, 'test@example.com');
        $this->assertEqual($otp->purpose, 'email_verification');
    }

    public function test_can_verify_valid_otp(): void
    {
        $email = 'test@example.com';
        $otp = $this->service->generate($email, 'email_verification');

        $result = $this->service->verify($email, $otp->code, 'email_verification');

        $this->assertNotNull($result);
        $this->assertEqual($result->email, $email);
    }

    public function test_cannot_verify_invalid_otp(): void
    {
        $otp = $this->service->verify('test@example.com', 'invalid', 'email_verification');

        $this->assertNull($otp);
    }

    public function test_creates_user_on_email_verification(): void
    {
        $email = 'newuser@example.com';
        $otp = $this->service->generate($email, 'email_verification');

        $this->service->verify($email, $otp->code, 'email_verification');

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->email_verified_at);
    }
}
