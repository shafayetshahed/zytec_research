<?php

namespace Tests\Feature\Admin;

use App\Enums\PaymentStatus;
use App\Mail\PaymentReceived;
use App\Mail\QuotationIssued;
use App\Models\Payment;
use App\Models\Submission;
use App\Models\User;
use App\Services\DocumentNumberService;
use App\Services\PdfDocumentService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DocumentsTest extends TestCase
{
    use LazilyRefreshDatabase;

    private User $admin;

    private function quoteAndPay(Submission $submission, array $payments = []): void
    {
        $this->admin ??= User::factory()->admin()->create();
        $this->actingAs($this->admin)->put(route('admin.submissions.quotation', $submission), [
            'currency' => 'GBP', 'total_amount' => '150', 'deposit_amount' => '50', 'country' => 'United Kingdom',
            'message' => 'Pay by Wise.', 'action' => 'send',
        ]);

        foreach ($payments as $amount) {
            $this->actingAs($this->admin)->post(route('admin.submissions.payments.store', $submission), [
                'type' => 'payment', 'amount' => $amount, 'paid_on' => now()->toDateString(), 'method' => 'wise', 'rate_to_bdt' => '160',
            ]);
        }
    }

    public function test_numbers_are_sequential_per_type_and_year(): void
    {
        $this->travelTo('2026-03-01');
        $numbers = app(DocumentNumberService::class);

        $this->assertSame('INV-2026-0001', $numbers->next('INV'));
        $this->assertSame('INV-2026-0002', $numbers->next('INV'));
        $this->assertSame('RCP-2026-0001', $numbers->next('RCP'));

        $this->travelTo('2027-01-02');
        $this->assertSame('INV-2027-0001', $numbers->next('INV'));
    }

    public function test_quote_receipts_and_final_invoice_get_numbers_at_the_right_time(): void
    {
        Mail::fake();
        $this->travelTo('2026-10-20');
        $submission = Submission::factory()->create();

        $this->quoteAndPay($submission, ['50']);
        $quotation = $submission->fresh()->quotation;
        $this->assertSame('QUO-2026-0001', $quotation->number);
        $this->assertNull($quotation->invoice_number);
        $this->assertSame('RCP-2026-0001', Payment::sole()->receipt_number);

        $this->quoteAndPay($submission, ['100']); // re-send keeps the same quote number
        $quotation->refresh();
        $this->assertSame('QUO-2026-0001', $quotation->number);
        $this->assertSame('INV-2026-0001', $quotation->invoice_number);
        $this->assertSame(['RCP-2026-0001', 'RCP-2026-0002'], Payment::orderBy('id')->pluck('receipt_number')->all());
    }

    public function test_refunds_get_no_receipt_number(): void
    {
        Mail::fake();
        $submission = Submission::factory()->create();
        $this->quoteAndPay($submission, ['150']);

        $this->actingAs($this->admin)->post(route('admin.submissions.payments.store', $submission), [
            'type' => 'refund', 'amount' => '20', 'paid_on' => now()->toDateString(), 'method' => 'wise', 'rate_to_bdt' => '160',
        ]);

        $this->assertNull(Payment::where('type', 'refund')->sole()->receipt_number);
    }

    public function test_student_downloads_own_pdfs(): void
    {
        Mail::fake();
        $submission = Submission::factory()->create();
        $this->quoteAndPay($submission, ['50', '100']);
        $student = $submission->student;
        $payment = Payment::orderBy('id')->first();
        $quotation = $submission->fresh()->quotation;

        foreach ([
            [route('student.documents.quotation', $submission->reference), $quotation->number],
            [route('student.documents.receipt', [$submission->reference, $payment->id]), $payment->receipt_number],
            [route('student.documents.invoice', $submission->reference), $quotation->invoice_number],
        ] as [$url, $number]) {
            $response = $this->actingAs($student)->get($url);
            $response->assertOk()->assertHeader('Content-Type', 'application/pdf')->assertDownload($number.'.pdf');
            $this->assertStringStartsWith('%PDF-', $response->getContent());
        }

        $this->assertDatabaseHas('audit_logs', ['action' => 'document.downloaded', 'actor_id' => $student->id]);
        $this->actingAs($student)->get(route('student.submissions.show', $submission->reference))
            ->assertSee('Quotation PDF')->assertSee('Final invoice (paid)')->assertSee($payment->receipt_number);
    }

    public function test_admin_downloads_any_pdf(): void
    {
        Mail::fake();
        $submission = Submission::factory()->create();
        $this->quoteAndPay($submission, ['150']);

        $this->actingAs($this->admin)->get(route('admin.documents.quotation', $submission->reference))->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->actingAs($this->admin)->get(route('admin.documents.invoice', $submission->reference))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.submissions.show', $submission))->assertSee('Invoice INV-')->assertSee('Receipt');
    }

    public function test_documents_are_not_available_when_they_should_not_be(): void
    {
        Mail::fake();
        $submission = Submission::factory()->create();
        $student = $submission->student;
        $other = User::factory()->create();

        // No quote yet
        $this->actingAs($student)->get(route('student.documents.quotation', $submission->reference))->assertNotFound();

        // Draft quote
        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin)->put(route('admin.submissions.quotation', $submission), ['currency' => 'GBP', 'total_amount' => '150', 'deposit_amount' => '50', 'action' => 'save']);
        $this->actingAs($student)->get(route('student.documents.quotation', $submission->reference))->assertNotFound();

        // Sent and part paid: no invoice yet
        $this->quoteAndPay($submission, ['50']);
        $this->actingAs($student)->get(route('student.documents.invoice', $submission->reference))->assertNotFound();

        // Another student cannot reach anything
        $payment = Payment::sole();
        $this->actingAs($other)->get(route('student.documents.quotation', $submission->reference))->assertNotFound();
        $this->actingAs($other)->get(route('student.documents.receipt', [$submission->reference, $payment->id]))->assertNotFound();

        // A receipt ID from another submission is refused
        $second = Submission::factory()->for($student, 'student')->create();
        $this->actingAs($student)->get(route('student.documents.receipt', [$second->reference, $payment->id]))->assertNotFound();

        // Guests are sent to sign in; students cannot use admin document routes
        auth()->logout();
        $this->get(route('student.documents.quotation', $submission->reference))->assertRedirect(route('login'));
        $this->actingAs($student)->get(route('admin.documents.quotation', $submission->reference))->assertForbidden();
    }

    public function test_invoice_disappears_if_a_payment_is_deleted(): void
    {
        Mail::fake();
        $submission = Submission::factory()->create();
        $this->quoteAndPay($submission, ['150']);

        $this->actingAs($this->admin)->delete(route('admin.submissions.payments.destroy', [$submission, Payment::sole()]));

        $this->actingAs($submission->student)->get(route('student.documents.invoice', $submission->reference))->assertNotFound();
    }

    public function test_document_content(): void
    {
        Mail::fake();
        $student = User::factory()->create(['name' => 'Ayesha <b>Khan</b>']);
        $submission = Submission::factory()->for($student, 'student')->create(['assignment_name' => 'Brand audit', 'module_code' => 'MAR036-6']);
        $this->quoteAndPay($submission, ['50', '100']);
        $quotation = $submission->fresh()->load('quotation.payments', 'student')->quotation;
        $pdf = app(PdfDocumentService::class);

        $quote = $pdf->html('documents.quotation', ['quotation' => $quotation]);
        foreach ([$quotation->number, 'Ayesha &lt;b&gt;Khan&lt;/b&gt;', $submission->contact_email, 'Brand audit', 'MAR036-6', 'GBP 150.00', 'GBP 50.00', 'Pay by Wise.', 'United Kingdom'] as $expected) {
            $this->assertStringContainsString($expected, $quote);
        }
        $this->assertStringNotContainsString('<b>Khan</b>', $quote);
        $this->assertStringNotContainsString($submission->whatsapp_number, $quote);

        $first = $quotation->payments->first();
        $receipt = $pdf->html('documents.receipt', ['payment' => $first, 'quotation' => $quotation]);
        $this->assertStringContainsString($first->receipt_number, $receipt);
        $this->assertStringContainsString('GBP 100.00', $receipt); // balance remaining after the first payment
        $this->assertStringContainsString('Your deposit has been received', $receipt);
        $this->assertStringNotContainsString('160', $receipt); // exchange rate is internal

        $invoice = $pdf->html('documents.invoice', ['quotation' => $quotation]);
        $this->assertStringContainsString($quotation->invoice_number, $invoice);
        $this->assertStringContainsString('PAID', $invoice);
        $this->assertStringContainsString('GBP 0.00', $invoice);
    }

    public function test_emails_carry_pdf_attachments(): void
    {
        Mail::fake();
        $submission = Submission::factory()->create();
        $this->quoteAndPay($submission, ['50', '100']);
        $fresh = $submission->fresh()->load('quotation.payments', 'student');

        // PDFs embed a creation time, so compare names and types rather than bytes.
        $names = fn ($mail) => collect($mail->attachments())->map(fn ($attachment) => [$attachment->as, $attachment->mime])->all();

        $this->assertSame([[$fresh->quotation->number.'.pdf', 'application/pdf']], $names(new QuotationIssued($fresh)));

        [$first, $last] = [$fresh->quotation->payments->first(), $fresh->quotation->payments->last()];
        $this->assertSame([
            [$last->receipt_number.'.pdf', 'application/pdf'],
            [$fresh->quotation->invoice_number.'.pdf', 'application/pdf'],
        ], $names(new PaymentReceived($fresh, $last)));

        $fresh->payment_status = PaymentStatus::DepositPaid;
        $this->assertSame([[$first->receipt_number.'.pdf', 'application/pdf']], $names(new PaymentReceived($fresh, $first)));

        // The attached data really is a PDF.
        $data = (new QuotationIssued($fresh))->attachments()[0]->attachWith(fn () => null, fn ($data) => $data());
        $this->assertStringStartsWith('%PDF-', $data);
    }
}
