<?php

namespace Tests\Unit;

use App\Support\WhatsAppNumber;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class WhatsAppNumberTest extends TestCase
{
    #[TestWith(['+44 7700 900123', '+447700900123'])]
    #[TestWith(['+1 (416) 555-0100', '+14165550100'])]
    #[TestWith(['0061 412 345 678', '+61412345678'])]
    #[TestWith(['+880 1871-333382', '+8801871333382'])]
    public function test_normalizes_to_e164(string $input, string $expected): void
    {
        $this->assertSame($expected, WhatsAppNumber::normalize($input));
        $this->assertTrue(WhatsAppNumber::isValid($input));
    }

    #[TestWith(['07700900123'])]
    #[TestWith(['+12'])]
    #[TestWith(['+0123456789'])]
    #[TestWith(['+1234567890123456'])]
    #[TestWith([''])]
    public function test_rejects_invalid_numbers(string $input): void
    {
        $this->assertFalse(WhatsAppNumber::isValid($input));
    }
}
