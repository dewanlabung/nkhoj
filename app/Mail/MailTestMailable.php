<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailTestMailable extends Mailable
{
    use SerializesModels;

    public function build(): static
    {
        $fromAddress = config('mail.from.address', '');
        $fromName    = config('mail.from.name', config('app.name'));

        $mail = $this
            ->subject(config('app.name') . ' — Mail Configuration Test')
            ->html(
                '<p>This is a test email from <strong>' . e(config('app.name')) . '</strong>.</p>' .
                '<p>If you received this, your outgoing mail settings are configured correctly.</p>'
            );

        if (!empty($fromAddress) && $fromAddress !== 'null') {
            $mail->from($fromAddress, $fromName);
        }

        return $mail;
    }
}
