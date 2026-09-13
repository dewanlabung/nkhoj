<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailTestMailable extends Mailable
{
    use SerializesModels;

    public function build(): static
    {
        return $this
            ->subject(config('app.name') . ' — Mail Configuration Test')
            ->html(
                '<p>This is a test email from <strong>' . e(config('app.name')) . '</strong>.</p>' .
                '<p>If you received this, your outgoing mail settings are configured correctly.</p>'
            );
    }
}
