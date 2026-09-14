<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TestMailMailable extends Mailable
{
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: config('app.name') . ' — SMTP Test Email',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.test-mail',
        );
    }
}
