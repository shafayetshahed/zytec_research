<x-layouts.admin title="Audit log">
    <h1 class="font-serif text-3xl">Audit log</h1>
    <p class="mt-1 text-muted">Security and administrative events. Passcodes, passwords and document contents are never recorded.</p>
    <div class="card mt-6 overflow-x-auto">
        <table class="data-table">
            <thead><tr><th scope="col">When</th><th scope="col">Who</th><th scope="col">Action</th><th scope="col">Subject</th><th scope="col">Details</th><th scope="col">IP</th></tr></thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td class="whitespace-nowrap">{{ $log->created_at->format('j M Y H:i:s') }}</td>
                        <td>{{ $log->actor?->name ?? '—' }}</td>
                        <td class="font-mono text-xs">{{ $log->action }}</td>
                        <td class="text-xs">{{ $log->subject_type ? class_basename($log->subject_type).' #'.$log->subject_id : '—' }}</td>
                        <td class="font-mono text-xs">{{ $log->metadata ? json_encode($log->metadata) : '' }}</td>
                        <td class="font-mono text-xs">{{ $log->ip_address }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted">No events yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $logs->links() }}
</x-layouts.admin>
