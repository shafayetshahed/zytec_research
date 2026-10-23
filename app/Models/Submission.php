<?php

namespace App\Models;

use App\Enums\OrderSource;
use App\Enums\PaymentStatus;
use App\Enums\SubmissionStatus;
use Database\Factories\SubmissionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $reference
 * @property int $user_id
 * @property string $client_token
 * @property string $assignment_name
 * @property string|null $module_code
 * @property OrderSource $source
 * @property string|null $institution
 * @property string|null $country
 * @property string|null $subject
 * @property string|null $assessment_type
 * @property Carbon|null $deadline
 * @property string|null $description
 * @property string|null $contact_email
 * @property string|null $whatsapp_number
 * @property SubmissionStatus $status
 * @property PaymentStatus $payment_status
 * @property Carbon $submitted_at
 * @property Carbon|null $closed_at
 * @property-read User $student
 * @property-read SubmissionFile|null $brief
 * @property-read Quotation|null $quotation
 */
#[Fillable([
    'assignment_name', 'module_code', 'institution', 'country', 'subject',
    'assessment_type', 'deadline', 'description',
])]
class Submission extends Model
{
    /** @use HasFactory<SubmissionFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SubmissionStatus::class,
            'payment_status' => PaymentStatus::class,
            'source' => OrderSource::class,
            'deadline' => 'date',
            'submitted_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasMany<SubmissionFile, $this>
     */
    public function files(): HasMany
    {
        return $this->hasMany(SubmissionFile::class);
    }

    /**
     * @return HasOne<SubmissionFile, $this>
     */
    public function brief(): HasOne
    {
        return $this->hasOne(SubmissionFile::class)->where('is_brief', true);
    }

    /**
     * @return HasOne<Quotation, $this>
     */
    public function quotation(): HasOne
    {
        return $this->hasOne(Quotation::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('paid_on')->orderBy('id');
    }

    /**
     * @return HasMany<SubmissionNote, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(SubmissionNote::class)->latest();
    }

    /**
     * @return HasMany<SubmissionStatusChange, $this>
     */
    public function statusChanges(): HasMany
    {
        return $this->hasMany(SubmissionStatusChange::class)->latest('id');
    }

    /**
     * @return HasMany<NotificationLog, $this>
     */
    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class)->latest('id');
    }

    /**
     * Free-text search across the fields administrators look up most.
     *
     * @param  Builder<Submission>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        if ($term === '') {
            return;
        }

        $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term).'%';

        $query->where(function (Builder $query) use ($like) {
            $query->where('reference', 'like', $like)
                ->orWhere('assignment_name', 'like', $like)
                ->orWhere('module_code', 'like', $like)
                ->orWhere('contact_email', 'like', $like)
                ->orWhereHas('student', fn (Builder $student) => $student
                    ->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like));
        });
    }
}
