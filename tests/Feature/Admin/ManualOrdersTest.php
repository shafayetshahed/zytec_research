<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderSource;
use App\Enums\PaymentStatus;
use App\Enums\SubmissionStatus;
use App\Mail\NewSubmissionReceived;
use App\Mail\QuotationIssued;
use App\Models\Payment;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ManualOrdersTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function admin(): User
    {
        return User::where('role', 'admin')->first() ?? User::factory()->admin()->create();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function addOrder(User $client, array $overrides = []): TestResponse
    {
        return $this->actingAs($this->admin())->post(route('admin.orders.store'), array_merge([
            'client_id' => $client->id, 'source' => 'whatsapp', 'assignment_name' => 'HRM report', 'module_code' => 'shr080-6',
            'country' => 'United Kingdom', 'currency' => 'GBP', 'total_amount' => '120', 'deposit_amount' => '40',
            'order_date' => now()->subDay()->toDateString(), 'notes' => 'Agreed on WhatsApp',
        ], $overrides));
    }

    public function test_admin_can_add_a_client_with_only_a_name(): void
    {
        $this->actingAs($this->admin())->post(route('admin.clients.store'), ['name' => 'Arnob'])->assertRedirect();

        $client = User::where('name', 'Arnob')->sole();
        $this->assertTrue($client->isStudent());
        $this->assertTrue($client->isManualClient());
        $this->assertNull($client->email);
        $this->assertNull($client->email_verified_at);
        $this->assertDatabaseHas('audit_logs', ['action' => 'client.created']);
    }

    public function test_client_validation_and_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.test']);

        $this->actingAs($this->admin())->post(route('admin.clients.store'), ['name' => 'A', 'email' => 'TAKEN@example.test', 'whatsapp_number' => '0171'])
            ->assertSessionHasErrors(['name', 'email', 'whatsapp_number']);
    }

    public function test_client_with_email_can_claim_portal_access_by_resetting_password(): void
    {
        Notification::fake();
        $this->actingAs($this->admin())->post(route('admin.clients.store'), ['name' => 'Arnob', 'email' => 'arnob@example.test', 'whatsapp_number' => '+8801711000000', 'country' => 'Australia']);
        auth()->logout();

        $this->post(route('password.email'), ['email' => 'arnob@example.test']);

        Notification::assertSentTo(User::where('email', 'arnob@example.test')->sole(), ResetPassword::class);
    }

    public function test_client_without_email_cannot_sign_in(): void
    {
        $client = User::factory()->unverified()->create(['email' => null, 'created_by' => $this->admin()->id]);

        $this->post(route('login'), ['email' => '', 'password' => 'password-123'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertNull($client->fresh()->email);
    }

    public function test_verified_email_cannot_be_changed_by_admin(): void
    {
        $student = User::factory()->create(['email' => 'real@example.test']);

        $this->actingAs($this->admin())->put(route('admin.clients.update', $student), ['name' => 'New Name', 'email' => 'other@example.test', 'whatsapp_number' => '+447700900123']);

        $student->refresh();
        $this->assertSame('New Name', $student->name);
        $this->assertSame('real@example.test', $student->email);
    }

    public function test_manual_order_creates_order_and_quotation_without_notifying_business(): void
    {
        Mail::fake();
        $client = User::factory()->unverified()->create(['email' => null, 'whatsapp_number' => '+8801711000000', 'country' => 'Australia', 'created_by' => $this->admin()->id]);

        $response = $this->addOrder($client);

        $order = Submission::sole();
        $response->assertRedirect(route('admin.submissions.show', $order));
        $this->assertSame(OrderSource::WhatsApp, $order->source);
        $this->assertSame('SHR080-6', $order->module_code);
        $this->assertSame(SubmissionStatus::Accepted, $order->status);
        $this->assertSame(PaymentStatus::AwaitingPayment, $order->payment_status);
        $this->assertNull($order->contact_email);
        $this->assertSame('+8801711000000', $order->whatsapp_number);
        $this->assertSame(now()->subDay()->toDateString(), $order->submitted_at->toDateString());
        $this->assertSame('Agreed on WhatsApp', $order->description);
        $this->assertSame('120.00', $order->quotation->total_amount);
        $this->assertSame('United Kingdom', $order->quotation->country);
        $this->assertNotNull($order->quotation->number);
        $this->assertCount(0, $order->files);
        Mail::assertNotSent(NewSubmissionReceived::class);
        Mail::assertNotSent(QuotationIssued::class);
        $this->assertDatabaseHas('audit_logs', ['action' => 'order.manual_created']);
    }

    public function test_manual_order_can_email_quotation_when_client_has_email(): void
    {
        Mail::fake();
        $client = User::factory()->create(['email' => 'client@example.test']);

        $this->addOrder($client, ['email_client' => '1']);

        Mail::assertSent(QuotationIssued::class, fn ($mail) => $mail->hasTo('client@example.test'));
    }

    public function test_manual_order_validation(): void
    {
        $client = User::factory()->create();

        $this->addOrder($client, ['source' => 'portal'])->assertSessionHasErrors('source');
        $this->addOrder($client, ['deposit_amount' => '500'])->assertSessionHasErrors('deposit_amount');
        $this->addOrder($client, ['order_date' => now()->addDay()->toDateString()])->assertSessionHasErrors('order_date');
        $this->addOrder($client, ['assignment_name' => ''])->assertSessionHasErrors('assignment_name');
        $this->addOrder($this->admin(), [])->assertSessionHasErrors('client_id');
        $this->assertSame(0, Submission::count());
    }

    public function test_payments_on_manual_order_flow_into_accounting_by_client_and_source(): void
    {
        Mail::fake();
        $client = User::factory()->create(['name' => 'Arnob', 'email' => null, 'created_by' => $this->admin()->id]);
        $this->addOrder($client);
        $order = Submission::sole();
        // A portal order for the same person sits on the same profile.
        $portal = Submission::factory()->for($client, 'student')->create();

        $this->actingAs($this->admin())->post(route('admin.submissions.payments.store', $order), [
            'type' => 'payment', 'amount' => '40', 'paid_on' => now()->toDateString(), 'method' => 'bkash', 'rate_to_bdt' => '150',
        ])->assertSessionHas('status');

        $payment = Payment::sole();
        $this->assertSame(OrderSource::WhatsApp, $payment->source);
        $this->assertSame(SubmissionStatus::InProgress, $order->fresh()->status);
        Mail::assertNothingSent(); // no email address on file

        $response = $this->actingAs($this->admin())->get(route('admin.accounting', ['period' => 'this_year']))->assertOk();
        $this->assertSame(6000.0, $response->viewData('bySource')['WhatsApp']['bdt']);
        $arnob = $response->viewData('byClient')[$client->id];
        $this->assertSame('Arnob', $arnob['name']);
        $this->assertSame(6000.0, $arnob['bdt']);
        $this->assertSame(['GBP' => 80.0], $arnob['owed']);
        $response->assertSee('Arnob');

        $this->actingAs($this->admin())->get(route('admin.accounting', ['source' => 'phone']))
            ->assertViewHas('summary', fn ($s) => $s['net_bdt'] === 0.0);

        $this->actingAs($this->admin())->get(route('admin.students.show', $client))
            ->assertSee($order->reference)->assertSee($portal->reference)->assertSee('GBP 80.00 due');
    }

    public function test_manual_orders_render_everywhere_without_email_or_module(): void
    {
        Mail::fake();
        $client = User::factory()->unverified()->create(['email' => null, 'whatsapp_number' => null, 'created_by' => $this->admin()->id]);
        $this->addOrder($client, ['module_code' => '']);
        $order = Submission::sole();
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.submissions.show', $order))->assertOk()->assertSee('WhatsApp')->assertSee('None');
        $this->actingAs($admin)->get(route('admin.submissions.index'))->assertOk()->assertSee('No email');
        $this->actingAs($admin)->get(route('admin.students.index'))->assertOk()->assertSee('Added by admin');
        $this->actingAs($admin)->get(route('admin.students.show', $client))->assertOk()->assertSee('No email, no portal access');
        $this->actingAs($admin)->get(route('admin.documents.quotation', $order->reference))->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->actingAs($admin)->get(route('admin.orders.create', ['client' => $client->id]))->assertOk()->assertSee('selected', false);
    }

    public function test_client_without_email_can_be_deleted_by_typing_name(): void
    {
        $client = User::factory()->create(['name' => 'Arnob Client', 'email' => null, 'created_by' => $this->admin()->id]);

        $this->actingAs($this->admin())->delete(route('admin.students.destroy', $client), ['confirm_email' => 'wrong'])->assertSessionHasErrors('confirm_email');
        $this->actingAs($this->admin())->delete(route('admin.students.destroy', $client), ['confirm_email' => 'Arnob Client'])->assertRedirect();

        $this->assertModelMissing($client);
    }

    public function test_students_cannot_reach_client_or_order_routes(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)->get(route('admin.clients.create'))->assertForbidden();
        $this->actingAs($student)->post(route('admin.clients.store'), ['name' => 'X'])->assertForbidden();
        $this->actingAs($student)->get(route('admin.orders.create'))->assertForbidden();
        $this->actingAs($student)->post(route('admin.orders.store'), [])->assertForbidden();
        $this->assertSame(1, User::count());
    }
}
