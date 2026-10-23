<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case NotQuoted = 'not_quoted';
    case AwaitingPayment = 'awaiting_payment';
    case PartPaid = 'part_paid';
    case DepositPaid = 'deposit_paid';
    case PaidInFull = 'paid_in_full';

    public function label(): string
    {
        return match ($this) {
            self::NotQuoted => 'No quotation yet',
            self::AwaitingPayment => 'Awaiting payment',
            self::PartPaid => 'Part paid',
            self::DepositPaid => 'Deposit paid',
            self::PaidInFull => 'Paid in full',
        };
    }
}
