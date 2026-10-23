<?php

namespace Tests\Feature\Admin;

use App\Enums\Currency;
use App\Enums\PaymentMethod;
use App\Enums\PaymentType;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AccountingTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function payment(string $date, string $currency, float $amount, float $rate, ?string $country, string $type = 'payment', ?Submission $submission = null): Payment
    {
        $submission ??= Submission::factory()->create();
        $payment = new Payment;
        $payment->submission_id = $submission->id;
        $payment->submission_reference = $submission->reference;
        $payment->country = $country;
        $payment->type = PaymentType::from($type);
        $payment->currency = Currency::from($currency);
        $payment->amount = number_format($amount, 2, '.', '');
        $payment->paid_on = $date;
        $payment->method = PaymentMethod::BankTransfer;
        $payment->rate_to_bdt = number_format($rate, 6, '.', '');
        $payment->amount_bdt = number_format($amount * $rate, 2, '.', '');
        $payment->rate_source = 'manual';
        $payment->save();

        return $payment;
    }

    private function seedLedger(): void
    {
        $this->travelTo('2026-10-20 12:00:00');
        $this->payment('2026-10-05', 'AUD', 100, 80, 'Australia');           // 8,000
        $this->payment('2026-10-12', 'GBP', 50, 160, 'United Kingdom');      // 8,000
        $this->payment('2026-09-03', 'AUD', 200, 78, 'Australia');           // 15,600
        $this->payment('2026-09-15', 'BDT', 5000, 1, null);                  // 5,000
        $this->payment('2026-09-20', 'AUD', 20, 78, 'Australia', 'refund');  // -1,560
        $this->payment('2025-12-30', 'CAD', 100, 85, 'Canada');              // 8,500 (last year)
    }

    public function test_year_summary_and_breakdowns_in_bdt_and_native_currency(): void
    {
        Http::preventStrayRequests();
        $this->seedLedger();

        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.accounting', ['period' => 'this_year']))->assertOk();

        $summary = $response->viewData('summary');
        $this->assertSame(36600.0, $summary['received_bdt']);
        $this->assertSame(1560.0, $summary['refunded_bdt']);
        $this->assertSame(35040.0, $summary['net_bdt']);

        $byMonth = $response->viewData('byMonth');
        $this->assertSame(16000.0, $byMonth['2026-10']['bdt']);
        $this->assertSame(19040.0, $byMonth['2026-09']['bdt']);
        $this->assertSame(180.0, $byMonth['2026-09']['native']['AUD']);
        $this->assertArrayNotHasKey('2025-12', $byMonth->all());

        $byCountry = $response->viewData('byCountry');
        $this->assertSame(22040.0, $byCountry['Australia']['bdt']);
        $this->assertSame(280.0, $byCountry['Australia']['native']['AUD']);
        $this->assertSame(8000.0, $byCountry['United Kingdom']['bdt']);
        $this->assertSame(5000.0, $byCountry['Not specified']['bdt']);

        $byCurrency = $response->viewData('byCurrency');
        $this->assertSame(50.0, $byCurrency['GBP']['native']['GBP']);
        $response->assertSee('BDT 35,040.00')->assertSee('October 2026');
    }

    public function test_period_and_country_and_currency_filters(): void
    {
        Http::preventStrayRequests();
        $this->seedLedger();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.accounting', ['period' => 'this_month']))
            ->assertViewHas('summary', fn ($s) => $s['net_bdt'] === 16000.0);
        $this->actingAs($admin)->get(route('admin.accounting', ['period' => 'last_year']))
            ->assertViewHas('summary', fn ($s) => $s['net_bdt'] === 8500.0);
        $this->actingAs($admin)->get(route('admin.accounting', ['period' => 'all', 'country' => 'Australia']))
            ->assertViewHas('summary', fn ($s) => $s['net_bdt'] === 22040.0);
        $this->actingAs($admin)->get(route('admin.accounting', ['period' => 'all', 'currency' => 'CAD']))
            ->assertViewHas('summary', fn ($s) => $s['net_bdt'] === 8500.0);
        $this->actingAs($admin)->get(route('admin.accounting', ['period' => 'custom', 'from' => '2026-09-01', 'to' => '2026-09-30']))
            ->assertViewHas('summary', fn ($s) => $s['net_bdt'] === 19040.0);
        $this->actingAs($admin)->get(route('admin.accounting', ['period' => 'custom']))->assertSessionHasErrors(['from', 'to']);
        $this->actingAs($admin)->get(route('admin.accounting', ['currency' => 'XYZ']))->assertSessionHasErrors('currency');
    }

    public function test_outstanding_balances_are_listed_with_bdt_estimate(): void
    {
        Http::preventStrayRequests();
        Http::fake(['*' => Http::response(['date' => '2026-10-20', 'aud' => ['bdt' => 80.0]])]);
        $this->travelTo('2026-10-20 12:00:00');
        $submission = Submission::factory()->create();
        $quotation = new Quotation;
        $quotation->forceFill(['submission_id' => $submission->id, 'currency' => 'AUD', 'total_amount' => '300', 'deposit_amount' => '100', 'country' => 'Australia', 'sent_at' => now()])->save();
        $this->payment('2026-10-10', 'AUD', 100, 80, 'Australia', submission: $submission)->forceFill(['quotation_id' => $quotation->id])->save();

        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.accounting'));

        $response->assertSee($submission->reference)->assertSee('AUD 200.00');
        $this->assertSame(16000.0, $response->viewData('summary')['outstanding_bdt']);
    }

    public function test_csv_export_contains_ledger_and_neutralises_formulas(): void
    {
        $this->seedLedger();
        Payment::first()->forceFill(['reference' => '=HYPERLINK("http://evil")'])->save();

        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.accounting.export', ['period' => 'this_year']));

        $response->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $csv = $response->streamedContent();
        $this->assertStringContainsString('Date,Submission,Country,Type,Currency,Amount', $csv);
        $this->assertStringContainsString('2026-09-20', $csv);
        $this->assertStringContainsString('-1560.00', $csv);
        $this->assertStringNotContainsString('2025-12-30', $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);
    }

    public function test_dashboard_shows_this_months_income(): void
    {
        Http::preventStrayRequests();
        $this->seedLedger();

        $this->actingAs(User::factory()->admin()->create())->get(route('admin.dashboard'))
            ->assertViewHas('metrics', fn ($m) => $m['received_month_bdt'] === 16000.0);
    }
}
