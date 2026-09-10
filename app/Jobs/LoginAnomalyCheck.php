<?php

namespace App\Jobs;

use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class LoginAnomalyCheck implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(
        public readonly int $userId,
        public readonly string $ip,
        public readonly string $userAgent,
    ) {}

    public function handle(): void
    {
        $user = User::find($this->userId);
        if (!$user || !$user->email) return;

        // Compare against last 10 known IPs
        $knownIps = LoginHistory::where('user_id', $this->userId)
            ->where('successful', true)
            ->where('created_at', '<', now()->subMinutes(5)) // exclude just-recorded login
            ->orderByDesc('created_at')
            ->limit(10)
            ->pluck('ip_address')
            ->filter()
            ->unique()
            ->values();

        // If we have history and current IP is completely new, send alert
        if ($knownIps->count() >= 2 && !$knownIps->contains($this->ip)) {
            Mail::raw(
                "Hi {$user->name},\n\n" .
                "We noticed a sign-in to your Nkhoj account from a new location.\n\n" .
                "IP address: {$this->ip}\n" .
                "Device: {$this->userAgent}\n" .
                "Time: " . now()->format('Y-m-d H:i:s') . " UTC\n\n" .
                "If this was you, no action is needed.\n\n" .
                "If this wasn't you, please change your password immediately:\n" .
                url('/account/security') . "\n\n" .
                "— Nkhoj Security",
                function ($m) use ($user) {
                    $m->to($user->email)
                      ->subject('New sign-in to your Nkhoj account');
                }
            );
        }
    }
}
