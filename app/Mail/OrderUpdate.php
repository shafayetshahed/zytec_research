<?php

namespace App\Mail;

use App\Models\NotificationLog;
use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Short alerts about assignments, materials and new messages. They never
 * contain message text, files, or the other party's contact details: the
 * recipient signs in to read them.
 */
class OrderUpdate extends Mailable
{
    use Queueable;

    public function __construct(public Submission $submission, public string $kind) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->headline().' — '.$this->submission->reference);
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.order-update', with: [
            'headline' => $this->headline(),
            'body' => $this->body(),
            'url' => $this->url(),
            'button' => $this->kind === NotificationLog::TYPE_MATERIALS_READY ? 'Download your materials' : 'Open the order',
        ]);
    }

    public function headline(): string
    {
        return match ($this->kind) {
            NotificationLog::TYPE_EXPERT_ASSIGNED => 'New order assigned to you',
            NotificationLog::TYPE_MATERIALS_UPLOADED => 'Materials awaiting your review',
            NotificationLog::TYPE_MATERIALS_READY => 'Your feedback and materials are ready',
            default => 'New message',
        };
    }

    private function body(): string
    {
        $work = '"'.str($this->submission->assignment_name)->limit(80).'"';

        return match ($this->kind) {
            NotificationLog::TYPE_EXPERT_ASSIGNED => "Order {$this->submission->reference} ({$work}) has been assigned to you. Sign in to the Team portal to see the brief and instructions.",
            NotificationLog::TYPE_MATERIALS_UPLOADED => "A team member uploaded materials for {$this->submission->reference} ({$work}). Review and approve them before they are released to the student.",
            NotificationLog::TYPE_MATERIALS_READY => "Guidance and feedback materials for {$work} are now available in your portal. Use them to review and improve your own work.",
            default => "There is a new message about order {$this->submission->reference} ({$work}). Sign in to read and reply.",
        };
    }

    private function url(): string
    {
        $base = rtrim((string) config('app.url'), '/');

        return $base.match ($this->kind) {
            NotificationLog::TYPE_EXPERT_ASSIGNED, NotificationLog::TYPE_MESSAGE_TO_TEAM => route('team.orders.show', $this->submission->reference, false),
            NotificationLog::TYPE_MATERIALS_UPLOADED, NotificationLog::TYPE_MESSAGE_TO_ADMIN => route('admin.submissions.show', $this->submission, false),
            default => route('student.submissions.show', $this->submission->reference, false),
        };
    }
}
