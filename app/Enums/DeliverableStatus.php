<?php

namespace App\Enums;

enum DeliverableStatus: string
{
    case PendingReview = 'pending_review';
    case Approved = 'approved';
    case Returned = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::PendingReview => 'Awaiting review',
            self::Approved => 'Approved',
            self::Returned => 'Sent back',
        };
    }
}
