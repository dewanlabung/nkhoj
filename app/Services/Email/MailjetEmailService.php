<?php

namespace App\Services\Email;

class MailjetEmailService implements EmailService
{
    public function send(string $to, string $subject, string $htmlContent, string $textContent = ''): bool
    {
        // TODO: Implement Mailjet API integration
        // For now, fall back to SMTP
        $smtpService = new SMTPEmailService();

        return $smtpService->send($to, $subject, $htmlContent, $textContent);
    }

    public function getName(): string
    {
        return 'Mailjet';
    }
}
