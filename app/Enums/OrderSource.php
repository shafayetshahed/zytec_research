<?php

namespace App\Enums;

enum OrderSource: string
{
    case Portal = 'portal';
    case WhatsApp = 'whatsapp';
    case Phone = 'phone';
    case Email = 'email';
    case InPerson = 'in_person';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Portal => 'Student portal',
            self::WhatsApp => 'WhatsApp',
            self::Phone => 'Phone',
            self::Email => 'Email',
            self::InPerson => 'In person',
            self::Other => 'Other',
        };
    }

    /**
     * Sources an administrator can choose for a manually entered order.
     *
     * @return list<self>
     */
    public static function manual(): array
    {
        return [self::WhatsApp, self::Phone, self::Email, self::InPerson, self::Other];
    }
}
