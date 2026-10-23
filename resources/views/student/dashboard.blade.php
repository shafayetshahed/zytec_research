<x-layouts.student title="Dashboard">
    <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="eyebrow">Student portal</p>
            <h1 class="mt-3 font-serif text-3xl sm:text-4xl">Welcome, {{ $user->name }}</h1>
        </div>
        <a href="{{ route('student.submissions.create') }}" class="btn btn-primary self-start"><x-icon name="file-up" class="size-5" />Submit a new brief</a>
    </div>

    <dl class="mt-8 grid gap-px overflow-hidden rounded-lg border border-line bg-line sm:grid-cols-3">
        <div class="bg-card p-5">
            <dt class="text-sm text-muted">Email</dt>
            <dd class="mt-1 flex flex-wrap items-center gap-2 font-medium break-all">
                {{ $user->email }}
                <span class="badge bg-success-bg text-success"><x-icon name="badge-check" class="size-3.5" />Verified</span>
            </dd>
        </div>
        <div class="bg-card p-5">
            <dt class="text-sm text-muted">WhatsApp</dt>
            <dd class="mt-1 font-mono font-medium">{{ $user->whatsapp_number ?? 'Not set' }}</dd>
        </div>
        <div class="bg-card p-5">
            <dt class="text-sm text-muted">Account</dt>
            <dd class="mt-1"><a href="{{ route('student.profile') }}" class="link-arrow">Profile and security<x-icon name="arrow-right" class="size-4" /></a></dd>
        </div>
    </dl>

    <section class="mt-12" aria-labelledby="submissions-heading">
        <h2 id="submissions-heading" class="font-serif text-2xl">Your submissions</h2>

        @if ($submissions->isEmpty())
            <div class="card mt-5 flex flex-col items-start gap-4 p-8 sm:flex-row sm:items-center">
                <span class="flex size-12 items-center justify-center rounded-full bg-paper-deep text-royal"><x-icon name="file-text" class="size-6" /></span>
                <div class="flex-1">
                    <p class="font-semibold">You have not submitted a brief yet.</p>
                    <p class="mt-1 text-ink-soft">Upload your assignment brief with the module code and we will review it and contact you on WhatsApp or email to discuss scope, timing and price.</p>
                </div>
                <a href="{{ route('student.submissions.create') }}" class="btn btn-primary">Submit your first brief</a>
            </div>
        @else
            <div class="card mt-5 overflow-x-auto">
                <table class="data-table">
                    <caption class="sr-only">Your submitted briefs</caption>
                    <thead>
                        <tr>
                            <th scope="col">Reference</th>
                            <th scope="col">Assignment</th>
                            <th scope="col">Module</th>
                            <th scope="col">File</th>
                            <th scope="col">Submitted</th>
                            <th scope="col">Status</th>
                            <th scope="col">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($submissions as $submission)
                            <tr>
                                <td><a href="{{ route('student.submissions.show', $submission->reference) }}" class="font-mono font-medium">{{ $submission->reference }}</a></td>
                                <td class="min-w-48">{{ $submission->assignment_name }}</td>
                                <td>@if ($submission->module_code)<span class="code-tag">{{ $submission->module_code }}</span>@else<span class="text-muted">—</span>@endif</td>
                                <td class="max-w-56 truncate" title="{{ $submission->brief?->original_name }}">{{ $submission->brief?->original_name }}</td>
                                <td class="whitespace-nowrap"><time datetime="{{ $submission->submitted_at->toIso8601String() }}">{{ $submission->submitted_at->format('j M Y') }}</time></td>
                                <td><x-status-badge :status="$submission->status" /></td>
                                <td class="whitespace-nowrap">
                                    @if ($submission->quotation?->sent_at)
                                        <span class="font-mono">{{ $submission->quotation->currency->format($submission->quotation->total_amount) }}</span>
                                        @if ($submission->quotation->balance() > 0)
                                            <span class="block text-xs font-semibold text-danger">{{ $submission->quotation->currency->format($submission->quotation->balance()) }} due</span>
                                        @else
                                            <span class="block text-xs font-semibold text-success">Paid in full</span>
                                        @endif
                                    @else
                                        <span class="text-muted">Awaiting quote</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $submissions->links() }}
        @endif
    </section>
</x-layouts.student>
