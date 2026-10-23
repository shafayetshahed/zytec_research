<?php

namespace App\Enums;

enum SubmissionStatus: string
{
    case New = 'new';
    case UnderReview = 'under_review';
    case AwaitingStudentResponse = 'awaiting_student_response';
    case QuotationSent = 'quotation_sent';
    case Accepted = 'accepted';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Closed = 'closed';
    case Declined = 'declined';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::UnderReview => 'Under review',
            self::AwaitingStudentResponse => 'Awaiting student response',
            self::QuotationSent => 'Quotation sent',
            self::Accepted => 'Accepted',
            self::InProgress => 'In progress',
            self::Completed => 'Completed',
            self::Closed => 'Closed',
            self::Declined => 'Declined',
        };
    }

    /**
     * Short explanation shown to students next to the status badge.
     */
    public function studentDescription(): string
    {
        return match ($this) {
            self::New => 'We have received your brief and it is waiting for review.',
            self::UnderReview => 'Our team is reviewing your requirements.',
            self::AwaitingStudentResponse => 'We need more information from you. Please check WhatsApp or email.',
            self::QuotationSent => 'We have sent you a quotation for the agreed scope.',
            self::Accepted => 'The support arrangement has been agreed.',
            self::InProgress => 'The agreed support is in progress.',
            self::Completed => 'The agreed support has been delivered.',
            self::Closed => 'This request is closed.',
            self::Declined => 'We are unable to take on this request.',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Completed, self::Closed, self::Declined], true);
    }

    /**
     * Statuses that count as "open" on the admin dashboard.
     *
     * @return list<self>
     */
    public static function open(): array
    {
        return array_values(array_filter(self::cases(), fn (self $status) => ! $status->isFinal()));
    }
}
