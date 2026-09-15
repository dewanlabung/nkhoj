<?php

namespace App\Validators;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMailMailable;

class MailCredentialsValidator
{
    const KEYS = [
        'mail_mailer',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
    ];

    public function validate(array $values): ?string
    {
        $this->setConfigDynamically($values);

        try {
            Mail::to(Auth::user()->email)->send(new TestMailMailable());
            return null; // Success
        } catch (Exception $e) {
            return $this->formatErrorMessage($e);
        }
    }

    private function setConfigDynamically(array $values): void
    {
        foreach ($values as $key => $value) {
            if (empty($value)) {
                continue;
            }

            // Convert mail_host to mail.host
            $configKey = str_replace('_', '.', $key);

            // Handle mail.default vs mail.mailer
            if ($configKey === 'mail.mailer') {
                $configKey = 'mail.default';
            }

            config([$configKey => $value]);
        }
    }

    private function formatErrorMessage(Exception $e): string
    {
        $message = $e->getMessage();

        if (str_contains($message, 'SMTP')) {
            return 'SMTP connection failed. Check host, port, and credentials.';
        }

        if (str_contains($message, 'authentication') || str_contains($message, 'auth')) {
            return 'Authentication failed. Check username and password.';
        }

        if (str_contains($message, 'Connection refused') || str_contains($message, 'connection')) {
            return 'Connection failed. Check if SMTP server is reachable at the specified host and port.';
        }

        return 'Mail configuration error: ' . $message;
    }
}
