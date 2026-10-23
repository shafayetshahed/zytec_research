<?php

namespace App\Mail;

use App\Models\Payment;
use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PaymentReceived extends Mailable
{
    use Queueable;

    public function __construct(public Submission $submission, public Payment $payment) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment received for '.$this->submission->reference.' — '.$this->payment->currency->format($this->payment->amount),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.payment-received',
            with: [
                'quotation' => $this->submission->quotation,
                'portalUrl' => route('student.submissions.show', $this->submission->reference),
            ],
        );
    }
}
