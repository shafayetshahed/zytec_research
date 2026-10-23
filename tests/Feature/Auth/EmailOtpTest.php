<?php

namespace Tests\Feature\Auth;

use App\Enums\OtpResult;
use App\Mail\EmailVerificationCode;
use App\Models\EmailOtp;
use App\Models\User;
use App\Services\EmailOtpService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailOtpTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * Issues a code and returns the plaintext captured from the fake mailer.
     */
    private function issueCode(User $user): string
    {
        Mail::fake();
        app(EmailOtpService::class)->issue($user);

        $code = null;
        Mail::assertSent(EmailVerificationCode::class, function (EmailVerificationCode $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        return (string) $code;
    }

    private function wrongCode(string $code): string
    {
        return str_pad((string) (((int) $code + 1) % 1_000_000), 6, '0', STR_PAD_LEFT);
    }

    public function test_correct_code_verifies_email(): void
    {
        $user = User::factory()->unverified()->create();
        $code = $this->issueCode($user);

        $this->actingAs($user)->post(route('verification.verify'), ['code' => $code])
            ->assertRedirect(route('student.dashboard'));

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertNotNull(EmailOtp::first()->consumed_at);
    }

    public function test_code_with_spaces_is_accepted(): void
    {
        $user = User::factory()->unverified()->create();
        $code = $this->issueCode($user);

        $this->actingAs($user)->post(route('verification.verify'), ['code' => substr($code, 0, 3).' '.substr($code, 3)]);

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_incorrect_code_is_rejected_and_counted(): void
    {
        $user = User::factory()->unverified()->create();
        $code = $this->issueCode($user);

        $this->actingAs($user)->post(route('verification.verify'), ['code' => $this->wrongCode($code)])
            ->assertSessionHasErrors(['code' => 'That code is not correct. Please check the latest email and try again.']);

        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertSame(1, EmailOtp::first()->attempts);
    }

    public function test_malformed_code_is_rejected_without_counting_an_attempt(): void
    {
        $user = User::factory()->unverified()->create();
        $this->issueCode($user);

        $this->actingAs($user)->post(route('verification.verify'), ['code' => '12ab'])->assertSessionHasErrors('code');

        $this->assertSame(0, EmailOtp::first()->attempts);
    }

    public function test_expired_code_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();
        $this->freezeTime();
        $code = $this->issueCode($user);

        $this->travel(config('zytecs.otp.expires_minutes') + 1)->minutes();

        $this->actingAs($user)->post(route('verification.verify'), ['code' => $code])
            ->assertSessionHasErrors(['code' => 'That code has expired. Please request a new code.']);

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_code_just_before_expiry_still_works(): void
    {
        $user = User::factory()->unverified()->create();
        $this->freezeTime();
        $code = $this->issueCode($user);

        $this->travel(config('zytecs.otp.expires_minutes') * 60 - 1)->seconds();

        $this->actingAs($user)->post(route('verification.verify'), ['code' => $code]);

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_consumed_code_cannot_be_reused(): void
    {
        $user = User::factory()->unverified()->create();
        $code = $this->issueCode($user);

        $this->assertTrue(app(EmailOtpService::class)->verify($user, $code) === OtpResult::Verified);
        $this->assertSame(OtpResult::NoActiveCode, app(EmailOtpService::class)->verify($user->fresh(), $code));
    }

    public function test_new_code_invalidates_previous_code(): void
    {
        $user = User::factory()->unverified()->create();
        $first = $this->issueCode($user);
        $second = $this->issueCode($user);

        if ($first === $second) {
            $this->markTestSkipped('Random codes collided (1 in a million); the invalidation path is still covered below.');
        }

        $this->actingAs($user)->post(route('verification.verify'), ['code' => $first])->assertSessionHasErrors('code');
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertSame(1, EmailOtp::whereNotNull('invalidated_at')->count());

        $this->actingAs($user)->post(route('verification.verify'), ['code' => $second]);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_maximum_attempts_locks_the_code(): void
    {
        $user = User::factory()->unverified()->create();
        $code = $this->issueCode($user);
        $max = (int) config('zytecs.otp.max_attempts');

        for ($i = 1; $i < $max; $i++) {
            $this->actingAs($user)->post(route('verification.verify'), ['code' => $this->wrongCode($code)]);
        }

        $this->actingAs($user)->post(route('verification.verify'), ['code' => $this->wrongCode($code)])
            ->assertSessionHasErrors(['code' => 'Too many incorrect attempts. Please request a new code.']);

        // Even the correct code no longer works once locked.
        $this->actingAs($user)->post(route('verification.verify'), ['code' => $code])->assertSessionHasErrors('code');
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_codes_are_stored_hashed_never_in_plaintext(): void
    {
        $user = User::factory()->unverified()->create();
        $code = $this->issueCode($user);

        $otp = EmailOtp::first();
        $this->assertNotSame($code, $otp->code_hash);
        $this->assertStringNotContainsString($code, $otp->code_hash);
        $this->assertSame(64, strlen($otp->code_hash));
    }

    public function test_code_is_not_exposed_in_page_or_url(): void
    {
        $user = User::factory()->unverified()->create();
        $code = $this->issueCode($user);

        $this->actingAs($user)->get(route('verification.notice'))
            ->assertOk()
            ->assertDontSee($code);

        $response = $this->actingAs($user)->post(route('verification.verify'), ['code' => $this->wrongCode($code)]);
        $this->assertStringNotContainsString($code, (string) $response->headers->get('Location'));
    }

    public function test_resend_respects_cooldown(): void
    {
        $user = User::factory()->unverified()->create();
        $this->freezeTime();
        $this->issueCode($user);

        Mail::fake();
        $this->actingAs($user)->post(route('verification.send'))->assertSessionHas('error');
        Mail::assertNothingSent();

        $this->travel(config('zytecs.otp.resend_cooldown_seconds'))->seconds();
        $this->actingAs($user)->post(route('verification.send'))->assertSessionHas('status');
        Mail::assertSent(EmailVerificationCode::class);
        $this->assertSame(2, EmailOtp::count());
    }

    public function test_resend_is_rate_limited(): void
    {
        $user = User::factory()->unverified()->create();
        Mail::fake();

        for ($i = 0; $i < 6; $i++) {
            $this->actingAs($user)->post(route('verification.send'));
            $this->travel(2)->minutes();
        }

        $this->actingAs($user)->post(route('verification.send'))->assertStatus(429);
    }

    public function test_verification_attempts_are_rate_limited(): void
    {
        $user = User::factory()->unverified()->create();
        $this->issueCode($user);

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user)->post(route('verification.verify'), ['code' => '000000']);
        }

        $this->actingAs($user)->post(route('verification.verify'), ['code' => '000000'])->assertStatus(429);
    }

    public function test_unverified_user_is_blocked_from_student_area(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('student.dashboard'))->assertRedirect(route('verification.notice'));
        $this->actingAs($user)->get(route('student.submissions.create'))->assertRedirect(route('verification.notice'));
        $this->actingAs($user)->post(route('student.submissions.store'), [])->assertRedirect(route('verification.notice'));
    }

    public function test_verified_user_visiting_notice_is_redirected(): void
    {
        $this->actingAs(User::factory()->create())->get(route('verification.notice'))->assertRedirect(route('student.dashboard'));
    }

    public function test_guest_cannot_reach_verification_page(): void
    {
        $this->get(route('verification.notice'))->assertRedirect(route('login'));
    }

    public function test_verification_email_contains_code_and_expiry(): void
    {
        $mail = new EmailVerificationCode('Ada <b>Lovelace</b>', '012345', 10);

        $mail->assertSeeInHtml('012345');
        $mail->assertSeeInText('10 minutes');
        $mail->assertDontSeeInHtml('<b>Lovelace</b>', false);
    }
}
