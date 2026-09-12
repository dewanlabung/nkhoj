<?php

use App\Models\ScheduleLog;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Symfony\Component\Stopwatch\Stopwatch;

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

// Purge expired 24-hour stories
Schedule::command('stories:purge-expired')->hourly();

// Clean old log table entries
Schedule::command('logs:clean')->daily();

// ── Schedule monitoring ─────────────────────────────────────────────────────
// Attach before/after hooks to every scheduled event to record run history.
app()->booted(function () {
    $schedule = app(\Illuminate\Console\Scheduling\Schedule::class);

    foreach ($schedule->events() as $event) {
        $stopwatch  = new Stopwatch(true);
        $ranAt      = null;
        $watchKey   = $event->command ?? ($event->description ?? 'closure');

        $event->before(function () use ($watchKey, $stopwatch, &$ranAt) {
            $ranAt = now();
            try { $stopwatch->start($watchKey); } catch (\Throwable) {}
        });

        $event->after(function (\Illuminate\Support\Stringable $output) use (
            $event, $watchKey, $stopwatch, &$ranAt
        ) {
            $duration = 0;
            try {
                $stopwatch->stop($watchKey);
                $duration = (int) abs($stopwatch->getEvent($watchKey)->getDuration());
            } catch (\Throwable) {}

            // Extract the artisan command signature
            $signature = $watchKey;
            if ($event->command) {
                $parts       = collect(explode(' ', $event->command));
                $artisanIdx  = $parts->search(fn($s) => trim($s, '\'"') === 'artisan');
                if ($artisanIdx !== false) {
                    $signature = $parts->slice($artisanIdx + 1)->implode(' ');
                }
            }

            $data = [
                'command'   => $signature,
                'output'    => trim(mb_substr($output->toString(), 0, 1000), "\n"),
                'exit_code' => $event->exitCode ?? 0,
                'duration'  => $duration,
            ];

            $existing = ScheduleLog::query()
                ->where('command', $signature)
                ->where('exit_code', $event->exitCode ?? 0)
                ->where('ran_at', '>=', now()->subHour())
                ->first();

            if ($existing) {
                $existing->fill([...$data, 'ran_at' => $ranAt, 'count_in_last_hour' => $existing->count_in_last_hour + 1])->save();
            } else {
                ScheduleLog::create([...$data, 'ran_at' => $ranAt, 'count_in_last_hour' => 1]);
            }
        });
    }
});
