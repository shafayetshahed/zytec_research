<?php

namespace Tests\Feature\Admin;

use App\Models\EmailOtp;
use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class EnquiryManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_view_and_toggle_enquiry(): void
    {
        $enquiry = Enquiry::create(['name' => 'Rumi', 'email' => 'rumi@example.test', 'enquiry_type' => 'privacy', 'message' => 'Please delete my data.']);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.enquiries.index'))->assertOk()->assertSee('Rumi')->assertSee('Open');
        $this->actingAs($admin)->get(route('admin.enquiries.show', $enquiry))->assertOk()->assertSee('Please delete my data.');

        $this->actingAs($admin)->post(route('admin.enquiries.handled', $enquiry))->assertSessionHas('status', 'Marked as handled.');
        $this->assertNotNull($enquiry->fresh()->handled_at);

        $this->actingAs($admin)->post(route('admin.enquiries.handled', $enquiry))->assertSessionHas('status', 'Marked as open.');
        $this->assertNull($enquiry->fresh()->handled_at);
    }

    public function test_old_enquiries_and_otps_are_pruned(): void
    {
        $this->freezeTime();
        $old = Enquiry::create(['name' => 'Old', 'email' => 'o@example.test', 'enquiry_type' => 'general', 'message' => 'Old message here']);
        $old->forceFill(['created_at' => now()->subMonths(13)])->save();
        $recent = Enquiry::create(['name' => 'New', 'email' => 'n@example.test', 'enquiry_type' => 'general', 'message' => 'New message here']);

        $user = User::factory()->unverified()->create();
        $staleOtp = new EmailOtp;
        $staleOtp->forceFill(['user_id' => $user->id, 'code_hash' => str_repeat('a', 64), 'expires_at' => now()->subDays(8)])->save();
        $staleOtp->forceFill(['created_at' => now()->subDays(8)])->save();

        $this->artisan('model:prune')->assertSuccessful();

        $this->assertModelMissing($old);
        $this->assertModelExists($recent);
        $this->assertModelMissing($staleOtp);
    }

    public function test_settings_page_warns_about_log_mailer_and_hides_secrets(): void
    {
        config(['mail.default' => 'log', 'mail.mailers.smtp.password' => 'super-secret-smtp-pass']);

        $this->actingAs(User::factory()->admin()->create())->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('Mail is not being delivered')
            ->assertDontSee('super-secret-smtp-pass');
    }
}
