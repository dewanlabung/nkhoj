<?php

namespace App\Services\Email;

class SwiftMailerService implements EmailService
{
    public function send(string $to, string $subject, string $htmlContent, string $textContent = ''): bool
    {
        // TODO: Implement Swift Mailer integration
        // For now, fall back to SMTP
        $smtpService = new SMTPEmailService();

        return $smtpService->send($to, $subject, $htmlContent, $textContent);
    }

    public function getName(): string
    {
        return 'Swift Mailer';
    }
}
