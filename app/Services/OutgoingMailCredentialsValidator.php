<?php

namespace App\Services;

use App\Mail\MailTestMailable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OutgoingMailCredentialsValidator
{
    /**
     * Temporarily apply SMTP credentials from the given settings array,
     * send a test email to $recipient, and return an error string on failure
     * or null on success.
     */
    public function validate(array $settings, string $recipient): ?string
    {
        $this->applyConfig($settings);

        try {
            Mail::to($recipient)->send(new MailTestMailable());
            return null;
        } catch (Throwable $e) {
            return $this->friendlyError($e);
        }
    }

    private function applyConfig(array $s): void
    {
        $mailer = match($s['service'] ?? 'smtp') {
            'gmail-api' => 'gmail-api',
            'mailgun'   => 'mailgun',
            'ses'       => 'ses',
            'sendmail'  => 'sendmail',
            default     => 'smtp',
        };

        $encryption = ($s['encryption'] ?? 'tls') === 'none' ? null : ($s['encryption'] ?? 'tls');

        $fromAddress = $s['from_address'] ?? '';
        if (empty($fromAddress) || $fromAddress === 'null') {
            $fromAddress = config('mail.from.address', '');
        }
        // Construct a plausible from address from the SMTP host as last resort
        if (empty($fromAddress) || $fromAddress === 'null') {
            $host = preg_replace('/^(smtp\.|mail\.)/', '', ($s['host'] ?? 'example.com'));
            $fromAddress = 'noreply@' . ($host ?: 'example.com');
        }

        Config::set([
            'mail.default'                 => $mailer,
            'mail.mailers.smtp.host'       => $s['host'] ?? '',
            'mail.mailers.smtp.port'       => $s['port'] ?? 587,
            'mail.mailers.smtp.encryption' => $encryption,
            'mail.mailers.smtp.username'   => $s['username'] ?? '',
            'mail.mailers.smtp.password'   => $s['password'] ?? '',
            'mail.from.address'            => $fromAddress,
            'mail.from.name'               => $s['title'] ?? config('app.name'),
        ]);

        // Reset the mailer singleton so it picks up the new config
        app()->forgetInstance('mailer');
        app()->forgetInstance('swift.mailer');
        app()->forgetInstance('swift.transport');
    }

    private function friendlyError(Throwable $e): string
    {
        $msg = $e->getMessage();

        if (str_contains($msg, 'Connection refused') || str_contains($msg, 'connection timed out')) {
            return 'Could not connect to the mail server. Check the host and port.';
        }
        if (str_contains($msg, 'Authentication')) {
            return 'Authentication failed. Check your username and password.';
        }
        if (str_contains($msg, 'SSL') || str_contains($msg, 'TLS')) {
            return 'Encryption error. Try changing the encryption setting.';
        }

        return 'Mail test failed: ' . $msg;
    }
}
