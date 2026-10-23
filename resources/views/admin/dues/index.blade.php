@php
    $bdt = fn ($v) => 'BDT '.number_format((float) $v, 2);
    $amounts = fn (array $list) => collect($list)->map(fn ($v, $c) => \App\Enums\Currency::from($c)->format($v));
    $tabs = ['all' => 'All clients', 'due' => 'With dues', 'paid' => 'Paid up', 'none' => 'No orders yet'];
    $query = fn (array $changes) => array_filter(array_merge(request()->only(['show', 'country', 'q', 'sort']), $changes), fn ($v) => $v !== null && $v !== '');
@endphp
<x-layouts.admin title="Clients & dues">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-serif text-3xl">Clients &amp; dues</h1>
            <p class="mt-1 max-w-2xl text-muted">Every client's position across all their orders, portal and manual. Use it to see who owes what and send reminders. Dues in taka are estimated at today's exchange rates.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.orders.create') }}" class="btn btn-primary btn-sm"><x-icon name="file-text" class="size-4" />New manual order</a>
            <a href="{{ route('admin.dues.export', request()->query()) }}" class="btn btn-outline btn-sm"><x-icon name="download" class="size-4" />Export CSV</a>
        </div>
    </div>

    <ul class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Summary">
        <li class="card border-danger/40 p-5">
            <span class="block text-sm text-muted">Total due now</span>
            <span class="mt-2 block font-serif text-3xl text-danger">{{ $stats['due_bdt'] !== null ? $bdt($stats['due_bdt']) : '—' }}</span>
            <span class="mt-1 block font-mono text-xs text-muted">@forelse ($amounts($stats['due_by_currency']) as $line){{ $line }}@if (! $loop->last) · @endif @empty Nothing due @endforelse</span>
        </li>
        <li class="card p-5">
            <span class="block text-sm text-muted">Total paid (all time)</span>
            <span class="mt-2 block font-serif text-3xl text-success">{{ $bdt($stats['paid_bdt']) }}</span>
        </li>
        <li class="card p-5">
            <a href="{{ route('admin.dues', ['show' => 'due']) }}" class="block text-ink no-underline">
                <span class="block text-sm text-muted">Clients with dues</span>
                <span class="mt-2 block font-serif text-3xl {{ $stats['clients_with_dues'] > 0 ? 'text-danger' : '' }}">{{ $stats['clients_with_dues'] }} <span class="text-lg text-muted">of {{ $stats['clients'] }}</span></span>
            </a>
        </li>
        <li class="card p-5">
            <span class="block text-sm text-muted">Orders</span>
            <span class="mt-2 block font-serif text-3xl">{{ $stats['orders'] }}</span>
            <span class="mt-1 block text-xs text-muted">{{ $stats['ongoing'] }} ongoing · {{ $stats['completed'] }} completed, closed or declined</span>
        </li>
    </ul>

    <nav aria-label="Show clients" class="mt-8 flex flex-wrap gap-2">
        @foreach ($tabs as $value => $label)
            <a href="{{ route('admin.dues', $query(['show' => $value === 'all' ? null : $value, 'page' => null])) }}" @if ($filters['show'] === $value) aria-current="page" @endif
               class="btn btn-outline btn-sm aria-[current=page]:border-ink aria-[current=page]:bg-ink aria-[current=page]:text-white">{{ $label }}</a>
        @endforeach
    </nav>

    <form method="GET" action="{{ route('admin.dues') }}" class="card mt-4 grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-4 lg:items-end" role="search" aria-label="Filter clients">
        @if ($filters['show'] !== 'all')<input type="hidden" name="show" value="{{ $filters['show'] }}">@endif
        <div class="field lg:col-span-2">
            <label for="q" class="label">Search</label>
            <input id="q" name="q" type="search" value="{{ $filters['q'] }}" class="input" placeholder="Name, email, WhatsApp or order reference">
        </div>
        <div class="field">
            <label for="country" class="label">Country</label>
            <select id="country" name="country" class="input">
                <option value="">All countries</option>
                @foreach ([...$countries, 'Not set'] as $country)
                    <option @selected($filters['country'] === $country)>{{ $country }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="sort" class="label">Sort by</label>
            <select id="sort" name="sort" class="input">
                @foreach (['due' => 'Biggest dues first', 'paid' => 'Most paid', 'recent' => 'Latest payment', 'name' => 'Name'] as $value => $label)
                    <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-3 lg:col-span-4">
            <button type="submit" class="btn btn-ink btn-sm"><x-icon name="filter" class="size-4" />Apply</button>
            <a href="{{ route('admin.dues') }}" class="text-sm">Reset</a>
        </div>
    </form>

    <section class="card mt-6 overflow-x-auto" aria-labelledby="clients-heading">
        <h2 id="clients-heading" class="sr-only">Clients</h2>
        @if ($rows->isEmpty())
            <p class="p-6 text-muted">No clients match.</p>
        @else
            <table class="data-table">
                <caption class="sr-only">Client balances</caption>
                <thead>
                    <tr>
                        <th scope="col">Client</th>
                        <th scope="col">Country</th>
                        <th scope="col">Orders</th>
                        <th scope="col" class="text-right">Total billed</th>
                        <th scope="col" class="text-right">Total paid</th>
                        <th scope="col" class="text-right">Total due</th>
                        <th scope="col">Last payment</th>
                        <th scope="col"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        @php $client = $row['client']; $reminder = $balances->reminderLink($row); @endphp
                        <tr class="{{ $row['has_dues'] ? 'bg-danger-bg/40' : '' }}">
                            <td class="min-w-52">
                                <a href="{{ route('admin.students.show', $client) }}" class="font-semibold">{{ $client->name }}</a>
                                <span class="mt-0.5 block text-xs text-muted">
                                    @if ($client->email)<span class="break-all">{{ $client->email }}</span><br>@endif
                                    @if ($client->whatsapp_number)<span class="font-mono whitespace-nowrap">{{ $client->whatsapp_number }}</span>@endif
                                    @if (! $client->email && ! $client->whatsapp_number)No contact details @endif
                                </span>
                                @if ($row['has_dues'])
                                    <details class="mt-2 text-xs">
                                        <summary class="cursor-pointer font-semibold text-danger">{{ $row['due_orders']->count() }} unpaid {{ Str::plural('order', $row['due_orders']->count()) }}</summary>
                                        <ul class="mt-2 space-y-1.5">
                                            @foreach ($row['due_orders'] as $order)
                                                <li>
                                                    <a href="{{ route('admin.submissions.show', $order) }}#billing" class="font-mono">{{ $order->reference }}</a>
                                                    <span class="text-ink-soft">{{ Str::limit($order->assignment_name, 36) }}</span><br>
                                                    <span class="font-mono">{{ $order->quotation->currency->format($order->quotation->balance()) }} due</span>
                                                    <span class="text-muted">of {{ $order->quotation->currency->format($order->quotation->total_amount) }}@if ($order->quotation->work_due_date) · delivery {{ $order->quotation->work_due_date->format('j M') }}@endif</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </details>
                                @endif
                            </td>
                            <td class="whitespace-nowrap">{{ $row['country'] ?? '—' }}</td>
                            <td class="whitespace-nowrap">{{ $row['orders'] }}@if ($row['ongoing'])<span class="block text-xs text-muted">{{ $row['ongoing'] }} ongoing</span>@endif</td>
                            <td class="text-right font-mono text-xs whitespace-nowrap">@forelse ($amounts($row['billed']) as $line){{ $line }}<br>@empty — @endforelse</td>
                            <td class="text-right font-mono text-xs whitespace-nowrap text-success">@forelse ($amounts($row['paid']) as $line){{ $line }}<br>@empty — @endforelse @if ($row['paid_bdt'] != 0)<span class="text-muted">{{ $bdt($row['paid_bdt']) }}</span>@endif</td>
                            <td class="text-right font-mono text-xs whitespace-nowrap">
                                @if ($row['has_dues'])
                                    @foreach ($amounts($row['due']) as $line)<strong class="text-danger">{{ $line }}</strong><br>@endforeach
                                    @if ($row['due_bdt'] !== null)<span class="text-muted">≈ {{ $bdt($row['due_bdt']) }}</span>@endif
                                @elseif ($row['paid'] !== [])
                                    <span class="badge bg-success-bg text-success">Paid up</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="whitespace-nowrap text-xs">{{ $row['last_payment']?->format('j M Y') ?? '—' }}</td>
                            <td class="whitespace-nowrap">
                                <div class="flex flex-col gap-1.5">
                                    @if ($reminder)
                                        <a href="{{ $reminder }}" class="btn btn-whatsapp btn-sm" rel="noopener noreferrer" target="_blank"><x-icon name="message-circle" class="size-4" />Remind<span class="sr-only"> {{ $client->name }} on WhatsApp (opens in a new tab)</span></a>
                                    @endif
                                    @if ($row['has_dues'] && $client->whatsapp_number)
                                        <a href="tel:{{ $client->whatsapp_number }}" class="btn btn-outline btn-sm"><x-icon name="phone" class="size-4" />Call<span class="sr-only"> {{ $client->name }}</span></a>
                                    @endif
                                    @if ($row['has_dues'] && $client->email)
                                        <a href="mailto:{{ $client->email }}?subject={{ rawurlencode('Balance due — Zytecs Research') }}" class="btn btn-outline btn-sm"><x-icon name="mail" class="size-4" />Email<span class="sr-only"> {{ $client->name }}</span></a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>
    {{ $rows->links() }}
</x-layouts.admin>
