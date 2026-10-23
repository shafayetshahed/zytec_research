<?php

namespace Tests\Feature\Auth;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Mail\EmailVerificationCode;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Student',
            'email' => 'Student@Example.test',
            'whatsapp_number' => '+44 7700 900123',
            'password' => 'correct-horse-42',
            'password_confirmation' => 'correct-horse-42',
            'terms' => '1',
        ], $overrides);
    }

    public function test_registration_page_renders(): void
    {
        $this->get(route('register'))->assertOk()->assertSee('Create your student account');
    }

    public function test_valid_registration_creates_unverified_student_and_sends_code(): void
    {
        Mail::fake();

        $response = $this->post(route('register'), $this->validPayload());

        $response->assertRedirect(route('verification.notice'));
        $user = User::where('email', 'student@example.test')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertNull($user->email_verified_at);
        $this->assertSame(UserRole::Student, $user->role);
        $this->assertSame(AccountStatus::Active, $user->status);
        $this->assertSame('+447700900123', $user->whatsapp_number);
        $this->assertNotNull($user->terms_accepted_at);
        $this->assertTrue(Hash::check('correct-horse-42', $user->password));
        $this->assertNotSame('correct-horse-42', $user->password);
        Mail::assertSent(EmailVerificationCode::class, fn (EmailVerificationCode $mail) => $mail->hasTo('student@example.test') && preg_match('/^\d{6}$/', $mail->code) === 1);
    }

    public function test_role_cannot_be_mass_assigned_during_registration(): void
    {
        Mail::fake();

        $this->post(route('register'), $this->validPayload(['role' => 'admin', 'status' => 'active', 'email_verified_at' => now()->toDateTimeString()]));

        $user = User::where('email', 'student@example.test')->firstOrFail();
        $this->assertSame(UserRole::Student, $user->role);
        $this->assertNull($user->email_verified_at);
    }

    public function test_duplicate_email_is_rejected_case_insensitively(): void
    {
        User::factory()->create(['email' => 'student@example.test']);

        $this->post(route('register'), $this->validPayload(['email' => 'STUDENT@example.test']))
            ->assertSessionHasErrors(['email' => 'An account already exists for this email address. Try signing in or resetting your password.']);

        $this->assertSame(1, User::count());
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function invalidWhatsAppNumbers(): array
    {
        return [
            'missing' => [''],
            'no country code' => ['07700900123'],
            'letters' => ['+44 77OO 9OO123'],
            'too short' => ['+44 12'],
            'too long' => ['+44 1234567890123456'],
            'leading zero country code' => ['+0 7700900123'],
        ];
    }

    #[DataProvider('invalidWhatsAppNumbers')]
    public function test_whatsapp_number_must_be_valid_international_format(string $number): void
    {
        $this->post(route('register'), $this->validPayload(['whatsapp_number' => $number]))
            ->assertSessionHasErrors('whatsapp_number');

        $this->assertGuest();
    }

    public function test_password_rules_are_enforced(): void
    {
        $this->post(route('register'), $this->validPayload(['password' => 'short1', 'password_confirmation' => 'short1']))
            ->assertSessionHasErrors('password');

        $this->post(route('register'), $this->validPayload(['password' => 'correct-horse-42', 'password_confirmation' => 'different-42']))
            ->assertSessionHasErrors('password');

        $this->post(route('register'), $this->validPayload(['password' => 'onlylettersherexx', 'password_confirmation' => 'onlylettersherexx']))
            ->assertSessionHasErrors('password');
    }

    public function test_terms_must_be_accepted(): void
    {
        $this->post(route('register'), $this->validPayload(['terms' => null]))
            ->assertSessionHasErrors(['terms' => 'Please confirm that you accept the Terms of Service and Privacy Policy.']);
    }

    public function test_required_fields(): void
    {
        $this->post(route('register'), [])->assertSessionHasErrors(['name', 'email', 'whatsapp_number', 'password', 'terms']);
    }

    public function test_mail_failure_still_creates_account_and_explains_next_step(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));

        $this->post(route('register'), $this->validPayload())
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['email' => 'student@example.test', 'email_verified_at' => null]);
    }

    public function test_registration_is_rate_limited_per_ip(): void
    {
        Mail::fake();

        for ($i = 0; $i < 10; $i++) {
            $this->post(route('register'), $this->validPayload(['email' => "s{$i}@example.test"]));
            auth()->logout();
        }

        $this->post(route('register'), $this->validPayload(['email' => 'eleventh@example.test']))->assertStatus(429);
    }
}
