<?php

namespace App\Models;

use App\Enums\EnquiryType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property EnquiryType $enquiry_type
 * @property Carbon|null $handled_at
 */
#[Fillable(['name', 'email', 'whatsapp_number', 'country', 'subject', 'enquiry_type', 'message'])]
class Enquiry extends Model
{
    use Prunable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'enquiry_type' => EnquiryType::class,
            'handled_at' => 'datetime',
        ];
    }

    /**
     * Enquiries are kept for up to 12 months (see the privacy policy).
     *
     * @return Builder<Enquiry>
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subMonths(12));
    }
}
