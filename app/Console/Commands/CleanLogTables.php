<?php

namespace App\Console\Commands;

use App\Models\OutgoingEmailLog;
use App\Models\ScheduleLog;
use Illuminate\Console\Command;

class CleanLogTables extends Command
{
    protected $signature = 'logs:clean';
    protected $description = 'Delete old schedule and email log entries.';

    public function handle(): int
    {
        ScheduleLog::where('ran_at', '<', now()->subDays(30))->delete();
        OutgoingEmailLog::where('created_at', '<', now()->subDays(7))->delete();

        $this->info('Old log entries deleted.');
        return self::SUCCESS;
    }
}
