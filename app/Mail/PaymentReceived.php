<?php

namespace App\Mail;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Submission;
use App\Services\PdfDocumentService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
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

    /**
     * The receipt, plus the final invoice once the balance is cleared.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $quotation = $this->submission->quotation;
        $pdf = app(PdfDocumentService::class);
        $attachments = [];

        if ($quotation !== null && $this->payment->receipt_number !== null) {
            $attachments[] = Attachment::fromData(fn () => $pdf->receipt($this->payment, $quotation), $this->payment->receipt_number.'.pdf')->withMime('application/pdf');
        }

        if ($quotation?->invoice_number !== null && $this->submission->payment_status === PaymentStatus::PaidInFull) {
            $attachments[] = Attachment::fromData(fn () => $pdf->invoice($quotation), $quotation->invoice_number.'.pdf')->withMime('application/pdf');
        }

        return $attachments;
    }
}
