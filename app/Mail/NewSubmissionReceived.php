<?php

namespace App\Mail;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewSubmissionReceived extends Mailable
{
    use Queueable;

    public function __construct(public Submission $submission) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New brief '.$this->submission->reference.' — '.$this->submission->module_code.': '.str($this->submission->assignment_name)->limit(60),
            replyTo: [new Address($this->submission->contact_email, $this->submission->student->name)],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.new-submission',
            with: [
                'adminUrl' => route('admin.submissions.show', $this->submission),
                'brief' => $this->submission->brief,
                'attached' => $this->shouldAttachBrief(),
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (! $this->shouldAttachBrief() || $this->submission->brief === null) {
            return [];
        }

        $brief = $this->submission->brief;

        return [
            Attachment::fromStorageDisk(config('zytecs.uploads.disk'), $brief->storage_path)
                ->as($brief->original_name)
                ->withMime($brief->mime_type),
        ];
    }

    public function shouldAttachBrief(): bool
    {
        $limitKb = (int) config('zytecs.notifications.attach_max_kb');
        $brief = $this->submission->brief;

        return $limitKb > 0 && $brief !== null && $brief->size <= $limitKb * 1024;
    }
}
