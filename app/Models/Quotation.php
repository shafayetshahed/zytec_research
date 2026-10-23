<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\PaymentType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $submission_id
 * @property string|null $number
 * @property string|null $invoice_number
 * @property Carbon|null $invoiced_at
 * @property Currency $currency
 * @property string $total_amount
 * @property string $deposit_amount
 * @property Carbon|null $work_due_date
 * @property string|null $country
 * @property string|null $message
 * @property Carbon|null $sent_at
 * @property int|null $created_by
 * @property-read Submission $submission
 */
#[Fillable(['currency', 'total_amount', 'deposit_amount', 'work_due_date', 'country', 'message'])]
class Quotation extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'currency' => Currency::class,
            'total_amount' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'work_due_date' => 'date',
            'sent_at' => 'datetime',
            'invoiced_at' => 'datetime',
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
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('paid_on')->orderBy('id');
    }

    /**
     * Net amount received in the quotation currency (payments minus refunds).
     */
    public function amountPaid(): float
    {
        return round($this->payments->sum(fn (Payment $payment) => $payment->type === PaymentType::Refund ? -(float) $payment->amount : (float) $payment->amount), 2);
    }

    public function balance(): float
    {
        return round(max(0, (float) $this->total_amount - $this->amountPaid()), 2);
    }
}
