<?php

namespace App\Core\Services\Mail;

use Illuminate\Support\Facades\Mail;

class MailService
{
    public function send(string $email, string $subject, string $body): bool
    {
        try {
            Mail::raw($body, function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);
            });
            return true;
        } catch (\Exception $e) {
            \Log::error('Mail send failed: ' . $e->getMessage());
            return false;
        }
    }

    public function sendToMultiple(array $emails, string $subject, string $body): int
    {
        $sent = 0;
        foreach ($emails as $email) {
            if ($this->send($email, $subject, $body)) {
                $sent++;
            }
        }
        return $sent;
    }

    public function sendMailable($email, $mailable): bool
    {
        try {
            Mail::to($email)->send($mailable);
            return true;
        } catch (\Exception $e) {
            \Log::error('Mailable send failed: ' . $e->getMessage());
            return false;
        }
    }
}
