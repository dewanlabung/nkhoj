<?php

namespace App\Services\Email;

interface EmailService
{
    public function send(string $to, string $subject, string $htmlContent, string $textContent = ''): bool;
    public function getName(): string;
}

class EmailServiceFactory
{
    public static function create(): EmailService
    {
        $mailer = config('mail.default', 'smtp');

        return match ($mailer) {
            'mailjet' => new MailjetEmailService(),
            'swift' => new SwiftMailerService(),
            default => new SMTPEmailService(),
        };
    }
}
