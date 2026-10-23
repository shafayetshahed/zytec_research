<?php

namespace Tests\Feature\Auth;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_login_page_renders(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Sign in to your account');
    }

    public function test_student_can_sign_in_and_is_sent_to_dashboard(): void
    {
        $user = User::factory()->create(['email' => 'student@example.test']);

        $this->post(route('login'), ['email' => 'Student@Example.test', 'password' => 'password-123'])
            ->assertRedirect(route('student.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
        $this->assertDatabaseHas('audit_logs', ['action' => 'account.login', 'actor_id' => $user->id]);
    }

    public function test_admin_is_sent_to_admin_dashboard(): void
    {
        User::factory()->admin()->create(['email' => 'admin@example.test']);

        $this->post(route('login'), ['email' => 'admin@example.test', 'password' => 'password-123'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_wrong_password_is_rejected_with_generic_message(): void
    {
        User::factory()->create(['email' => 'student@example.test']);

        $this->post(route('login'), ['email' => 'student@example.test', 'password' => 'wrong-password'])
            ->assertSessionHasErrors(['email' => 'These details do not match our records.']);

        $this->post(route('login'), ['email' => 'nobody@example.test', 'password' => 'wrong-password'])
            ->assertSessionHasErrors(['email' => 'These details do not match our records.']);

        $this->assertGuest();
    }

    public function test_suspended_account_cannot_sign_in(): void
    {
        User::factory()->suspended()->create(['email' => 'student@example.test']);

        $this->post(route('login'), ['email' => 'student@example.test', 'password' => 'password-123'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_suspended_account_is_signed_out_on_next_request(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('student.dashboard'))->assertOk();

        $user->forceFill(['status' => 'suspended'])->save();

        $this->actingAs($user->fresh())->get(route('student.dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_session_is_regenerated_on_login(): void
    {
        User::factory()->create(['email' => 'student@example.test']);

        $this->startSession();
        $before = session()->getId();

        $this->post(route('login'), ['email' => 'student@example.test', 'password' => 'password-123']);

        $this->assertNotSame($before, session()->getId());
    }

    public function test_login_is_rate_limited(): void
    {
        User::factory()->create(['email' => 'student@example.test']);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login'), ['email' => 'student@example.test', 'password' => 'wrong-password']);
        }

        $this->post(route('login'), ['email' => 'student@example.test', 'password' => 'password-123'])->assertStatus(429);
        $this->assertGuest();
    }

    public function test_intended_url_is_honoured(): void
    {
        User::factory()->create(['email' => 'student@example.test']);

        $this->get(route('student.submissions.create'))->assertRedirect(route('login'));
        $this->post(route('login'), ['email' => 'student@example.test', 'password' => 'password-123'])
            ->assertRedirect(route('student.submissions.create'));
    }

    public function test_logout_invalidates_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();
        $this->get(route('student.dashboard'))->assertRedirect(route('login'));
    }

    public function test_logout_requires_post_with_csrf_route(): void
    {
        $this->actingAs(User::factory()->create())->get('/logout')->assertStatus(405);
    }

    public function test_signed_in_user_is_redirected_away_from_login_and_register(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('login'))->assertRedirect(route('student.dashboard'));
        $this->actingAs($user)->get(route('register'))->assertRedirect(route('student.dashboard'));
    }

    public function test_audit_log_never_stores_password(): void
    {
        User::factory()->create(['email' => 'student@example.test']);
        $this->post(route('login'), ['email' => 'student@example.test', 'password' => 'password-123']);

        foreach (AuditLog::all() as $log) {
            $this->assertStringNotContainsString('password-123', json_encode($log->toArray()));
        }
    }
}
