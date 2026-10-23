<?php

namespace App\Services;

use App\Enums\NotificationStatus;
use App\Jobs\SendBusinessNotification;
use App\Models\NotificationLog;
use App\Models\Submission;
use Illuminate\Support\Facades\Cache;

/**
 * Queues order alerts with a delivery log, skipping recipients without an
 * email address. Message alerts are throttled so a burst of messages
 * produces one email per recipient every few minutes.
 */
class OrderNotifier
{
    public function send(Submission $submission, string $type, ?string $recipient, bool $throttle = false): void
    {
        if (blank($recipient)) {
            return;
        }

        if ($throttle && ! Cache::add("order-alert:{$submission->id}:{$type}:".sha1((string) $recipient), true, now()->addMinutes(10))) {
            return;
        }

        $log = new NotificationLog;
        $log->submission_id = $submission->id;
        $log->type = $type;
        $log->recipient = (string) $recipient;
        $log->status = NotificationStatus::Pending;
        $log->save();

        SendBusinessNotification::dispatch($log->id);
    }

    public function adminAddress(): string
    {
        return (string) config('zytecs.notifications.admin_address');
    }
}
