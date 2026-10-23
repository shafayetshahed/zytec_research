<x-layouts.admin title="Notifications">
    <h1 class="font-serif text-3xl">Email notifications</h1>
    <p class="mt-1 text-muted">Delivery records for emails sent to {{ config('zytecs.notifications.admin_address') }}. Message contents are not stored.</p>

    <nav aria-label="Filter by status" class="mt-6 flex flex-wrap gap-2">
        @foreach (['' => 'All', 'failed' => 'Failed', 'pending' => 'Pending', 'sent' => 'Sent'] as $value => $label)
            <a href="{{ route('admin.notifications.index', array_filter(['status' => $value])) }}" @if (($filters['status'] ?? '') === $value) aria-current="page" @endif
               class="btn btn-outline btn-sm aria-[current=page]:border-ink aria-[current=page]:bg-ink aria-[current=page]:text-white">{{ $label }}</a>
        @endforeach
    </nav>

    <div class="card mt-6 overflow-x-auto">
        @if ($logs->isEmpty())
            <p class="p-6 text-muted">No notifications recorded.</p>
        @else
            <table class="data-table">
                <thead><tr><th scope="col">Type</th><th scope="col">Related to</th><th scope="col">Status</th><th scope="col">Attempts</th><th scope="col">Last activity</th><th scope="col">Error</th><th scope="col"><span class="sr-only">Actions</span></th></tr></thead>
                <tbody>
                    @foreach ($logs as $log)
                        <tr>
                            <td>{{ $log->typeLabel() }}</td>
                            <td>
                                @if ($log->submission)
                                    <a href="{{ route('admin.submissions.show', $log->submission) }}" class="font-mono">{{ $log->submission->reference }}</a>
                                @elseif ($log->enquiry)
                                    <a href="{{ route('admin.enquiries.show', $log->enquiry) }}">Enquiry #{{ $log->enquiry->id }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td><x-status-badge :status="$log->status" /></td>
                            <td class="font-mono">{{ $log->attempts }}</td>
                            <td class="whitespace-nowrap">{{ ($log->sent_at ?? $log->last_attempt_at ?? $log->created_at)->format('j M Y H:i') }}</td>
                            <td class="text-danger">{{ $log->error_summary }}</td>
                            <td>
                                @if ($log->status !== \App\Enums\NotificationStatus::Sent)
                                    <form method="POST" action="{{ route('admin.notifications.retry', $log) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline btn-sm"><x-icon name="refresh-cw" class="size-4" />Retry</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    {{ $logs->links() }}
</x-layouts.admin>
