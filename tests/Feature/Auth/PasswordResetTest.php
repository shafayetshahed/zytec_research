<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_reset_link_is_sent_to_registered_email(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'student@example.test']);

        $this->post(route('password.email'), ['email' => 'Student@example.test'])->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_response_is_identical_for_unknown_email(): void
    {
        Notification::fake();
        User::factory()->create(['email' => 'student@example.test']);

        $known = $this->post(route('password.email'), ['email' => 'student@example.test'])->getSession()->get('status');
        $this->travel(2)->minutes();
        $unknown = $this->post(route('password.email'), ['email' => 'nobody@example.test'])->getSession()->get('status');

        $this->assertSame($known, $unknown);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'student@example.test']);
        $this->post(route('password.email'), ['email' => 'student@example.test']);

        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token) {
            $token = $notification->token;

            return true;
        });

        $this->get(route('password.reset', ['token' => $token, 'email' => 'student@example.test']))->assertOk();

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'student@example.test',
            'password' => 'brand-new-pass-9',
            'password_confirmation' => 'brand-new-pass-9',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('brand-new-pass-9', $user->fresh()->password));
    }

    public function test_invalid_token_is_rejected(): void
    {
        $user = User::factory()->create(['email' => 'student@example.test']);

        $this->post(route('password.update'), [
            'token' => 'not-a-real-token',
            'email' => 'student@example.test',
            'password' => 'brand-new-pass-9',
            'password_confirmation' => 'brand-new-pass-9',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('password-123', $user->fresh()->password));
    }

    public function test_expired_token_is_rejected(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'student@example.test']);
        $this->post(route('password.email'), ['email' => 'student@example.test']);

        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token) {
            $token = $notification->token;

            return true;
        });

        $this->travel(config('auth.passwords.users.expire') + 1)->minutes();

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'student@example.test',
            'password' => 'brand-new-pass-9',
            'password_confirmation' => 'brand-new-pass-9',
        ])->assertSessionHasErrors('email');
    }

    public function test_reset_requests_are_rate_limited(): void
    {
        Notification::fake();

        for ($i = 0; $i < 3; $i++) {
            $this->post(route('password.email'), ['email' => 'someone@example.test']);
        }

        $this->post(route('password.email'), ['email' => 'someone@example.test'])->assertStatus(429);
    }
}
