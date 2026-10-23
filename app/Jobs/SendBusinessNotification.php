<?php

namespace App\Jobs;

use App\Enums\NotificationStatus;
use App\Mail\NewEnquiryReceived;
use App\Mail\NewSubmissionReceived;
use App\Mail\OrderUpdate;
use App\Mail\PaymentReceived;
use App\Mail\QuotationIssued;
use App\Models\NotificationLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Delivers a business notification and records the outcome on its
 * NotificationLog. Failures are retried with back-off and then surfaced in
 * the admin panel for a manual retry.
 */
class SendBusinessNotification implements ShouldQueue
{
    use Queueable;

    public int $tries;

    public function __construct(public int $notificationLogId, public bool $manual = false)
    {
        $this->tries = max(1, (int) config('zytecs.notifications.max_attempts'));
        $this->afterCommit();
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(): void
    {
        $log = NotificationLog::with(['submission.student', 'submission.brief', 'submission.quotation.payments', 'enquiry', 'payment'])->find($this->notificationLogId);

        if ($log === null || $log->status === NotificationStatus::Sent) {
            return;
        }

        $log->attempts++;
        $log->last_attempt_at = now();

        try {
            Mail::to($log->recipient)->send($this->mailable($log));
        } catch (Throwable $exception) {
            $maxAttempts = max(1, (int) config('zytecs.notifications.max_attempts'));
            $willRetry = ! $this->manual && $this->attempts() < $maxAttempts;

            $log->status = $willRetry ? NotificationStatus::Pending : NotificationStatus::Failed;
            $log->error_summary = 'Delivery failed ('.class_basename($exception).')'.($willRetry ? '; retrying automatically.' : '.');
            $log->save();

            Log::warning('Business notification delivery failed.', [
                'notification_log' => $log->id,
                'type' => $log->type,
                'exception' => class_basename($exception),
            ]);

            if ($willRetry) {
                $this->release($this->backoff()[$this->attempts() - 1] ?? 900);
            }

            return;
        }

        $log->status = NotificationStatus::Sent;
        $log->sent_at = now();
        $log->error_summary = null;
        $log->save();
    }

    private function mailable(NotificationLog $log): Mailable
    {
        return match ($log->type) {
            NotificationLog::TYPE_NEW_SUBMISSION => new NewSubmissionReceived($log->submission ?? throw new \RuntimeException('Submission missing.')),
            NotificationLog::TYPE_NEW_ENQUIRY => new NewEnquiryReceived($log->enquiry ?? throw new \RuntimeException('Enquiry missing.')),
            NotificationLog::TYPE_QUOTATION_SENT => new QuotationIssued($log->submission ?? throw new \RuntimeException('Submission missing.')),
            NotificationLog::TYPE_EXPERT_ASSIGNED,
            NotificationLog::TYPE_MATERIALS_UPLOADED,
            NotificationLog::TYPE_MATERIALS_READY,
            NotificationLog::TYPE_MESSAGE_TO_STUDENT,
            NotificationLog::TYPE_MESSAGE_TO_ADMIN,
            NotificationLog::TYPE_MESSAGE_TO_TEAM => new OrderUpdate($log->submission ?? throw new \RuntimeException('Submission missing.'), $log->type),
            NotificationLog::TYPE_PAYMENT_RECEIVED => new PaymentReceived(
                $log->submission ?? throw new \RuntimeException('Submission missing.'),
                $log->payment ?? throw new \RuntimeException('Payment missing.'),
            ),
            default => throw new \RuntimeException('Unknown notification type.'),
        };
    }
}
