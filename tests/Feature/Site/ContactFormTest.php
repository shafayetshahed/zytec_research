<?php

namespace Tests\Feature\Site;

use App\Mail\NewEnquiryReceived;
use App\Models\Enquiry;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_enquiry_is_saved_and_emailed(): void
    {
        Mail::fake();

        $this->post(route('contact.store'), [
            'name' => 'Priya', 'whatsapp_number' => '+61 412 345 678', 'enquiry_type' => 'service',
            'message' => 'Do you help with literature reviews?', 'country' => 'Australia',
        ])->assertRedirect(route('contact'))->assertSessionHas('status');

        $enquiry = Enquiry::sole();
        $this->assertSame('+61412345678', $enquiry->whatsapp_number);
        Mail::assertSent(NewEnquiryReceived::class, fn ($mail) => $mail->hasTo('research@zytecs.com'));
    }

    public function test_a_reply_channel_is_required(): void
    {
        $this->post(route('contact.store'), ['name' => 'Priya', 'enquiry_type' => 'general', 'message' => 'Hello, a question here.'])
            ->assertSessionHasErrors(['email', 'whatsapp_number']);
    }

    public function test_honeypot_blocks_bots(): void
    {
        $this->post(route('contact.store'), [
            'name' => 'Bot', 'email' => 'bot@example.test', 'enquiry_type' => 'general', 'message' => 'Buy things now please', 'website' => 'http://spam.example',
        ])->assertSessionHasErrors('website');

        $this->assertSame(0, Enquiry::count());
    }

    public function test_enquiries_are_rate_limited(): void
    {
        Mail::fake();
        $payload = ['name' => 'P', 'email' => 'p@example.test', 'enquiry_type' => 'general', 'message' => 'Just a question here.'];

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('contact.store'), $payload);
        }

        $this->post(route('contact.store'), $payload)->assertStatus(429);
    }

    public function test_csrf_protection_is_active_for_forms(): void
    {
        $this->assertContains(PreventRequestForgery::class, app(Kernel::class)->getMiddlewareGroups()['web']);
        $this->get(route('contact'))->assertSee('name="_token"', false);
    }
}
