<?php

namespace App\Support;

/**
 * Wording shown wherever a student is asked to pay, so the order reference
 * is used consistently in bKash/bank notes and payment screenshots.
 */
class PaymentReference
{
    /**
     * @return list<string>
     */
    public static function instructions(string $reference): array
    {
        return [
            "Write {$reference} in the reference or note field when you pay by bKash, Nagad, Rocket, bank transfer, Wise or any other method.",
            "When you send a payment screenshot by email or WhatsApp, mention {$reference} in your message.",
        ];
    }
}
