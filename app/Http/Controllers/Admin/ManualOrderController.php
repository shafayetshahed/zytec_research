<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Currency;
use App\Enums\NotificationStatus;
use App\Enums\OrderSource;
use App\Enums\SubmissionStatus;
use App\Http\Controllers\Controller;
use App\Jobs\SendBusinessNotification;
use App\Models\NotificationLog;
use App\Models\Quotation;
use App\Models\Submission;
use App\Models\SubmissionStatusChange;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\BillingService;
use App\Services\DocumentNumberService;
use App\Support\SiteContent;
use App\Support\SubmissionReference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Records an order taken on WhatsApp, by phone, email or in person, so it
 * flows through the same quotation, payment and accounting records as
 * portal orders.
 */
class ManualOrderController extends Controller
{
    public function create(Request $request): View
    {
        return view('admin.orders.create', [
            'clients' => User::students()->orderBy('name')->get(['id', 'name', 'email', 'whatsapp_number', 'country']),
            'selectedClient' => $request->integer('client') ?: null,
            'sources' => OrderSource::manual(),
            'currencies' => Currency::cases(),
            'countries' => SiteContent::countriesOfStudy(),
        ]);
    }

    public function store(Request $request, BillingService $billing, DocumentNumberService $numbers, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validate([
            'client_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'student')],
            'source' => ['required', Rule::in(array_map(fn (OrderSource $s) => $s->value, OrderSource::manual()))],
            'assignment_name' => ['required', 'string', 'min:2', 'max:200'],
            'module_code' => ['nullable', 'string', 'max:40'],
            'country' => ['nullable', 'string', Rule::in(SiteContent::countriesOfStudy())],
            'currency' => ['required', Rule::enum(Currency::class)],
            'total_amount' => ['required', 'numeric', 'min:0.01', 'max:9999999.99', 'decimal:0,2'],
            'deposit_amount' => ['required', 'numeric', 'min:0', 'lte:total_amount', 'decimal:0,2'],
            'order_date' => ['nullable', 'date', 'before_or_equal:today'],
            'work_due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'email_client' => ['nullable', 'boolean'],
        ], [
            'client_id.required' => 'Choose the client, or add a new client first.',
            'deposit_amount.lte' => 'The deposit cannot be more than the total price.',
        ]);

        $client = User::findOrFail($data['client_id']);
        $orderedAt = isset($data['order_date']) ? Carbon::parse($data['order_date'])->setTimeFrom(now()) : now();

        $submission = DB::transaction(function () use ($data, $client, $orderedAt, $request, $billing, $numbers) {
            $submission = new Submission;
            $submission->fill([
                'assignment_name' => trim($data['assignment_name']),
                'module_code' => filled($data['module_code'] ?? null) ? mb_strtoupper(trim($data['module_code'])) : null,
                'country' => $data['country'] ?? $client->country,
                'deadline' => $data['work_due_date'] ?? null,
                'description' => $data['notes'] ?? null,
            ]);
            $submission->user_id = $client->id;
            $submission->client_token = (string) Str::uuid();
            $submission->source = OrderSource::from($data['source']);
            $submission->reference = SubmissionReference::generate();
            $submission->contact_email = $client->email;
            $submission->whatsapp_number = $client->whatsapp_number;
            $submission->status = SubmissionStatus::Accepted;
            $submission->submitted_at = $orderedAt;
            $submission->save();

            $change = new SubmissionStatusChange;
            $change->submission_id = $submission->id;
            $change->admin_id = $request->user()->id;
            $change->to_status = SubmissionStatus::Accepted;
            $change->save();

            $quotation = new Quotation;
            $quotation->fill([
                'currency' => $data['currency'],
                'total_amount' => $data['total_amount'],
                'deposit_amount' => $data['deposit_amount'],
                'work_due_date' => $data['work_due_date'] ?? null,
                'country' => $data['country'] ?? $client->country,
            ]);
            $quotation->submission_id = $submission->id;
            $quotation->created_by = $request->user()->id;
            $quotation->sent_at = $orderedAt;
            $quotation->number = $numbers->next(DocumentNumberService::QUOTATION);
            $quotation->save();

            $billing->refresh($submission, $request->user()->id);

            return $submission;
        });

        $audit->record('order.manual_created', $submission, ['source' => $data['source'], 'total' => $data['total_amount'], 'currency' => $data['currency']]);

        if ($request->boolean('email_client') && filled($client->email)) {
            $log = new NotificationLog;
            $log->submission_id = $submission->id;
            $log->type = NotificationLog::TYPE_QUOTATION_SENT;
            $log->recipient = (string) $client->email;
            $log->status = NotificationStatus::Pending;
            $log->save();

            SendBusinessNotification::dispatch($log->id);
        }

        return redirect()->route('admin.submissions.show', $submission)->with('status', 'Order '.$submission->reference.' added. Record payments below as they arrive.');
    }
}
