<x-layouts.team title="Assigned orders">
    <p class="eyebrow">Team portal</p>
    <h1 class="mt-3 font-serif text-3xl sm:text-4xl">Your assigned orders</h1>
    <p class="mt-2 max-w-2xl text-ink-soft">Prepare guidance and feedback that helps the student understand the task and improve their own work: process notes, suggestions, structure advice, feedback on their draft. Everything you upload is reviewed before it is shared.</p>

    <div class="card mt-8 overflow-x-auto">
        @if ($orders->isEmpty())
            <p class="p-6 text-muted">No orders are assigned to you yet.</p>
        @else
            <table class="data-table">
                <caption class="sr-only">Orders assigned to you</caption>
                <thead><tr><th scope="col">Order</th><th scope="col">Work</th><th scope="col">Module</th><th scope="col">Delivery</th><th scope="col">Your uploads</th><th scope="col">Messages</th></tr></thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td><a href="{{ route('team.orders.show', $order->reference) }}" class="font-mono font-medium">{{ $order->reference }}</a></td>
                            <td class="min-w-48">{{ $order->assignment_name }}</td>
                            <td>@if ($order->module_code)<span class="code-tag">{{ $order->module_code }}</span>@else — @endif</td>
                            <td class="whitespace-nowrap">{{ ($order->quotation?->work_due_date ?? $order->deadline)?->format('j M Y') ?? '—' }}</td>
                            <td class="text-sm">
                                @php $mine = $order->deliverables->where('uploaded_by', auth()->id()); @endphp
                                @if ($mine->isEmpty())
                                    <span class="text-muted">None yet</span>
                                @else
                                    @foreach ($mine->groupBy(fn ($d) => $d->status->label()) as $label => $group)
                                        <span class="block">{{ $group->count() }} {{ strtolower($label) }}</span>
                                    @endforeach
                                @endif
                            </td>
                            <td>@if (($unread[$order->id] ?? 0) > 0)<span class="badge bg-royal text-white">{{ $unread[$order->id] }} new</span>@else<span class="text-muted">—</span>@endif</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-layouts.team>
