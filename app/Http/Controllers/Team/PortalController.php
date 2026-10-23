<?php

namespace App\Http\Controllers\Team;

use App\Enums\MessageChannel;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostMessageRequest;
use App\Http\Requests\UploadMaterialRequest;
use App\Models\Submission;
use App\Services\AuditLogger;
use App\Services\MaterialsService;
use App\Services\MessageThreads;
use App\Support\PrivateDownload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The Team portal. A team member sees only orders assigned to them, never
 * the student's name or contact details, and only the team message thread.
 */
class PortalController extends Controller
{
    public function dashboard(Request $request, MessageThreads $threads): View
    {
        $orders = $request->user()->assignedSubmissions()
            ->with(['deliverables'])
            ->latest('assigned_at')
            ->get();

        return view('team.dashboard', [
            'orders' => $orders,
            'unread' => $orders->mapWithKeys(fn (Submission $s) => [$s->id => $threads->unreadCount($s, $request->user(), MessageChannel::Team)]),
        ]);
    }

    public function show(Request $request, string $reference, MessageThreads $threads): View
    {
        $order = $this->order($request, $reference);
        $order->load(['files', 'deliverables']);
        $threads->markRead($order, $request->user(), MessageChannel::Team);

        return view('team.order', [
            'order' => $order,
            'messages' => $threads->thread($order, MessageChannel::Team),
        ]);
    }

    public function downloadBrief(Request $request, string $reference, int $file, AuditLogger $audit): StreamedResponse
    {
        $order = $this->order($request, $reference);
        $record = $order->files()->whereKey($file)->firstOrFail();
        $audit->record('file.downloaded', $record, ['submission' => $order->reference, 'by' => 'team']);

        return PrivateDownload::response($record->storage_path, $record->original_name, $record->mime_type);
    }

    public function uploadMaterial(UploadMaterialRequest $request, string $reference, MaterialsService $materials, AuditLogger $audit): RedirectResponse
    {
        $order = $this->order($request, $reference);
        $deliverable = $materials->upload($order, $request->user(), $request->file('material'), $request->validated('note'));
        $audit->record('material.uploaded', $deliverable, ['submission' => $order->reference]);

        return back()->with('status', 'Uploaded. An administrator will review it before it is shared.');
    }

    public function downloadMaterial(Request $request, string $reference, int $deliverable): StreamedResponse
    {
        $order = $this->order($request, $reference);
        $record = $order->deliverables()->whereKey($deliverable)->where('uploaded_by', $request->user()->id)->firstOrFail();

        return PrivateDownload::response($record->storage_path, $record->original_name, $record->mime_type);
    }

    public function postMessage(PostMessageRequest $request, string $reference, MessageThreads $threads): RedirectResponse
    {
        $order = $this->order($request, $reference);
        $threads->post($order, MessageChannel::Team, $request->user(), $request->validated('body'), $request->file('attachment'));

        return redirect()->to(route('team.orders.show', $order->reference).'#chat')->with('status', 'Message sent.');
    }

    public function downloadAttachment(Request $request, string $reference, int $message): StreamedResponse
    {
        $order = $this->order($request, $reference);
        $record = $order->messages()->where('channel', MessageChannel::Team)->whereKey($message)->whereNotNull('attachment_path')->firstOrFail();

        return PrivateDownload::response((string) $record->attachment_path, (string) $record->attachment_name, (string) $record->attachment_mime);
    }

    /**
     * Only orders assigned to the signed-in team member; anything else is 404.
     */
    private function order(Request $request, string $reference): Submission
    {
        return $request->user()->assignedSubmissions()->where('reference', $reference)->firstOrFail();
    }
}
