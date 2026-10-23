<?php

namespace App\Http\Controllers\Student;

use App\Enums\MessageChannel;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostMessageRequest;
use App\Models\Submission;
use App\Services\AuditLogger;
use App\Services\MessageThreads;
use App\Support\PrivateDownload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The student's materials downloads and their message thread with the team.
 */
class WorkController extends Controller
{
    public function downloadMaterial(Request $request, string $reference, int $deliverable, AuditLogger $audit): StreamedResponse
    {
        $submission = $this->owned($request, $reference);
        $record = $submission->deliverables()->whereKey($deliverable)->firstOrFail();
        abort_unless($record->isAvailableToStudent($submission->load('quotation')), 404);

        $audit->record('material.downloaded', $record, ['by' => 'student']);

        return PrivateDownload::response($record->storage_path, $record->original_name, $record->mime_type);
    }

    public function postMessage(PostMessageRequest $request, string $reference, MessageThreads $threads): RedirectResponse
    {
        $submission = $this->owned($request, $reference);
        $threads->post($submission, MessageChannel::Student, $request->user(), $request->validated('body'), $request->file('attachment'));

        return redirect()->to(route('student.submissions.show', $submission->reference).'#chat')->with('status', 'Message sent. We will reply as soon as we can.');
    }

    public function downloadAttachment(Request $request, string $reference, int $message): StreamedResponse
    {
        $submission = $this->owned($request, $reference);
        $record = $submission->messages()->where('channel', MessageChannel::Student)->whereKey($message)->whereNotNull('attachment_path')->firstOrFail();

        return PrivateDownload::response((string) $record->attachment_path, (string) $record->attachment_name, (string) $record->attachment_mime);
    }

    private function owned(Request $request, string $reference): Submission
    {
        return $request->user()->submissions()->where('reference', $reference)->firstOrFail();
    }
}
