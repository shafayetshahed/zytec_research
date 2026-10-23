<?php

namespace App\Services;

use App\Enums\OtpResult;
use App\Mail\EmailVerificationCode;
use App\Models\EmailOtp;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Issues and checks six-digit email verification codes.
 *
 * Codes are generated with a CSPRNG, stored only as keyed HMACs, compared in
 * constant time, expire, are single use, and are invalidated when a newer
 * code is issued.
 */
class EmailOtpService
{
    public function issue(User $user): EmailOtp
    {
        $code = str_pad((string) random_int(0, 999_999), 6, '0', STR_PAD_LEFT);
        $expiresMinutes = (int) config('zytecs.otp.expires_minutes');

        $otp = DB::transaction(function () use ($user, $code, $expiresMinutes) {
            $user->emailOtps()->usable()->update(['invalidated_at' => now()]);

            $otp = new EmailOtp;
            $otp->user_id = $user->id;
            $otp->code_hash = $this->hash($user, $code);
            $otp->expires_at = now()->addMinutes($expiresMinutes);
            $otp->save();

            return $otp;
        });

        Mail::to($user->email)->send(new EmailVerificationCode($user->name, $code, $expiresMinutes));

        return $otp;
    }

    public function verify(User $user, string $code): OtpResult
    {
        return DB::transaction(function () use ($user, $code) {
            /** @var EmailOtp|null $otp */
            $otp = $user->emailOtps()->usable()->latest('id')->lockForUpdate()->first();

            if ($otp === null) {
                return OtpResult::NoActiveCode;
            }

            if ($otp->isExpired()) {
                return OtpResult::Expired;
            }

            $maxAttempts = (int) config('zytecs.otp.max_attempts');

            if ($otp->attempts >= $maxAttempts) {
                return OtpResult::TooManyAttempts;
            }

            $otp->attempts++;

            if (! hash_equals($otp->code_hash, $this->hash($user, $code))) {
                if ($otp->attempts >= $maxAttempts) {
                    $otp->invalidated_at = now();
                    $otp->save();

                    return OtpResult::TooManyAttempts;
                }

                $otp->save();

                return OtpResult::Invalid;
            }

            $otp->consumed_at = now();
            $otp->save();

            $user->markEmailAsVerified();

            return OtpResult::Verified;
        });
    }

    /**
     * Seconds the user must wait before another code can be sent.
     */
    public function secondsUntilResend(User $user): int
    {
        $latest = $user->emailOtps()->latest('id')->first();

        if ($latest === null) {
            return 0;
        }

        $availableAt = $latest->created_at->addSeconds((int) config('zytecs.otp.resend_cooldown_seconds'));

        return max(0, (int) ceil(now()->diffInSeconds($availableAt, false)));
    }

    private function hash(User $user, string $code): string
    {
        return hash_hmac('sha256', $user->getKey().'|'.$code, (string) config('app.key'));
    }
}
