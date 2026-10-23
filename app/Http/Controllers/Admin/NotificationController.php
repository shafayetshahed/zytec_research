<?php

namespace App\Http\Controllers\Admin;

use App\Enums\NotificationStatus;
use App\Http\Controllers\Controller;
use App\Jobs\SendBusinessNotification;
use App\Models\NotificationLog;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['status' => ['nullable', Rule::enum(NotificationStatus::class)]]);

        return view('admin.notifications.index', [
            'logs' => NotificationLog::with(['submission', 'enquiry'])
                ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
                ->latest('id')
                ->paginate(30)
                ->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function retry(NotificationLog $notification, AuditLogger $audit): RedirectResponse
    {
        if ($notification->status === NotificationStatus::Sent) {
            return back()->with('status', 'That notification has already been delivered.');
        }

        SendBusinessNotification::dispatchSync($notification->id, true);
        $notification->refresh();
        $audit->record('notification.retried', $notification, ['result' => $notification->status->value]);

        return $notification->status === NotificationStatus::Sent
            ? back()->with('status', 'Notification sent.')
            : back()->with('error', 'The notification still could not be delivered. Check the mail settings.');
    }
}
