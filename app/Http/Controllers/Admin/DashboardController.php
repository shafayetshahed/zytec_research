<?php

namespace App\Http\Controllers\Admin;

use App\Enums\NotificationStatus;
use App\Enums\SubmissionStatus;
use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\NotificationLog;
use App\Models\Submission;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $statusCounts = Submission::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $count = fn (SubmissionStatus ...$statuses): int => (int) collect($statuses)->sum(fn (SubmissionStatus $status) => $statusCounts[$status->value] ?? 0);

        return view('admin.dashboard', [
            'metrics' => [
                'students' => User::students()->count(),
                'verified' => User::students()->whereNotNull('email_verified_at')->count(),
                'submissions' => (int) $statusCounts->sum(),
                'new' => $count(SubmissionStatus::New),
                'under_review' => $count(SubmissionStatus::UnderReview),
                'awaiting' => $count(SubmissionStatus::AwaitingStudentResponse),
                'finished' => $count(SubmissionStatus::Completed, SubmissionStatus::Closed, SubmissionStatus::Declined),
                'failed_notifications' => NotificationLog::where('status', NotificationStatus::Failed)->count(),
                'open_enquiries' => Enquiry::whereNull('handled_at')->count(),
                'deletion_requests' => User::students()->whereNotNull('deletion_requested_at')->count(),
            ],
            'statusCounts' => collect(SubmissionStatus::cases())->mapWithKeys(fn (SubmissionStatus $status) => [$status->label() => $count($status)]),
            'recent' => Submission::with('student')->latest('submitted_at')->limit(8)->get(),
        ]);
    }
}
