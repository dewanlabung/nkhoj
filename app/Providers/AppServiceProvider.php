<?php

namespace App\Providers;

use App\Listeners\OutgoingEmailLogSubscriber;
use App\Services\Mail\GmailApiMailTransport;
use App\Services\Mail\GmailClient;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Event::subscribe(OutgoingEmailLogSubscriber::class);

        $path = storage_path('app/site_settings.json');
        if (!File::exists($path)) return;

        $s = json_decode(File::get($path), true) ?? [];

        // Apply queue driver from admin settings
        if (!empty($s['queue']['connection'])) {
            config(['queue.default' => $s['queue']['connection']]);
        }

        // Register Gmail API custom mailer
        Mail::extend('gmail-api', fn() => new GmailApiMailTransport());

        // Apply mail settings so they survive config:cache rebuilds
        $service = $s['email']['service'] ?? 'smtp';

        if ($service === 'gmail-api' && GmailClient::tokenExists()) {
            config([
                'mail.default'         => 'gmail-api',
                'mail.mailers.gmail-api' => ['transport' => 'gmail-api'],
                'mail.from.address'    => GmailClient::connectedEmail() ?? config('mail.from.address'),
                'mail.from.name'       => $s['email']['title'] ?? config('app.name'),
            ]);
        } elseif (!empty($s['email']['host'])) {
            config([
                'mail.mailers.smtp.host'       => $s['email']['host'],
                'mail.mailers.smtp.port'       => $s['email']['port'] ?? 587,
                'mail.mailers.smtp.encryption' => ($s['email']['encryption'] ?? 'tls') === 'none' ? null : ($s['email']['encryption'] ?? 'tls'),
                'mail.mailers.smtp.username'   => $s['email']['username'] ?? '',
                'mail.mailers.smtp.password'   => $s['email']['password'] ?? '',
                'mail.from.address'            => $s['email']['from_address'] ?? config('mail.from.address'),
                'mail.from.name'               => $s['email']['title'] ?? config('app.name'),
            ]);
        }
    }
}
