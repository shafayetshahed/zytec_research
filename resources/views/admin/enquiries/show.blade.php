<x-layouts.admin title="Enquiry">
    <nav aria-label="Breadcrumb" class="text-sm"><a href="{{ route('admin.enquiries.index') }}">Enquiries</a> <span class="text-muted">/</span> <span aria-current="page">#{{ $enquiry->id }}</span></nav>
    <h1 class="mt-3 font-serif text-3xl">{{ $enquiry->enquiry_type->label() }} from {{ $enquiry->name }}</h1>
    <section class="card mt-6 max-w-3xl p-6">
        <dl class="grid gap-4 sm:grid-cols-2">
            <div><dt class="text-sm text-muted">Email</dt><dd class="mt-1 break-all">@if ($enquiry->email)<a href="mailto:{{ $enquiry->email }}">{{ $enquiry->email }}</a>@else — @endif</dd></div>
            <div><dt class="text-sm text-muted">WhatsApp</dt><dd class="mt-1 font-mono">@if ($enquiry->whatsapp_number)<a href="https://wa.me/{{ ltrim($enquiry->whatsapp_number, '+') }}" rel="noopener noreferrer" target="_blank">{{ $enquiry->whatsapp_number }}</a>@else — @endif</dd></div>
            <div><dt class="text-sm text-muted">Country of study</dt><dd class="mt-1">{{ $enquiry->country ?? '—' }}</dd></div>
            <div><dt class="text-sm text-muted">Subject</dt><dd class="mt-1">{{ $enquiry->subject ?? '—' }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-sm text-muted">Message</dt><dd class="mt-1 whitespace-pre-line">{{ $enquiry->message }}</dd></div>
            <div><dt class="text-sm text-muted">Received</dt><dd class="mt-1">{{ $enquiry->created_at->format('j F Y H:i') }}</dd></div>
        </dl>
        <form method="POST" action="{{ route('admin.enquiries.handled', $enquiry) }}" class="mt-6">
            @csrf
            <button type="submit" class="btn btn-ink btn-sm">{{ $enquiry->handled_at ? 'Mark as open' : 'Mark as handled' }}</button>
        </form>
    </section>
</x-layouts.admin>
