<?php

namespace App\Console\Commands;

use App\Core\Actions\SubscribeUserToNotifications;
use App\Models\User;
use Illuminate\Console\Command;

class SubscribeUsersToNotifications extends Command
{
    protected $signature = 'notifications:subscribe-users {--force : Skip confirmation}';
    protected $description = 'Initialize notification subscriptions for all existing users with default channels';

    public function handle(): int
    {
        $userCount = User::count();

        if ($userCount === 0) {
            $this->info('No users found. Nothing to do.');
            return 0;
        }

        if (!$this->option('force')) {
            $this->warn("This will initialize notification subscriptions for $userCount users.");
            if (!$this->confirm('Continue?')) {
                $this->info('Cancelled.');
                return 1;
            }
        }

        $this->info("Initializing notification subscriptions for $userCount users...");

        $bar = $this->output->createProgressBar($userCount);
        $bar->start();

        User::query()->each(function (User $user) use ($bar) {
            try {
                SubscribeUserToNotifications::execute($user);
                $bar->advance();
            } catch (\Exception $e) {
                $bar->advance();
                $this->error("\nFailed to subscribe user {$user->id}: {$e->getMessage()}");
            }
        });

        $bar->finish();
        $this->newLine();

        $this->info('✓ Notification subscriptions initialized successfully!');
        return 0;
    }
}
