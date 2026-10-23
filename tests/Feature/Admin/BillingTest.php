<?php

namespace Tests\Feature\Admin;

use App\Enums\PaymentStatus;
use App\Enums\SubmissionStatus;
use App\Mail\PaymentReceived;
use App\Mail\QuotationIssued;
use App\Models\NotificationLog;
use App\Models\Payment;
use App\Models\Submission;
use App\Models\User;
use App\Services\PdfDocumentService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function fakeRates(float $audToBdt = 80.0): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'cdn.jsdelivr.net/*' => Http::response(['date' => '2026-10-01', 'aud' => ['bdt' => $audToBdt], 'gbp' => ['bdt' => 160.0]]),
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function sendQuote(User $admin, Submission $submission, array $overrides = []): TestResponse
    {
        return $this->actingAs($admin)->put(route('admin.submissions.quotation', $submission), array_merge([
            'currency' => 'AUD', 'total_amount' => '200', 'deposit_amount' => '80', 'work_due_date' => now()->addWeek()->toDateString(),
            'country' => 'Australia', 'message' => 'Pay by bank transfer to the details on WhatsApp.', 'action' => 'send',
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function recordPayment(User $admin, Submission $submission, array $overrides = []): TestResponse
    {
        return $this->actingAs($admin)->post(route('admin.submissions.payments.store', $submission), array_merge([
            'type' => 'payment', 'amount' => '80', 'paid_on' => now()->toDateString(), 'method' => 'bank_transfer', 'notify_student' => '1',
        ], $overrides));
    }

    public function test_sending_quote_emails_student_and_updates_statuses(): void
    {
        Mail::fake();
        $submission = Submission::factory()->create();

        $this->sendQuote($this->admin(), $submission)->assertSessionHas('status');

        $submission->refresh();
        $this->assertSame(SubmissionStatus::QuotationSent, $submission->status);
        $this->assertSame(PaymentStatus::AwaitingPayment, $submission->payment_status);
        $this->assertSame('200.00', $submission->quotation->total_amount);
        Mail::assertSent(QuotationIssued::class, fn (QuotationIssued $mail) => $mail->hasTo($submission->contact_email));
        $this->assertDatabaseHas('notification_logs', ['type' => NotificationLog::TYPE_QUOTATION_SENT, 'recipient' => $submission->contact_email, 'status' => 'sent']);
    }

    public function test_draft_quote_is_hidden_from_student_and_not_emailed(): void
    {
        Mail::fake();
        $submission = Submission::factory()->create();

        $this->sendQuote($this->admin(), $submission, ['action' => 'save']);

        Mail::assertNothingSent();
        $this->assertSame(PaymentStatus::NotQuoted, $submission->fresh()->payment_status);
        $this->actingAs($submission->student)->get(route('student.submissions.show', $submission->reference))
            ->assertOk()->assertDontSee('Your quotation')->assertDontSee('AUD 200.00');
    }

    public function test_quote_validation(): void
    {
        $submission = Submission::factory()->create();
        $admin = $this->admin();

        $this->sendQuote($admin, $submission, ['deposit_amount' => '300'])->assertSessionHasErrors(['deposit_amount' => 'The deposit cannot be more than the total price.']);
        $this->sendQuote($admin, $submission, ['currency' => 'EUR'])->assertSessionHasErrors('currency');
        $this->sendQuote($admin, $submission, ['total_amount' => '0'])->assertSessionHasErrors('total_amount');
        $this->sendQuote($admin, $submission, ['total_amount' => '10.123'])->assertSessionHasErrors('total_amount');
        $this->assertNull($submission->fresh()->quotation);
    }

    public function test_student_sees_highlighted_quote_and_balance(): void
    {
        Mail::fake();
        $this->fakeRates();
        $submission = Submission::factory()->create(['assignment_name' => 'Marketing plan']);
        $admin = $this->admin();
        $this->sendQuote($admin, $submission);
        $this->recordPayment($admin, $submission, ['amount' => '50']);

        $this->actingAs($submission->student)->get(route('student.submissions.show', $submission->reference))
            ->assertOk()
            ->assertSee('Your quotation')
            ->assertSee('AUD 200.00')
            ->assertSee('AUD 80.00')
            ->assertSee('AUD 50.00')
            ->assertSee('AUD 150.00')
            ->assertSee('Pay by bank transfer to the details on WhatsApp.')
            ->assertDontSee('80.000000'); // exchange rates are internal

        $this->actingAs($submission->student)->get(route('student.dashboard'))->assertSee('AUD 150.00 due');
    }

    public function test_other_students_cannot_see_a_quote(): void
    {
        Mail::fake();
        $submission = Submission::factory()->create();
        $this->sendQuote($this->admin(), $submission);

        $this->actingAs(User::factory()->create())->get(route('student.submissions.show', $submission->reference))->assertNotFound();
    }

    public function test_payment_uses_market_rate_and_moves_work_forward(): void
    {
        Mail::fake();
        $this->fakeRates(80.5);
        $submission = Submission::factory()->create();
        $admin = $this->admin();
        $this->sendQuote($admin, $submission);

        $this->recordPayment($admin, $submission, ['amount' => '80', 'paid_on' => '2026-10-01', 'reference' => 'TXN-1'])->assertSessionHas('status');

        $payment = Payment::sole();
        $this->assertSame('80.500000', $payment->rate_to_bdt);
        $this->assertSame('6440.00', $payment->amount_bdt);
        $this->assertSame('auto', $payment->rate_source);
        $this->assertSame('Australia', $payment->country);
        $this->assertSame($submission->reference, $payment->submission_reference);
        $submission->refresh();
        $this->assertSame(PaymentStatus::DepositPaid, $submission->payment_status);
        $this->assertSame(SubmissionStatus::InProgress, $submission->status);
        $this->assertDatabaseHas('submission_status_changes', ['submission_id' => $submission->id, 'to_status' => 'in_progress', 'admin_id' => $admin->id]);
        Mail::assertSent(PaymentReceived::class, fn (PaymentReceived $mail) => $mail->hasTo($submission->contact_email));
        Http::assertSent(fn ($request) => str_contains($request->url(), '@2026-10-01/v1/currencies/aud.json'));
    }

    public function test_manual_rate_overrides_market_rate(): void
    {
        Mail::fake();
        Http::preventStrayRequests();
        $submission = Submission::factory()->create();
        $admin = $this->admin();
        $this->sendQuote($admin, $submission);

        $this->recordPayment($admin, $submission, ['amount' => '100', 'rate_to_bdt' => '79.25']);

        $payment = Payment::sole();
        $this->assertSame('7925.00', $payment->amount_bdt);
        $this->assertSame('manual', $payment->rate_source);
        $this->assertSame(PaymentStatus::DepositPaid, $submission->fresh()->payment_status);
    }

    public function test_rate_service_failure_asks_for_manual_rate(): void
    {
        Mail::fake();
        Http::preventStrayRequests();
        Http::fake(['*' => Http::response('', 500)]);
        $submission = Submission::factory()->create();
        $admin = $this->admin();
        $this->sendQuote($admin, $submission);

        $this->recordPayment($admin, $submission)->assertSessionHasErrors('rate_to_bdt');
        $this->assertSame(0, Payment::count());
    }

    public function test_bdt_quotes_need_no_rate_lookup(): void
    {
        Mail::fake();
        Http::preventStrayRequests();
        $submission = Submission::factory()->create();
        $admin = $this->admin();
        $this->sendQuote($admin, $submission, ['currency' => 'BDT', 'total_amount' => '15000', 'deposit_amount' => '5000']);

        $this->recordPayment($admin, $submission, ['amount' => '15000']);

        $this->assertSame('15000.00', Payment::sole()->amount_bdt);
        $this->assertSame(PaymentStatus::PaidInFull, $submission->fresh()->payment_status);
    }

    public function test_payment_status_progression_and_completion_gate(): void
    {
        Mail::fake();
        $this->fakeRates();
        $submission = Submission::factory()->create();
        $admin = $this->admin();
        $this->sendQuote($admin, $submission);

        $this->recordPayment($admin, $submission, ['amount' => '30']);
        $this->assertSame(PaymentStatus::PartPaid, $submission->fresh()->payment_status);
        $this->assertSame(SubmissionStatus::QuotationSent, $submission->fresh()->status);

        $this->recordPayment($admin, $submission, ['amount' => '50']);
        $this->assertSame(PaymentStatus::DepositPaid, $submission->fresh()->payment_status);

        $this->actingAs($admin)->put(route('admin.submissions.status', $submission), ['status' => 'completed'])
            ->assertSessionHas('error', 'This request still has AUD 120.00 outstanding. Record the payment before marking it completed.');
        $this->assertNotSame(SubmissionStatus::Completed, $submission->fresh()->status);

        $this->recordPayment($admin, $submission, ['amount' => '120']);
        $this->assertSame(PaymentStatus::PaidInFull, $submission->fresh()->payment_status);

        $this->actingAs($admin)->put(route('admin.submissions.status', $submission), ['status' => 'completed'])->assertSessionHas('status');
        $this->assertSame(SubmissionStatus::Completed, $submission->fresh()->status);
    }

    public function test_refunds_reduce_paid_amount_and_cannot_exceed_it(): void
    {
        Mail::fake();
        $this->fakeRates();
        $submission = Submission::factory()->create();
        $admin = $this->admin();
        $this->sendQuote($admin, $submission);
        $this->recordPayment($admin, $submission, ['amount' => '200']);

        $this->recordPayment($admin, $submission, ['type' => 'refund', 'amount' => '250'])->assertSessionHasErrors('amount');
        $this->recordPayment($admin, $submission, ['type' => 'refund', 'amount' => '50']);

        $this->assertSame(150.0, $submission->fresh()->quotation->amountPaid());
        $this->assertSame(PaymentStatus::DepositPaid, $submission->fresh()->payment_status);
        Mail::assertSentCount(2); // quotation + one receipt (refunds send no receipt)
    }

    public function test_payment_requires_a_sent_quote_and_valid_input(): void
    {
        $submission = Submission::factory()->create();
        $admin = $this->admin();

        $this->recordPayment($admin, $submission)->assertSessionHas('error', 'Send a quotation before recording payments.');

        Mail::fake();
        $this->sendQuote($admin, $submission);
        $this->recordPayment($admin, $submission, ['amount' => '-5'])->assertSessionHasErrors('amount');
        $this->recordPayment($admin, $submission, ['paid_on' => now()->addDay()->toDateString()])->assertSessionHasErrors('paid_on');
        $this->recordPayment($admin, $submission, ['method' => 'crypto'])->assertSessionHasErrors('method');
    }

    public function test_currency_is_locked_once_payments_exist(): void
    {
        Mail::fake();
        $this->fakeRates();
        $submission = Submission::factory()->create();
        $admin = $this->admin();
        $this->sendQuote($admin, $submission);
        $this->recordPayment($admin, $submission);

        $this->sendQuote($admin, $submission, ['currency' => 'GBP'])->assertSessionHasErrors('currency');
        $this->assertSame('AUD', $submission->fresh()->quotation->currency->value);
    }

    public function test_deleting_a_payment_recalculates_and_is_audited(): void
    {
        Mail::fake();
        $this->fakeRates();
        $submission = Submission::factory()->create();
        $admin = $this->admin();
        $this->sendQuote($admin, $submission);
        $this->recordPayment($admin, $submission, ['amount' => '200']);
        $payment = Payment::sole();

        $this->actingAs($admin)->delete(route('admin.submissions.payments.destroy', [$submission, $payment]))->assertSessionHas('status');

        $this->assertModelMissing($payment);
        $this->assertSame(PaymentStatus::AwaitingPayment, $submission->fresh()->payment_status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'payment.deleted', 'actor_id' => $admin->id]);
    }

    public function test_payment_must_belong_to_submission_in_url(): void
    {
        Mail::fake();
        $this->fakeRates();
        $admin = $this->admin();
        [$a, $b] = Submission::factory(2)->create();
        $this->sendQuote($admin, $b);
        $this->recordPayment($admin, $b);

        $this->actingAs($admin)->delete(route('admin.submissions.payments.destroy', [$a, Payment::sole()]))->assertNotFound();
        $this->assertSame(1, Payment::count());
    }

    public function test_payment_records_survive_submission_deletion(): void
    {
        Mail::fake();
        $this->fakeRates();
        $submission = Submission::factory()->create();
        $admin = $this->admin();
        $this->sendQuote($admin, $submission);
        $this->recordPayment($admin, $submission);

        $submission->delete();

        $payment = Payment::sole();
        $this->assertNull($payment->submission_id);
        $this->assertSame($submission->reference, $payment->submission_reference);
    }

    public function test_emails_escape_markdown_in_quote_message(): void
    {
        Mail::fake();
        $submission = Submission::factory()->create();
        $this->sendQuote($this->admin(), $submission, ['message' => '[Pay here](https://evil.example)']);

        $html = (new QuotationIssued($submission->fresh()->load('quotation', 'student')))->render();

        $this->assertStringNotContainsString('href="https://evil.example', $html);
        $this->assertStringContainsString('AUD 200.00', $html);
    }

    public function test_students_cannot_reach_billing_endpoints(): void
    {
        $submission = Submission::factory()->create();
        $student = $submission->student;

        $this->actingAs($student)->put(route('admin.submissions.quotation', $submission), ['currency' => 'AUD', 'total_amount' => 1, 'deposit_amount' => 0, 'action' => 'send'])->assertForbidden();
        $this->actingAs($student)->post(route('admin.submissions.payments.store', $submission), [])->assertForbidden();
        $this->actingAs($student)->get(route('admin.accounting'))->assertForbidden();
        $this->actingAs($student)->get(route('admin.accounting.export'))->assertForbidden();
        $this->assertNull($submission->fresh()->quotation);
    }

    public function test_payment_receipt_email_shows_balance_and_cleared_message(): void
    {
        Mail::fake();
        $this->fakeRates();
        $submission = Submission::factory()->create(['assignment_name' => 'Finance report']);
        $admin = $this->admin();
        $this->sendQuote($admin, $submission);
        $this->recordPayment($admin, $submission, ['amount' => '80']);

        $fresh = $submission->fresh()->load('quotation.payments', 'student');
        $partial = new PaymentReceived($fresh, Payment::sole());
        $partial->assertHasSubject('Payment received for '.$submission->reference.' — AUD 80.00');
        $partial->assertSeeInHtml('AUD 200.00');
        $partial->assertSeeInHtml('AUD 120.00');
        $partial->assertSeeInHtml('The remaining balance is due before your work is completed.');

        $this->recordPayment($admin, $submission, ['amount' => '120']);
        $cleared = new PaymentReceived($submission->fresh()->load('quotation.payments', 'student'), Payment::latest('id')->first());
        $cleared->assertSeeInHtml('Your balance is fully cleared.');
    }

    public function test_payment_reference_is_prominent_in_email_pdf_and_portal(): void
    {
        Mail::fake();
        $submission = Submission::factory()->create(['assignment_name' => 'Brand plan']);
        $this->sendQuote($this->admin(), $submission);
        $fresh = $submission->fresh()->load('quotation.payments', 'student');
        $reference = $submission->reference;

        $mail = new QuotationIssued($fresh);
        $mail->assertHasSubject('Your quotation '.$reference.' — Brand plan — AUD 200.00');
        $mail->assertSeeInHtml('Your payment reference: '.$reference);
        $mail->assertSeeInText('Write '.$reference.' in the reference or note field when you pay by bKash');
        $mail->assertSeeInText('When you send a payment screenshot by email or WhatsApp, mention '.$reference);

        $pdfHtml = app(PdfDocumentService::class)->html('documents.quotation', ['quotation' => $fresh->quotation]);
        $this->assertStringContainsString('Payment reference', $pdfHtml);
        $this->assertStringContainsString('payment screenshot by email or WhatsApp, mention '.$reference, strip_tags($pdfHtml));
        $this->assertStringContainsString('white-space: nowrap;">'.$reference, $pdfHtml);

        $this->actingAs($submission->student)->get(route('student.submissions.show', $reference))
            ->assertSee('Your payment reference')
            ->assertSee('in the reference or note field when you pay by bKash', false);

        $this->actingAs(User::where('role', 'admin')->first())->get(route('admin.submissions.show', $submission))
            ->assertSee('Students are asked to quote', false);
    }

    public function test_reference_block_is_hidden_once_paid_in_full(): void
    {
        Mail::fake();
        $this->fakeRates();
        $submission = Submission::factory()->create();
        $admin = $this->admin();
        $this->sendQuote($admin, $submission);
        $this->recordPayment($admin, $submission, ['amount' => '200']);

        $this->actingAs($submission->student)->get(route('student.submissions.show', $submission->reference))
            ->assertDontSee('Your payment reference');
    }
}
