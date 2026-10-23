<?php

namespace App\Mail;

use App\Models\Submission;
use App\Services\PdfDocumentService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class QuotationIssued extends Mailable
{
    use Queueable;

    public function __construct(public Submission $submission) {}

    public function envelope(): Envelope
    {
        $quotation = $this->submission->quotation;

        return new Envelope(
            subject: 'Your quotation '.$this->submission->reference.' — '.str($this->submission->assignment_name)->limit(50).' — '.$quotation?->currency->format($quotation->total_amount),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.quotation',
            with: [
                'quotation' => $this->submission->quotation,
                'portalUrl' => route('student.submissions.show', $this->submission->reference),
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $quotation = $this->submission->quotation;

        if ($quotation?->number === null) {
            return [];
        }

        return [
            Attachment::fromData(fn () => app(PdfDocumentService::class)->quotation($quotation), $quotation->number.'.pdf')->withMime('application/pdf'),
        ];
    }
}
