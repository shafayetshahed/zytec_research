<?php

namespace App\Models;

use App\Enums\DeliverableStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

/**
 * A guidance or feedback file prepared for an order. Students only see it
 * after an administrator approves it, and normally only once the order is
 * paid in full.
 *
 * @property int $id
 * @property int $submission_id
 * @property int|null $uploaded_by
 * @property string $original_name
 * @property string $storage_path
 * @property string $mime_type
 * @property string $extension
 * @property int $size
 * @property string $sha256
 * @property string|null $note
 * @property DeliverableStatus $status
 * @property string|null $review_note
 * @property int|null $reviewed_by
 * @property Carbon|null $reviewed_at
 * @property bool $release_before_payment
 * @property Carbon|null $released_at
 * @property Carbon $created_at
 * @property-read Submission $submission
 * @property-read User|null $uploader
 * @property-read User|null $reviewer
 */
class Deliverable extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => DeliverableStatus::class,
            'size' => 'integer',
            'release_before_payment' => 'boolean',
            'reviewed_at' => 'datetime',
            'released_at' => 'datetime',
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
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Whether the student may download it now.
     */
    public function isAvailableToStudent(Submission $submission): bool
    {
        if ($this->status !== DeliverableStatus::Approved) {
            return false;
        }

        $quoted = $submission->quotation?->sent_at !== null;

        return $this->release_before_payment || ! $quoted || $submission->payment_status === PaymentStatus::PaidInFull;
    }

    public function humanSize(): string
    {
        return Number::fileSize($this->size, precision: 1);
    }
}
