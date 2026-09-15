<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LoggingAnalytics\ScheduleLog;

class ScheduleHealthCommand extends Command
{
    protected $signature = 'schedule:health';
    protected $description = 'Canary job proving cron scheduler is alive. Run every minute.';

    public function handle()
    {
        try {
            ScheduleLog::create([
                'command' => 'schedule:health',
                'status' => 'success',
                'duration_ms' => 1,
                'output' => 'Canary health check passed',
            ]);

            $this->info('✅ Scheduler health check passed');
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Scheduler health check failed: ' . $e->getMessage());
            return 1;
        }
    }
}
