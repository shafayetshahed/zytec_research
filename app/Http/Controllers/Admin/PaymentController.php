<?php

namespace App\Http\Controllers\Admin;

use App\Enums\NotificationStatus;
use App\Enums\PaymentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePaymentRequest;
use App\Jobs\SendBusinessNotification;
use App\Models\NotificationLog;
use App\Models\Payment;
use App\Models\Submission;
use App\Services\AuditLogger;
use App\Services\BillingService;
use App\Services\DocumentNumberService;
use App\Services\ExchangeRateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function store(StorePaymentRequest $request, Submission $submission, ExchangeRateService $rates, BillingService $billing, DocumentNumberService $numbers, AuditLogger $audit): RedirectResponse
    {
        $quotation = $submission->quotation;

        if ($quotation === null || $quotation->sent_at === null) {
            return back()->with('error', 'Send a quotation before recording payments.');
        }

        $type = PaymentType::from($request->validated('type'));
        $amount = round((float) $request->validated('amount'), 2);

        if ($type === PaymentType::Refund && $amount > $quotation->amountPaid()) {
            return back()->withInput()->withErrors(['amount' => 'A refund cannot be more than the amount received.']);
        }

        $paidOn = Carbon::parse($request->validated('paid_on'));
        $manualRate = $request->validated('rate_to_bdt');
        $rate = $manualRate !== null ? (float) $manualRate : $rates->rateToBdt($quotation->currency, $paidOn);

        if ($rate === null) {
            return back()->withInput()->withErrors(['rate_to_bdt' => 'The exchange rate could not be fetched. Enter the rate you received (BDT per 1 '.$quotation->currency->value.').']);
        }

        $payment = DB::transaction(function () use ($request, $submission, $quotation, $type, $amount, $paidOn, $rate, $manualRate, $billing, $numbers) {
            $payment = new Payment;
            $payment->submission_id = $submission->id;
            $payment->quotation_id = $quotation->id;
            $payment->submission_reference = $submission->reference;
            $payment->country = $quotation->country ?? $submission->country;
            $payment->type = $type;
            $payment->currency = $quotation->currency;
            $payment->amount = number_format($amount, 2, '.', '');
            $payment->paid_on = $paidOn;
            $payment->method = $request->validated('method');
            $payment->reference = $request->validated('reference');
            $payment->rate_to_bdt = number_format($rate, 6, '.', '');
            $payment->amount_bdt = number_format(round($amount * $rate, 2), 2, '.', '');
            $payment->rate_source = $manualRate !== null ? 'manual' : 'auto';
            $payment->note = $request->validated('note');
            $payment->recorded_by = $request->user()->id;
            $payment->receipt_number = $type === PaymentType::Payment ? $numbers->next(DocumentNumberService::RECEIPT) : null;
            $payment->save();

            $billing->refresh($submission, $request->user()->id);

            return $payment;
        });

        $audit->record('payment.recorded', $payment, [
            'submission' => $submission->reference,
            'type' => $type->value,
            'amount' => $payment->amount,
            'currency' => $payment->currency->value,
        ]);

        if ($type === PaymentType::Payment && $request->boolean('notify_student', true)) {
            $log = new NotificationLog;
            $log->submission_id = $submission->id;
            $log->payment_id = $payment->id;
            $log->type = NotificationLog::TYPE_PAYMENT_RECEIVED;
            $log->recipient = $submission->contact_email;
            $log->status = NotificationStatus::Pending;
            $log->save();

            SendBusinessNotification::dispatch($log->id);
        }

        return back()->with('status', $type->label().' of '.$payment->currency->format($payment->amount).' recorded ('.$submission->fresh()->payment_status->label().').');
    }

    public function destroy(Submission $submission, Payment $payment, BillingService $billing, AuditLogger $audit): RedirectResponse
    {
        abort_unless($payment->submission_id === $submission->id, 404);

        $details = ['submission' => $submission->reference, 'type' => $payment->type->value, 'amount' => $payment->amount, 'currency' => $payment->currency->value, 'paid_on' => $payment->paid_on->toDateString()];

        DB::transaction(function () use ($payment, $submission, $billing) {
            $payment->delete();
            $billing->refresh($submission, request()->user()?->id);
        });

        $audit->record('payment.deleted', $submission, $details);

        return back()->with('status', 'Payment record deleted.');
    }
}
