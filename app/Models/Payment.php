<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\OrderSource;
use App\Enums\PaymentMethod;
use App\Enums\PaymentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A money movement recorded by an administrator. Kept for accounting even if
 * the related submission is deleted under the retention policy.
 *
 * @property int $id
 * @property int|null $submission_id
 * @property int|null $quotation_id
 * @property string|null $receipt_number
 * @property string $submission_reference
 * @property string|null $country
 * @property OrderSource $source
 * @property PaymentType $type
 * @property Currency $currency
 * @property string $amount
 * @property Carbon $paid_on
 * @property PaymentMethod $method
 * @property string|null $reference
 * @property string $rate_to_bdt
 * @property string $amount_bdt
 * @property string $rate_source
 * @property string|null $note
 * @property int|null $recorded_by
 */
class Payment extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PaymentType::class,
            'source' => OrderSource::class,
            'currency' => Currency::class,
            'method' => PaymentMethod::class,
            'amount' => 'decimal:2',
            'rate_to_bdt' => 'decimal:6',
            'amount_bdt' => 'decimal:2',
            'paid_on' => 'date',
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
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Signed BDT value: refunds count negatively.
     */
    public function signedBdt(): float
    {
        return ($this->type === PaymentType::Refund ? -1 : 1) * (float) $this->amount_bdt;
    }

    /**
     * Signed amount in the payment currency.
     */
    public function signedAmount(): float
    {
        return ($this->type === PaymentType::Refund ? -1 : 1) * (float) $this->amount;
    }
}
