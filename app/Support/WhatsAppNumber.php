<?php

namespace App\Support;

/**
 * Normalises phone numbers to E.164 (e.g. +447700900123) for WhatsApp.
 */
class WhatsAppNumber
{
    public static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if (str_starts_with($value, '00')) {
            $value = '+'.substr($value, 2);
        }

        $hasPlus = str_starts_with($value, '+');
        $digits = preg_replace('/\D+/', '', $value) ?? '';

        if (! $hasPlus || $digits === '') {
            return null;
        }

        return '+'.$digits;
    }

    public static function isValid(?string $value): bool
    {
        $normalized = self::normalize($value);

        return $normalized !== null && preg_match('/^\+[1-9]\d{7,14}$/', $normalized) === 1;
    }

    /**
     * Characters a user may reasonably type in a phone field.
     */
    public static function hasOnlyPhoneCharacters(string $value): bool
    {
        return preg_match('/^[\d\s+().\-]+$/', $value) === 1;
    }
}
