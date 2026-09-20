<?php

namespace App\Mail;

use App\Models\UserEngagement\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifyRecoveryEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User   $user,
        public string $verifyUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verify your recovery email — ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.verify-recovery-email');
    }
}
