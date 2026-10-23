<?php

namespace App\Enums;

enum Currency: string
{
    case AUD = 'AUD';
    case GBP = 'GBP';
    case USD = 'USD';
    case CAD = 'CAD';
    case BDT = 'BDT';
    case MYR = 'MYR';

    public function label(): string
    {
        return match ($this) {
            self::AUD => 'Australian dollar (AUD)',
            self::GBP => 'British pound (GBP)',
            self::USD => 'US dollar (USD)',
            self::CAD => 'Canadian dollar (CAD)',
            self::BDT => 'Bangladeshi taka (BDT)',
            self::MYR => 'Malaysian ringgit (MYR)',
        };
    }

    /**
     * Formats an amount with the currency code, e.g. "AUD 1,250.00".
     */
    public function format(string|float|int|null $amount): string
    {
        return $this->value.' '.number_format((float) $amount, 2);
    }
}
