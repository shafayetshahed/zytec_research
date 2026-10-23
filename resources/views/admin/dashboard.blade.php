<x-layouts.admin title="Overview">
    <h1 class="font-serif text-3xl">Overview</h1>
    <p class="mt-1 text-muted">Live figures from the database.</p>

    @php
        $tiles = [
            ['Registered students', $metrics['students'], route('admin.students.index'), 'users'],
            ['Verified accounts', $metrics['verified'], route('admin.students.index'), 'badge-check'],
            ['Total submissions', $metrics['submissions'], route('admin.submissions.index'), 'inbox'],
            ['New', $metrics['new'], route('admin.submissions.index', ['status' => 'new']), 'file-text'],
            ['Under review', $metrics['under_review'], route('admin.submissions.index', ['status' => 'under_review']), 'eye'],
            ['Awaiting student response', $metrics['awaiting'], route('admin.submissions.index', ['status' => 'awaiting_student_response']), 'clock'],
            ['Completed, closed or declined', $metrics['finished'], route('admin.submissions.index'), 'check'],
            ['Failed notifications', $metrics['failed_notifications'], route('admin.notifications.index', ['status' => 'failed']), 'circle-alert'],
        ];
    @endphp

    <ul class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($tiles as [$label, $value, $url, $icon])
            <li>
                <a href="{{ $url }}" class="card flex h-full items-start justify-between gap-3 p-5 text-ink no-underline hover:border-royal/50">
                    <span>
                        <span class="block text-sm text-muted">{{ $label }}</span>
                        <span class="mt-2 block font-serif text-4xl {{ $label === 'Failed notifications' && $value > 0 ? 'text-danger' : '' }}">{{ number_format($value) }}</span>
                    </span>
                    <x-icon :name="$icon" class="size-5 text-slate" />
                </a>
            </li>
        @endforeach
    </ul>

    @if ($metrics['open_enquiries'] > 0 || $metrics['deletion_requests'] > 0)
        <div class="mt-6 grid gap-3">
            @if ($metrics['open_enquiries'] > 0)
                <div class="alert-info alert"><x-icon name="message-circle" class="mt-0.5 size-5" /><p>{{ $metrics['open_enquiries'] }} website {{ Str::plural('enquiry', $metrics['open_enquiries']) }} not yet handled. <a href="{{ route('admin.enquiries.index') }}">View enquiries</a></p></div>
            @endif
            @if ($metrics['deletion_requests'] > 0)
                <div class="alert-warning alert"><x-icon name="user-x" class="mt-0.5 size-5" /><p>{{ $metrics['deletion_requests'] }} account deletion {{ Str::plural('request', $metrics['deletion_requests']) }} waiting. <a href="{{ route('admin.students.index', ['filter' => 'deletion']) }}">Review requests</a></p></div>
            @endif
        </div>
    @endif

    <div class="mt-10 grid gap-8 xl:grid-cols-3">
        <section class="card overflow-x-auto xl:col-span-2" aria-labelledby="recent-heading">
            <div class="flex items-center justify-between border-b border-line px-5 py-4">
                <h2 id="recent-heading" class="font-serif text-xl">Latest submissions</h2>
                <a href="{{ route('admin.submissions.index') }}" class="text-sm font-semibold">View all</a>
            </div>
            @if ($recent->isEmpty())
                <p class="p-5 text-muted">No submissions yet.</p>
            @else
                <table class="data-table">
                    <thead><tr><th scope="col">Reference</th><th scope="col">Student</th><th scope="col">Module</th><th scope="col">Submitted</th><th scope="col">Status</th></tr></thead>
                    <tbody>
                        @foreach ($recent as $submission)
                            <tr>
                                <td><a href="{{ route('admin.submissions.show', $submission) }}" class="font-mono">{{ $submission->reference }}</a></td>
                                <td>{{ $submission->student->name }}</td>
                                <td><span class="code-tag">{{ $submission->module_code }}</span></td>
                                <td class="whitespace-nowrap">{{ $submission->submitted_at->format('j M Y H:i') }}</td>
                                <td><x-status-badge :status="$submission->status" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        <section class="card p-5" aria-labelledby="status-heading">
            <h2 id="status-heading" class="font-serif text-xl">By status</h2>
            <dl class="mt-4 divide-y divide-line-soft">
                @foreach ($statusCounts as $label => $count)
                    <div class="flex items-center justify-between py-2 text-sm"><dt>{{ $label }}</dt><dd class="font-mono font-medium">{{ $count }}</dd></div>
                @endforeach
            </dl>
        </section>
    </div>
</x-layouts.admin>
