<?php

namespace App\Console\Commands;

use App\Models\OtpCode;
use Illuminate\Console\Command;

class DeleteExpiredOtpCodesCommand extends Command
{
    protected $signature   = 'otp:delete-expired';
    protected $description = 'Purge OTP codes that have passed their expiry time';

    public function handle(): int
    {
        $deleted = OtpCode::where('expires_at', '<', now())->delete();
        $this->info("Deleted {$deleted} expired OTP code(s).");
        return self::SUCCESS;
    }
}
