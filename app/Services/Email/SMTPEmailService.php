<?php

namespace App\Services\Email;

use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class SMTPEmailService implements EmailService
{
    public function send(string $to, string $subject, string $htmlContent, string $textContent = ''): bool
    {
        try {
            Mail::send([], [], function (Message $message) use ($to, $subject, $htmlContent, $textContent) {
                $message->to($to)
                    ->subject($subject)
                    ->setBody($htmlContent, 'text/html');

                if ($textContent) {
                    $message->addPart($textContent, 'text/plain');
                }
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('SMTP Email Send Failed', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function getName(): string
    {
        return 'SMTP';
    }
}
