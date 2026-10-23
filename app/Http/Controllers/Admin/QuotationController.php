<?php

namespace App\Http\Controllers\Admin;

use App\Enums\NotificationStatus;
use App\Enums\SubmissionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveQuotationRequest;
use App\Jobs\SendBusinessNotification;
use App\Models\NotificationLog;
use App\Models\Quotation;
use App\Models\Submission;
use App\Services\AuditLogger;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function save(SaveQuotationRequest $request, Submission $submission, BillingService $billing, AuditLogger $audit): RedirectResponse
    {
        $send = $request->validated('action') === 'send';
        $quotation = $submission->quotation ?? new Quotation;

        if ($quotation->exists && $quotation->payments()->exists() && $quotation->currency->value !== $request->validated('currency')) {
            return back()->withInput()->withErrors(['currency' => 'The currency cannot be changed after payments have been recorded.']);
        }

        DB::transaction(function () use ($request, $submission, $quotation, $send, $billing) {
            $quotation->fill($request->safe()->except('action'));
            $quotation->submission_id = $submission->id;
            $quotation->created_by ??= $request->user()->id;

            if ($send) {
                $quotation->sent_at = now();
            }

            $quotation->save();
            $submission->setRelation('quotation', $quotation);

            if ($send && in_array($submission->status, [SubmissionStatus::New, SubmissionStatus::UnderReview, SubmissionStatus::AwaitingStudentResponse], true)) {
                $billing->changeStatus($submission, SubmissionStatus::QuotationSent, $request->user()->id);
            }

            $billing->refresh($submission, $request->user()->id);
        });

        $audit->record($send ? 'quotation.sent' : 'quotation.saved', $submission, [
            'currency' => $quotation->currency->value,
            'total' => $quotation->total_amount,
            'deposit' => $quotation->deposit_amount,
        ]);

        if (! $send) {
            return back()->with('status', 'Quotation saved as a draft. The student cannot see it until you send it.');
        }

        $this->notifyStudent($submission);

        return back()->with('status', 'Quotation sent. The student has been emailed and can see it in their portal.');
    }

    private function notifyStudent(Submission $submission): void
    {
        $log = new NotificationLog;
        $log->submission_id = $submission->id;
        $log->type = NotificationLog::TYPE_QUOTATION_SENT;
        $log->recipient = $submission->contact_email;
        $log->status = NotificationStatus::Pending;
        $log->save();

        SendBusinessNotification::dispatch($log->id);
    }
}
