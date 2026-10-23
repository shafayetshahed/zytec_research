<?php

namespace Tests\Feature\Student;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_profile_page_shows_details(): void
    {
        $user = User::factory()->create(['email' => 'student@example.test', 'whatsapp_number' => '+447700900123']);

        $this->actingAs($user)->get(route('student.profile'))->assertOk()->assertSee('student@example.test')->assertSee('+447700900123');
    }

    public function test_student_can_update_name_and_whatsapp(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('student.profile.update'), ['name' => 'New Name', 'whatsapp_number' => '+1 (416) 555-0100'])
            ->assertSessionHas('status');

        $this->assertSame('New Name', $user->fresh()->name);
        $this->assertSame('+14165550100', $user->fresh()->whatsapp_number);
    }

    public function test_profile_update_cannot_change_email_or_role(): void
    {
        $user = User::factory()->create(['email' => 'student@example.test']);

        $this->actingAs($user)->put(route('student.profile.update'), [
            'name' => 'Name', 'whatsapp_number' => '+447700900123', 'email' => 'hijack@example.test', 'role' => 'admin',
        ]);

        $this->assertSame('student@example.test', $user->fresh()->email);
        $this->assertTrue($user->fresh()->isStudent());
    }

    public function test_password_change_requires_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('student.profile.password'), [
            'current_password' => 'wrong-one-1', 'password' => 'another-pass-77', 'password_confirmation' => 'another-pass-77',
        ])->assertSessionHasErrors('current_password');

        $this->actingAs($user)->put(route('student.profile.password'), [
            'current_password' => 'password-123', 'password' => 'another-pass-77', 'password_confirmation' => 'another-pass-77',
        ])->assertSessionHas('status');

        $this->assertTrue(Hash::check('another-pass-77', $user->fresh()->password));
    }

    public function test_student_can_request_deletion(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('student.profile.deletion'), [])->assertSessionHasErrors('confirm_deletion');
        $this->actingAs($user)->post(route('student.profile.deletion'), ['confirm_deletion' => '1'])->assertSessionHas('status');

        $this->assertNotNull($user->fresh()->deletion_requested_at);
        $this->assertDatabaseHas('audit_logs', ['action' => 'account.deletion_requested']);
    }
}
