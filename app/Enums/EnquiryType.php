<?php

namespace App\Enums;

enum EnquiryType: string
{
    case General = 'general';
    case Service = 'service';
    case Quotation = 'quotation';
    case Existing = 'existing';
    case Privacy = 'privacy';

    public function label(): string
    {
        return match ($this) {
            self::General => 'General question',
            self::Service => 'Question about a service',
            self::Quotation => 'Request a quotation',
            self::Existing => 'About an existing submission',
            self::Privacy => 'Privacy or data request',
        };
    }
}
