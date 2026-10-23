<?php

namespace Tests\Feature\Admin;

use App\Enums\SubmissionStatus;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DuesTest extends TestCase
{
    use LazilyRefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    private function order(User $client, string $currency, string $total, array $payments = [], string $status = 'new'): Submission
    {
        Mail::fake();
        $submission = Submission::factory()->for($client, 'student')->create(['status' => $status]);
        $this->actingAs($this->admin)->put(route('admin.submissions.quotation', $submission), ['currency' => $currency, 'total_amount' => $total, 'deposit_amount' => '0', 'action' => 'send']);

        foreach ($payments as $amount) {
            $this->actingAs($this->admin)->post(route('admin.submissions.payments.store', $submission), [
                'type' => 'payment', 'amount' => $amount, 'paid_on' => now()->toDateString(), 'method' => 'bkash', 'rate_to_bdt' => $currency === 'BDT' ? null : '100',
            ]);
        }

        return $submission->fresh();
    }

    private function seedClients(): array
    {
        Http::preventStrayRequests();
        Http::fake(['*' => Http::response(['aud' => ['bdt' => 80.0], 'usd' => ['bdt' => 120.0], 'gbp' => ['bdt' => 160.0]])]);

        $arnob = User::factory()->create(['name' => 'Arnob', 'country' => 'Australia', 'whatsapp_number' => '+61412345678']);
        $juthi = User::factory()->create(['name' => 'Juthi', 'country' => 'United Kingdom', 'whatsapp_number' => '+447700900111']);
        $nadia = User::factory()->create(['name' => 'Nadia', 'country' => null, 'whatsapp_number' => null]);

        $a1 = $this->order($arnob, 'AUD', '300', ['100']);       // AUD 200 due
        $a2 = $this->order($arnob, 'USD', '50');                  // USD 50 due
        $j1 = $this->order($juthi, 'GBP', '100', ['100']);       // paid up
        Submission::factory()->for($nadia, 'student')->create();  // no quote yet

        return compact('arnob', 'juthi', 'nadia', 'a1', 'a2', 'j1');
    }

    public function test_summary_figures(): void
    {
        $this->seedClients();

        $stats = $this->actingAs($this->admin)->get(route('admin.dues'))->assertOk()->viewData('stats');

        $this->assertSame(3, $stats['clients']);
        $this->assertSame(1, $stats['clients_with_dues']);
        $this->assertSame(4, $stats['orders']);
        $this->assertSame(['AUD' => 200.0, 'USD' => 50.0], $stats['due_by_currency']);
        $this->assertSame(22000.0, $stats['due_bdt']); // 200×80 + 50×120
        $this->assertSame(20000.0, $stats['paid_bdt']); // (100 + 100) × 100
    }

    public function test_client_rows_show_billed_paid_due_and_unpaid_orders(): void
    {
        ['arnob' => $arnob, 'a1' => $a1, 'a2' => $a2] = $this->seedClients();

        $response = $this->actingAs($this->admin)->get(route('admin.dues'));
        $rows = $response->viewData('rows')->getCollection();

        $first = $rows->first();
        $this->assertSame('Arnob', $first['client']->name); // biggest dues first
        $this->assertSame(['AUD' => 300.0, 'USD' => 50.0], $first['billed']);
        $this->assertSame(['AUD' => 100.0], $first['paid']);
        $this->assertSame(['AUD' => 200.0, 'USD' => 50.0], $first['due']);
        $this->assertEqualsCanonicalizing([$a1->reference, $a2->reference], $first['due_orders']->pluck('reference')->all());

        $response->assertSee('AUD 200.00')->assertSee('USD 50.00')->assertSee('2 unpaid orders')->assertSee('Paid up')->assertSee('tel:+61412345678', false);
    }

    public function test_filters(): void
    {
        $this->seedClients();
        $names = fn ($response) => $response->viewData('rows')->getCollection()->map(fn ($r) => $r['client']->name)->values()->all();

        $this->assertSame(['Arnob'], $names($this->actingAs($this->admin)->get(route('admin.dues', ['show' => 'due']))));
        $this->assertSame(['Juthi'], $names($this->actingAs($this->admin)->get(route('admin.dues', ['show' => 'paid']))));
        // Nadia has an order without a quotation, so she is not a "no orders" client.
        $this->assertSame([], $names($this->actingAs($this->admin)->get(route('admin.dues', ['show' => 'none']))));
        User::factory()->create(['name' => 'Zara', 'country' => 'Canada']);
        $this->assertSame(['Zara'], $names($this->actingAs($this->admin)->get(route('admin.dues', ['show' => 'none']))));
        $this->assertSame(['Juthi'], $names($this->actingAs($this->admin)->get(route('admin.dues', ['country' => 'United Kingdom']))));
        $this->assertSame(['Nadia'], $names($this->actingAs($this->admin)->get(route('admin.dues', ['country' => 'Not set']))));
        $this->assertSame(['Juthi'], $names($this->actingAs($this->admin)->get(route('admin.dues', ['q' => 'juth']))));
        $this->assertSame(['Arnob', 'Juthi', 'Nadia', 'Zara'], $names($this->actingAs($this->admin)->get(route('admin.dues', ['sort' => 'name']))));
        $this->actingAs($this->admin)->get(route('admin.dues', ['show' => 'everything']))->assertSessionHasErrors('show');
    }

    public function test_search_by_order_reference(): void
    {
        ['a2' => $a2] = $this->seedClients();

        $rows = $this->actingAs($this->admin)->get(route('admin.dues', ['q' => $a2->reference]))->viewData('rows')->getCollection();

        $this->assertSame(['Arnob'], $rows->map(fn ($r) => $r['client']->name)->all());
    }

    public function test_whatsapp_reminder_lists_dues_and_references(): void
    {
        ['a1' => $a1] = $this->seedClients();

        $html = $this->actingAs($this->admin)->get(route('admin.dues'))->getContent();

        preg_match('#https://wa\.me/61412345678\?text=([^"]+)#', $html, $match);
        $this->assertNotEmpty($match, 'Reminder link for Arnob');
        $message = rawurldecode(html_entity_decode($match[1]));
        $this->assertStringContainsString('Hello Arnob', $message);
        $this->assertStringContainsString($a1->reference, $message);
        $this->assertStringContainsString('AUD 200.00', $message);
        $this->assertStringNotContainsString('wa.me/447700900111?text', $html); // Juthi is paid up
    }

    public function test_csv_export(): void
    {
        $this->seedClients();

        $csv = $this->actingAs($this->admin)->get(route('admin.dues.export', ['show' => 'due']))->assertOk()->streamedContent();

        $this->assertStringContainsString('Client,Country,WhatsApp,Email,Orders', $csv);
        $this->assertStringContainsString('Arnob,Australia,+61412345678', $csv);
        $this->assertStringContainsString('"AUD 200.00; USD 50.00"', $csv);
        $this->assertStringNotContainsString('Juthi', $csv);
    }

    public function test_overview_links_to_clients_with_dues(): void
    {
        $this->seedClients();

        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertViewHas('metrics', fn ($m) => $m['clients_with_dues'] === 1)
            ->assertSee(route('admin.dues', ['show' => 'due']), false);
    }

    public function test_completed_orders_count_but_do_not_hide_dues(): void
    {
        Http::preventStrayRequests();
        $client = User::factory()->create(['name' => 'Rafi']);
        $this->order($client, 'BDT', '5000', ['2000'], SubmissionStatus::Closed->value);

        $row = $this->actingAs($this->admin)->get(route('admin.dues'))->viewData('rows')->getCollection()->first();

        $this->assertSame(1, $row['completed']);
        $this->assertSame(['BDT' => 3000.0], $row['due']);
        $this->assertSame(3000.0, $row['due_bdt']);
    }

    public function test_students_cannot_open_dues(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)->get(route('admin.dues'))->assertForbidden();
        $this->actingAs($student)->get(route('admin.dues.export'))->assertForbidden();
        auth()->logout();
        $this->get(route('admin.dues'))->assertRedirect(route('login'));
    }

    public function test_country_falls_back_to_latest_order(): void
    {
        Http::preventStrayRequests();
        $client = User::factory()->create(['name' => 'Portal Student', 'country' => null]);
        Submission::factory()->for($client, 'student')->create(['country' => 'United Kingdom']);

        $response = $this->actingAs($this->admin)->get(route('admin.dues', ['country' => 'United Kingdom']));

        $this->assertSame(['Portal Student'], $response->viewData('rows')->getCollection()->map(fn ($r) => $r['client']->name)->all());
        $this->assertSame('United Kingdom', $response->viewData('rows')->getCollection()->first()['country']);
    }
}
