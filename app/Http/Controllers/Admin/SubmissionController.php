<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Currency;
use App\Enums\NotificationStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\SubmissionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSubmissionStatusRequest;
use App\Jobs\SendBusinessNotification;
use App\Models\NotificationLog;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\SubmissionNote;
use App\Models\SubmissionStatusChange;
use App\Services\AuditLogger;
use App\Support\SiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(SubmissionStatus::class)],
            'payment' => ['nullable', Rule::enum(PaymentStatus::class)],
            'country' => ['nullable', 'string', 'max:60'],
            'subject' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $submissions = Submission::query()
            ->with(['student', 'quotation.payments'])
            ->search($filters['q'] ?? null)
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['payment'] ?? null, fn ($query, $payment) => $query->where('payment_status', $payment))
            ->when($filters['country'] ?? null, fn ($query, $country) => $query->where('country', $country))
            ->when($filters['subject'] ?? null, fn ($query, $subject) => $query->where('subject', $subject))
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->whereDate('submitted_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->whereDate('submitted_at', '<=', $to))
            ->latest('submitted_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.submissions.index', [
            'submissions' => $submissions,
            'filters' => $filters,
            'statuses' => SubmissionStatus::cases(),
            'paymentStatuses' => PaymentStatus::cases(),
            'countries' => SiteContent::countriesOfStudy(),
            'subjects' => array_values(SiteContent::portfolioAreas()),
        ]);
    }

    public function show(Submission $submission): View
    {
        $submission->load(['student', 'files', 'notes.admin', 'statusChanges.admin', 'notificationLogs', 'quotation.payments.recorder']);

        return view('admin.submissions.show', [
            'submission' => $submission,
            'history' => $submission->student->submissions()->whereKeyNot($submission->id)->latest('submitted_at')->limit(10)->get(),
            'statuses' => SubmissionStatus::cases(),
            'currencies' => Currency::cases(),
            'methods' => PaymentMethod::cases(),
            'countries' => SiteContent::countriesOfStudy(),
        ]);
    }

    public function updateStatus(UpdateSubmissionStatusRequest $request, Submission $submission, AuditLogger $audit): RedirectResponse
    {
        $newStatus = SubmissionStatus::from($request->validated('status'));
        $oldStatus = $submission->status;

        if ($newStatus === $oldStatus) {
            return back()->with('status', 'The status is already '.$newStatus->label().'.');
        }

        $quotation = $submission->quotation;

        if ($newStatus === SubmissionStatus::Completed && $quotation?->sent_at !== null && $quotation->balance() > 0) {
            return back()->with('error', 'This request still has '.$quotation->currency->format($quotation->balance()).' outstanding. Record the payment before marking it completed.');
        }

        DB::transaction(function () use ($request, $submission, $newStatus, $oldStatus) {
            $submission->status = $newStatus;
            $submission->closed_at = $newStatus->isFinal() ? now() : null;
            $submission->save();

            $change = new SubmissionStatusChange;
            $change->submission_id = $submission->id;
            $change->admin_id = $request->user()->id;
            $change->from_status = $oldStatus;
            $change->to_status = $newStatus;
            $change->save();

            if (filled($request->validated('note'))) {
                $this->addNote($submission, $request->user()->id, $request->validated('note'));
            }
        });

        $audit->record('submission.status_changed', $submission, ['from' => $oldStatus->value, 'to' => $newStatus->value]);

        return back()->with('status', 'Status changed to '.$newStatus->label().'.');
    }

    public function storeNote(Request $request, Submission $submission, AuditLogger $audit): RedirectResponse
    {
        $validated = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        $this->addNote($submission, $request->user()->id, $validated['body']);
        $audit->record('submission.note_added', $submission);

        return back()->with('status', 'Internal note added.');
    }

    public function retryNotification(Submission $submission, NotificationLog $notification, AuditLogger $audit): RedirectResponse
    {
        abort_unless($notification->submission_id === $submission->id, 404);

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

    public function download(Submission $submission, SubmissionFile $file, AuditLogger $audit): StreamedResponse
    {
        abort_unless($file->submission_id === $submission->id, 404);

        $audit->record('file.downloaded', $file, ['submission' => $submission->reference]);

        return Storage::disk(config('zytecs.uploads.disk'))->download($file->storage_path, $file->original_name, [
            'Content-Type' => $file->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    private function addNote(Submission $submission, int $adminId, string $body): void
    {
        $note = new SubmissionNote;
        $note->submission_id = $submission->id;
        $note->admin_id = $adminId;
        $note->body = $body;
        $note->save();
    }
}
