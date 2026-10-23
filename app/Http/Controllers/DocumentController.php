<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Submission;
use App\Services\AuditLogger;
use App\Services\PdfDocumentService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves quotation, receipt and invoice PDFs. Students reach a submission
 * only through their own records (404 otherwise); administrators reach any.
 * Drafts, refunds and unpaid invoices are never available.
 */
class DocumentController extends Controller
{
    public function __construct(private PdfDocumentService $pdf, private AuditLogger $audit) {}

    public function quotation(Request $request, string $reference): Response
    {
        $submission = $this->resolve($request, $reference);
        $quotation = $submission->quotation;
        abort_if($quotation === null || $quotation->sent_at === null || $quotation->number === null, 404);

        return $this->download($submission, $quotation->number, $this->pdf->quotation($quotation));
    }

    public function receipt(Request $request, string $reference, int $payment): Response
    {
        $submission = $this->resolve($request, $reference);
        $quotation = $submission->quotation;
        $record = $quotation?->payments->firstWhere('id', $payment);
        abort_if($quotation === null || $record === null || $record->type !== PaymentType::Payment || $record->receipt_number === null, 404);

        return $this->download($submission, $record->receipt_number, $this->pdf->receipt($record, $quotation));
    }

    public function invoice(Request $request, string $reference): Response
    {
        $submission = $this->resolve($request, $reference);
        $quotation = $submission->quotation;
        abort_if($quotation === null || $quotation->invoice_number === null || $submission->payment_status !== PaymentStatus::PaidInFull, 404);

        return $this->download($submission, $quotation->invoice_number, $this->pdf->invoice($quotation));
    }

    private function resolve(Request $request, string $reference): Submission
    {
        $query = $request->user()->isAdmin() ? Submission::query() : $request->user()->submissions();

        return $query->where('reference', $reference)->with(['student', 'quotation.payments', 'quotation.submission.student'])->firstOrFail();
    }

    private function download(Submission $submission, string $number, string $pdf): Response
    {
        $this->audit->record('document.downloaded', $submission, ['document' => $number]);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$number.'.pdf"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
