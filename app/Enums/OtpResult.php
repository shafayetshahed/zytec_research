<?php

namespace App\Enums;

enum OtpResult: string
{
    case Verified = 'verified';
    case Invalid = 'invalid';
    case Expired = 'expired';
    case TooManyAttempts = 'too_many_attempts';
    case NoActiveCode = 'no_active_code';

    public function message(): string
    {
        return match ($this) {
            self::Verified => 'Your email address has been verified.',
            self::Invalid => 'That code is not correct. Please check the latest email and try again.',
            self::Expired => 'That code has expired. Please request a new code.',
            self::TooManyAttempts => 'Too many incorrect attempts. Please request a new code.',
            self::NoActiveCode => 'There is no active code for your account. Please request a new code.',
        };
    }
}
