<?php

namespace Tests\Unit\Core\Helpers;

use App\Core\Helpers\OtpGenerator;
use Tests\TestCase;

class OtpGeneratorTest extends TestCase
{
    public function test_can_generate_otp_code(): void
    {
        $code = OtpGenerator::generate();

        $this->assertEqual(strlen($code), 6);
        $this->assertTrue(ctype_digit($code));
    }

    public function test_can_generate_custom_length_otp(): void
    {
        $code = OtpGenerator::generate(8);

        $this->assertEqual(strlen($code), 8);
        $this->assertTrue(ctype_digit($code));
    }

    public function test_generated_codes_are_random(): void
    {
        $code1 = OtpGenerator::generate();
        $code2 = OtpGenerator::generate();

        $this->assertNotEqual($code1, $code2);
    }

    public function test_valid_code_format(): void
    {
        $code = OtpGenerator::generate(6);

        $this->assertTrue(OtpGenerator::isValid($code, 6));
    }

    public function test_invalid_code_format(): void
    {
        $this->assertFalse(OtpGenerator::isValid('abc123', 6));
        $this->assertFalse(OtpGenerator::isValid('12345', 6));
        $this->assertFalse(OtpGenerator::isValid('123456', 4));
    }
}
