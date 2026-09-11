<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// AI auto-post generation — runs daily at 6:00 AM server time
Schedule::command('ai:generate-posts')->dailyAt('06:00');

// Auto-publish posts whose scheduled_at has passed
Schedule::command('posts:publish-scheduled')->everyFiveMinutes();

// Purge accounts that requested deletion 30+ days ago
Schedule::job(new \App\Jobs\PurgeDeletedAccounts)->daily();

// Purge expired disappearing DMs
Schedule::command('messages:purge-expired')->hourly();
