<x-layouts.admin title="Submissions">
    <h1 class="font-serif text-3xl">Submissions</h1>

    <form method="GET" action="{{ route('admin.submissions.index') }}" class="card mt-6 grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-7" role="search" aria-label="Filter submissions">
        <div class="field lg:col-span-2">
            <label for="q" class="label">Search</label>
            <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" class="input" placeholder="Name, email, reference, assignment or module">
        </div>
        <div class="field">
            <label for="status" class="label">Status</label>
            <select id="status" name="status" class="input">
                <option value="">Any</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="payment" class="label">Payment</label>
            <select id="payment" name="payment" class="input">
                <option value="">Any</option>
                @foreach ($paymentStatuses as $paymentStatus)
                    <option value="{{ $paymentStatus->value }}" @selected(($filters['payment'] ?? '') === $paymentStatus->value)>{{ $paymentStatus->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="country" class="label">Country</label>
            <select id="country" name="country" class="input">
                <option value="">Any</option>
                @foreach ($countries as $country)
                    <option @selected(($filters['country'] ?? '') === $country)>{{ $country }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="subject" class="label">Subject</label>
            <select id="subject" name="subject" class="input">
                <option value="">Any</option>
                @foreach ($subjects as $subject)
                    <option @selected(($filters['subject'] ?? '') === $subject)>{{ $subject }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div class="field"><label for="from" class="label">From</label><input id="from" name="from" type="date" value="{{ $filters['from'] ?? '' }}" class="input px-2"></div>
            <div class="field"><label for="to" class="label">To</label><input id="to" name="to" type="date" value="{{ $filters['to'] ?? '' }}" class="input px-2"></div>
        </div>
        <div class="flex items-end gap-3 sm:col-span-2 lg:col-span-7">
            <button type="submit" class="btn btn-ink btn-sm"><x-icon name="filter" class="size-4" />Apply filters</button>
            <a href="{{ route('admin.submissions.index') }}" class="text-sm">Clear</a>
        </div>
    </form>

    <div class="card mt-6 overflow-x-auto">
        @if ($submissions->isEmpty())
            <p class="p-6 text-muted">No submissions match these filters.</p>
        @else
            <table class="data-table">
                <caption class="sr-only">Submissions</caption>
                <thead>
                    <tr>
                        <th scope="col">Reference</th><th scope="col">Student</th><th scope="col">WhatsApp</th><th scope="col">Assignment</th>
                        <th scope="col">Module</th><th scope="col">Institution / country</th><th scope="col">Submitted</th><th scope="col">Deadline</th><th scope="col">Status</th><th scope="col">Payment</th><th scope="col"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($submissions as $submission)
                        <tr>
                            <td class="font-mono whitespace-nowrap">{{ $submission->reference }}</td>
                            <td><span class="font-medium">{{ $submission->student->name }}</span><br><span class="text-muted break-all">{{ $submission->contact_email ?? 'No email' }}</span>@if ($submission->source !== \App\Enums\OrderSource::Portal)<br><span class="badge mt-1 bg-paper-deep text-ink">{{ $submission->source->label() }}</span>@endif</td>
                            <td class="font-mono whitespace-nowrap">{{ $submission->whatsapp_number ?? '—' }}</td>
                            <td class="min-w-44">{{ $submission->assignment_name }}</td>
                            <td>@if ($submission->module_code)<span class="code-tag">{{ $submission->module_code }}</span>@else<span class="text-muted">—</span>@endif</td>
                            <td>{{ $submission->institution ?? '—' }}<br><span class="text-muted">{{ $submission->country ?? '—' }}</span></td>
                            <td class="whitespace-nowrap">{{ $submission->submitted_at->format('j M Y') }}</td>
                            <td class="whitespace-nowrap">{{ $submission->deadline?->format('j M Y') ?? '—' }}</td>
                            <td><x-status-badge :status="$submission->status" /></td>
                            <td>
                                <x-payment-badge :status="$submission->payment_status" />
                                @if ($submission->quotation?->sent_at && $submission->quotation->balance() > 0)
                                    <span class="mt-1 block font-mono text-xs text-muted whitespace-nowrap">Due {{ $submission->quotation->currency->format($submission->quotation->balance()) }}</span>
                                @endif
                            </td>
                            <td><a href="{{ route('admin.submissions.show', $submission) }}" class="btn btn-outline btn-sm">Open<span class="sr-only"> {{ $submission->reference }}</span></a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    {{ $submissions->links() }}
</x-layouts.admin>
