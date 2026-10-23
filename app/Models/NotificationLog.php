<?php

namespace App\Models;

use App\Enums\NotificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $submission_id
 * @property int|null $enquiry_id
 * @property string $type
 * @property string $recipient
 * @property NotificationStatus $status
 * @property int $attempts
 * @property Carbon|null $last_attempt_at
 * @property Carbon|null $sent_at
 * @property string|null $error_summary
 *
 * Delivery record for an outbound business notification. Never holds message bodies or credentials.
 */
class NotificationLog extends Model
{
    public const TYPE_NEW_SUBMISSION = 'new_submission';

    public const TYPE_NEW_ENQUIRY = 'new_enquiry';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => NotificationStatus::class,
            'last_attempt_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Submission, $this>
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * @return BelongsTo<Enquiry, $this>
     */
    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_NEW_SUBMISSION => 'New brief',
            self::TYPE_NEW_ENQUIRY => 'New enquiry',
            default => $this->type,
        };
    }
}
