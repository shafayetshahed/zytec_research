<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Sent synchronously so the student immediately knows if delivery failed.
 */
class EmailVerificationCode extends Mailable
{
    use Queueable;

    public function __construct(public string $recipientName, public string $code, public int $expiresMinutes) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Zytecs Research verification code');
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.verification-code');
    }
}
