<?php

namespace App\Support;

use App\Models\Submission;

/**
 * Generates unguessable, human-friendly references such as ZR-2610-K7Q2MX.
 */
class SubmissionReference
{
    /** Crockford-style alphabet without easily confused characters. */
    private const ALPHABET = '23456789ABCDEFGHJKMNPQRSTVWXYZ';

    public static function generate(): string
    {
        do {
            $suffix = '';

            for ($i = 0; $i < 6; $i++) {
                $suffix .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
            }

            $reference = 'ZR-'.now()->format('ym').'-'.$suffix;
        } while (Submission::where('reference', $reference)->exists());

        return $reference;
    }
}
