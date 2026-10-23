<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MessageChannel;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostMessageRequest;
use App\Http\Requests\UploadMaterialRequest;
use App\Models\Deliverable;
use App\Models\Message;
use App\Models\NotificationLog;
use App\Models\Submission;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\MaterialsService;
use App\Services\MessageThreads;
use App\Services\OrderNotifier;
use App\Support\PrivateDownload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Administrator tools on an order: assigning a team member, reviewing and
 * releasing materials, and both message threads.
 */
class OrderWorkController extends Controller
{
    public function assign(Request $request, Submission $submission, OrderNotifier $notifier, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validate([
            'expert_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', UserRole::Expert->value)->where('status', 'active')],
            'expert_instructions' => ['nullable', 'string', 'max:5000'],
        ]);

        $previous = $submission->expert_id;
        $submission->expert_id = $data['expert_id'] ?? null;
        $submission->expert_instructions = $data['expert_instructions'] ?? null;
        $submission->assigned_at = $submission->expert_id ? ($previous === $submission->expert_id ? $submission->assigned_at : now()) : null;
        $submission->save();

        $audit->record('order.assigned', $submission, ['expert_id' => $submission->expert_id]);

        if ($submission->expert_id && $previous !== $submission->expert_id) {
            $notifier->send($submission, NotificationLog::TYPE_EXPERT_ASSIGNED, User::find($submission->expert_id)?->email);

            return back()->with('status', 'Assigned. The team member has been emailed.');
        }

        return back()->with('status', $submission->expert_id ? 'Instructions saved.' : 'Team member removed from this order.');
    }

    public function uploadMaterial(UploadMaterialRequest $request, Submission $submission, MaterialsService $materials, AuditLogger $audit): RedirectResponse
    {
        $deliverable = $materials->upload($submission, $request->user(), $request->file('material'), $request->validated('note'));
        $audit->record('material.uploaded', $deliverable, ['submission' => $submission->reference]);

        return back()->with('status', $deliverable->released_at ? 'Uploaded and released to the student.' : 'Uploaded and approved. It will be released to the student once the order is paid in full.');
    }

    public function approveMaterial(Request $request, Submission $submission, Deliverable $deliverable, MaterialsService $materials, AuditLogger $audit): RedirectResponse
    {
        abort_unless($deliverable->submission_id === $submission->id, 404);

        $materials->approve($deliverable, $request->user(), $request->boolean('release_before_payment'));
        $audit->record('material.approved', $deliverable, ['early_release' => $request->boolean('release_before_payment')]);

        return back()->with('status', $deliverable->fresh()->released_at ? 'Approved and released. The student has been emailed.' : 'Approved. It will be released automatically once the order is paid in full.');
    }

    public function returnMaterial(Request $request, Submission $submission, Deliverable $deliverable, MaterialsService $materials, AuditLogger $audit): RedirectResponse
    {
        abort_unless($deliverable->submission_id === $submission->id, 404);

        $note = $request->validate(['review_note' => ['required', 'string', 'max:3000']], ['review_note.required' => 'Tell the team member what needs changing.'])['review_note'];
        $materials->sendBack($deliverable, $request->user(), $note);
        $audit->record('material.returned', $deliverable);

        return back()->with('status', 'Sent back to the team member with your note.');
    }

    public function downloadMaterial(Submission $submission, Deliverable $deliverable, AuditLogger $audit): StreamedResponse
    {
        abort_unless($deliverable->submission_id === $submission->id, 404);
        $audit->record('material.downloaded', $deliverable);

        return PrivateDownload::response($deliverable->storage_path, $deliverable->original_name, $deliverable->mime_type);
    }

    public function postMessage(PostMessageRequest $request, Submission $submission, string $channel, MessageThreads $threads): RedirectResponse
    {
        $thread = MessageChannel::from($channel);
        abort_if($thread === MessageChannel::Team && $submission->expert_id === null, 422, 'Assign a team member first.');

        $threads->post($submission, $thread, $request->user(), $request->validated('body'), $request->file('attachment'));

        return redirect()->to(route('admin.submissions.show', $submission).'#chat-'.$thread->value)->with('status', 'Message sent.');
    }

    public function downloadAttachment(Submission $submission, Message $message): StreamedResponse
    {
        abort_unless($message->submission_id === $submission->id && $message->attachment_path !== null, 404);

        return PrivateDownload::response($message->attachment_path, (string) $message->attachment_name, (string) $message->attachment_mime);
    }
}
