<?php

namespace App\Http\Controllers\Student;

use App\Actions\SubmitBrief;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreSubmissionRequest;
use App\Models\Submission;
use App\Services\AuditLogger;
use App\Support\SiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class SubmissionController extends Controller
{
    public function create(Request $request): View
    {
        return view('student.submissions.create', [
            'user' => $request->user(),
            'clientToken' => (string) Str::uuid(),
            'countries' => SiteContent::countriesOfStudy(),
            'subjects' => array_values(SiteContent::portfolioAreas()),
            'assessmentTypes' => SiteContent::assessmentTypes(),
        ]);
    }

    public function store(StoreSubmissionRequest $request, SubmitBrief $submitBrief, AuditLogger $audit): RedirectResponse
    {
        try {
            $submission = $submitBrief->handle(
                $request->user(),
                $request->normalizedWhatsApp(),
                $request->submissionDetails(),
                $request->file('brief'),
                array_values($request->file('supplementary', [])),
            );
        } catch (Throwable $exception) {
            Log::error('Brief submission failed.', ['user' => $request->user()->id, 'exception' => class_basename($exception)]);

            return back()->withInput($request->except(['brief', 'supplementary']))
                ->with('error', 'Your brief could not be saved. Nothing was submitted. Please try again, or contact us on WhatsApp if the problem continues.');
        }

        if ($submission->wasRecentlyCreated) {
            $audit->record('submission.created', $submission);
        }

        return redirect()->route('student.submissions.show', $submission->reference)
            ->with('submitted', true);
    }

    public function show(Request $request, string $reference): View
    {
        $submission = $this->ownedSubmission($request, $reference);
        $submission->load('files');

        return view('student.submissions.show', [
            'submission' => $submission,
            'justSubmitted' => (bool) $request->session()->get('submitted'),
        ]);
    }

    public function download(Request $request, string $reference, int $file, AuditLogger $audit): StreamedResponse
    {
        $submission = $this->ownedSubmission($request, $reference);
        $record = $submission->files()->whereKey($file)->firstOrFail();

        $audit->record('file.downloaded', $record, ['submission' => $submission->reference]);

        return Storage::disk(config('zytecs.uploads.disk'))->download($record->storage_path, $record->original_name, [
            'Content-Type' => $record->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    /**
     * Resolves a submission only within the signed-in student's own records,
     * so another student's reference returns 404 rather than revealing it exists.
     */
    private function ownedSubmission(Request $request, string $reference): Submission
    {
        return $request->user()->submissions()->where('reference', $reference)->firstOrFail();
    }
}
