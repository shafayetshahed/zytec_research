<?php

namespace App\Rules;

use App\Support\WhatsAppNumber as WhatsAppNumberSupport;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class WhatsAppNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)
            || ! WhatsAppNumberSupport::hasOnlyPhoneCharacters($value)
            || ! WhatsAppNumberSupport::isValid($value)) {
            $fail('Enter a WhatsApp number with its country code, for example +44 7700 900123.');
        }
    }
}
