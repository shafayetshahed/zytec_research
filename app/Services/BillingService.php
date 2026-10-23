<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\SubmissionStatus;
use App\Models\Submission;
use App\Models\SubmissionStatusChange;

/**
 * Keeps a submission's payment status in step with its quotation and the
 * payments recorded against it, and moves work forward when the deposit
 * has been received.
 */
class BillingService
{
    public function __construct(private DocumentNumberService $numbers) {}

    /** Work statuses that move to "In progress" once the deposit is paid. */
    private const PRE_WORK = [
        SubmissionStatus::New,
        SubmissionStatus::UnderReview,
        SubmissionStatus::AwaitingStudentResponse,
        SubmissionStatus::QuotationSent,
        SubmissionStatus::Accepted,
    ];

    public function refresh(Submission $submission, ?int $adminId = null): PaymentStatus
    {
        $submission->load('quotation.payments');
        $quotation = $submission->quotation;

        if ($quotation === null || $quotation->sent_at === null) {
            $status = PaymentStatus::NotQuoted;
        } else {
            $paid = $quotation->amountPaid();
            $status = match (true) {
                $paid >= (float) $quotation->total_amount => PaymentStatus::PaidInFull,
                $paid > 0 && $paid >= (float) $quotation->deposit_amount => PaymentStatus::DepositPaid,
                $paid > 0 => PaymentStatus::PartPaid,
                default => PaymentStatus::AwaitingPayment,
            };
        }

        $submission->payment_status = $status;

        // The final invoice is issued once, the first time the balance is cleared.
        if ($status === PaymentStatus::PaidInFull && $quotation->invoice_number === null) {
            $quotation->invoice_number = $this->numbers->next(DocumentNumberService::INVOICE);
            $quotation->invoiced_at = now();
            $quotation->save();
        }

        if (in_array($status, [PaymentStatus::DepositPaid, PaymentStatus::PaidInFull], true)
            && in_array($submission->status, self::PRE_WORK, true)) {
            $this->changeStatus($submission, SubmissionStatus::InProgress, $adminId);
        }

        $submission->save();

        return $status;
    }

    public function changeStatus(Submission $submission, SubmissionStatus $to, ?int $adminId): void
    {
        if ($submission->status === $to) {
            return;
        }

        $change = new SubmissionStatusChange;
        $change->submission_id = $submission->id;
        $change->admin_id = $adminId;
        $change->from_status = $submission->status;
        $change->to_status = $to;
        $change->save();

        $submission->status = $to;
        $submission->closed_at = $to->isFinal() ? now() : null;
        $submission->save();
    }
}
