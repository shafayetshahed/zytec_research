<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Invites a team member to set a password for the Team portal. Sent
 * synchronously so the administrator sees any delivery failure at once.
 */
class TeamInvitation extends Mailable
{
    use Queueable;

    public function __construct(public string $recipientName, public string $setPasswordUrl, public int $expiresMinutes) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'You have been added to the Zytecs Research team portal');
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.team-invitation');
    }
}
