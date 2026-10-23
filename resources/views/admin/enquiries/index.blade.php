<x-layouts.admin title="Enquiries">
    <h1 class="font-serif text-3xl">Website enquiries</h1>
    <div class="card mt-6 overflow-x-auto">
        @if ($enquiries->isEmpty())
            <p class="p-6 text-muted">No enquiries yet.</p>
        @else
            <table class="data-table">
                <thead><tr><th scope="col">Received</th><th scope="col">Name</th><th scope="col">Type</th><th scope="col">Reply via</th><th scope="col">Status</th></tr></thead>
                <tbody>
                    @foreach ($enquiries as $enquiry)
                        <tr>
                            <td class="whitespace-nowrap">{{ $enquiry->created_at->format('j M Y H:i') }}</td>
                            <td><a href="{{ route('admin.enquiries.show', $enquiry) }}" class="font-medium">{{ $enquiry->name }}</a></td>
                            <td>{{ $enquiry->enquiry_type->label() }}</td>
                            <td class="break-all">{{ $enquiry->whatsapp_number ?? $enquiry->email }}</td>
                            <td>@if ($enquiry->handled_at)<span class="badge bg-success-bg text-success">Handled</span>@else<span class="badge bg-warning-bg text-warning">Open</span>@endif</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    {{ $enquiries->links() }}
</x-layouts.admin>
