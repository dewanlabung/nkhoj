<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// AI auto-post generation — runs daily at 6:00 AM server time
Schedule::command('ai:generate-posts')->dailyAt('06:00');
