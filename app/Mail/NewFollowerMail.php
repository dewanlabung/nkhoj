<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewFollowerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $recipient,
        public User $follower,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->follower->name . ' ले तपाईंलाई फलो गर्नुभयो — Nkhoj',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-follower',
        );
    }
}
