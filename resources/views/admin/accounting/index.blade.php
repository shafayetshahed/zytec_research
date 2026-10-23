@php
    $bdt = fn ($v) => 'BDT '.number_format((float) $v, 2);
    $usedCurrencies = collect($currencies)->filter(fn ($c) => $ledger->contains(fn ($p) => $p->currency === $c))->values();
    $periods = ['this_month' => 'This month', 'last_month' => 'Last month', 'this_year' => 'This year', 'last_year' => 'Last year', 'all' => 'All time', 'custom' => 'Custom dates'];
@endphp
<x-layouts.admin title="Accounting">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-serif text-3xl">Accounting</h1>
            <p class="mt-1 text-muted">{{ $from->format('j M Y') }} – {{ $to->format('j M Y') }}. Income is counted on the date each payment was received; taka values use the rate saved with each payment.</p>
        </div>
        <a href="{{ route('admin.accounting.export', request()->query()) }}" class="btn btn-outline btn-sm"><x-icon name="download" class="size-4" />Export CSV</a>
    </div>

    <form method="GET" action="{{ route('admin.accounting') }}" class="card mt-6 grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-6 lg:items-end" aria-label="Filter accounting">
        <div class="field lg:col-span-2">
            <label for="period" class="label">Period</label>
            <select id="period" name="period" class="input">
                @foreach ($periods as $value => $label)
                    <option value="{{ $value }}" @selected($filters['period'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="field"><label for="from" class="label">From <span class="label-optional">(custom)</span></label><input id="from" name="from" type="date" class="input px-2" value="{{ $filters['from'] ?? '' }}"></div>
        <div class="field"><label for="to" class="label">To <span class="label-optional">(custom)</span></label><input id="to" name="to" type="date" class="input px-2" value="{{ $filters['to'] ?? '' }}"></div>
        <div class="field">
            <label for="country" class="label">Country</label>
            <select id="country" name="country" class="input">
                <option value="">All countries</option>
                @foreach ([...$countries, 'Not specified'] as $country)
                    <option @selected(($filters['country'] ?? '') === $country)>{{ $country }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="currency" class="label">Currency</label>
            <select id="currency" name="currency" class="input">
                <option value="">All currencies</option>
                @foreach ($currencies as $currency)
                    <option value="{{ $currency->value }}" @selected(($filters['currency'] ?? '') === $currency->value)>{{ $currency->value }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-3 lg:col-span-6">
            <button type="submit" class="btn btn-ink btn-sm"><x-icon name="filter" class="size-4" />Show</button>
            <a href="{{ route('admin.accounting') }}" class="text-sm">Reset</a>
        </div>
    </form>
    <x-field-error name="from" /><x-field-error name="to" />

    <ul class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <li class="card p-5"><span class="block text-sm text-muted">Net income</span><span class="mt-2 block font-serif text-3xl">{{ $bdt($summary['net_bdt']) }}</span><span class="text-xs text-muted">{{ $summary['count'] }} {{ Str::plural('transaction', $summary['count']) }}</span></li>
        <li class="card p-5"><span class="block text-sm text-muted">Payments received</span><span class="mt-2 block font-serif text-3xl text-success">{{ $bdt($summary['received_bdt']) }}</span></li>
        <li class="card p-5"><span class="block text-sm text-muted">Refunds given</span><span class="mt-2 block font-serif text-3xl {{ $summary['refunded_bdt'] > 0 ? 'text-danger' : '' }}">{{ $bdt($summary['refunded_bdt']) }}</span></li>
        <li class="card p-5"><span class="block text-sm text-muted">Outstanding now (all dates)</span><span class="mt-2 block font-serif text-3xl {{ $outstanding->isNotEmpty() ? 'text-warning' : '' }}">{{ $summary['outstanding_bdt'] !== null ? $bdt($summary['outstanding_bdt']) : '—' }}</span><span class="text-xs text-muted">estimate at today's rates</span></li>
    </ul>

    <div class="mt-8 grid grid-cols-1 gap-6 xl:grid-cols-2">
        @foreach (['By currency' => $byCurrency, 'By country' => $byCountry] as $heading => $rows)
            <section class="card overflow-x-auto" aria-label="{{ $heading }}">
                <h2 class="border-b border-line px-5 py-4 font-serif text-xl">{{ $heading }}</h2>
                @if ($rows->isEmpty())
                    <p class="p-5 text-sm text-muted">No payments in this period.</p>
                @else
                    <table class="data-table">
                        <thead><tr><th scope="col">{{ $heading === 'By currency' ? 'Currency' : 'Country' }}</th><th scope="col">Received (native)</th><th scope="col" class="text-right">In BDT</th></tr></thead>
                        <tbody>
                            @foreach ($rows as $label => $row)
                                <tr>
                                    <th scope="row" class="!text-sm !normal-case !tracking-normal !text-ink">{{ $label }}</th>
                                    <td class="font-mono text-xs">@foreach ($row['native'] as $code => $amount){{ \App\Enums\Currency::from($code)->format($amount) }}@if (! $loop->last)<br>@endif @endforeach</td>
                                    <td class="text-right font-mono">{{ $bdt($row['bdt']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </section>
        @endforeach
    </div>

    @foreach (['Monthly income' => $byMonth, 'Yearly income' => $byYear] as $heading => $rows)
        <section class="card mt-6 overflow-x-auto" aria-label="{{ $heading }}">
            <h2 class="border-b border-line px-5 py-4 font-serif text-xl">{{ $heading }}</h2>
            @if ($rows->isEmpty())
                <p class="p-5 text-sm text-muted">No payments in this period.</p>
            @else
                <table class="data-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ $heading === 'Monthly income' ? 'Month' : 'Year' }}</th>
                            @foreach ($usedCurrencies as $currency)<th scope="col" class="text-right">{{ $currency->value }}</th>@endforeach
                            <th scope="col" class="text-right">Total in BDT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $key => $row)
                            <tr>
                                <th scope="row" class="!text-sm !normal-case !tracking-normal !text-ink whitespace-nowrap">{{ $heading === 'Monthly income' ? \Illuminate\Support\Carbon::createFromFormat('Y-m', $key)->format('F Y') : $key }}</th>
                                @foreach ($usedCurrencies as $currency)
                                    <td class="text-right font-mono">{{ isset($row['native'][$currency->value]) ? number_format($row['native'][$currency->value], 2) : '—' }}</td>
                                @endforeach
                                <td class="text-right font-mono font-semibold">{{ $bdt($row['bdt']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>
    @endforeach

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <section class="card overflow-x-auto xl:col-span-2" aria-labelledby="outstanding-heading">
            <h2 id="outstanding-heading" class="border-b border-line px-5 py-4 font-serif text-xl">Outstanding balances <span class="text-sm font-sans text-muted">(all dates)</span></h2>
            @if ($outstanding->isEmpty())
                <p class="p-5 text-sm text-muted">Nothing outstanding.</p>
            @else
                <table class="data-table">
                    <thead><tr><th scope="col">Submission</th><th scope="col">Country</th><th scope="col" class="text-right">Total</th><th scope="col" class="text-right">Paid</th><th scope="col" class="text-right">Balance</th><th scope="col">Delivery</th></tr></thead>
                    <tbody>
                        @foreach ($outstanding as $quotation)
                            <tr>
                                <td><a href="{{ route('admin.submissions.show', $quotation->submission) }}#billing" class="font-mono">{{ $quotation->submission->reference }}</a><br><span class="text-xs text-muted">{{ Str::limit($quotation->submission->assignment_name, 40) }}</span></td>
                                <td>{{ $quotation->country ?? '—' }}</td>
                                <td class="text-right font-mono whitespace-nowrap">{{ $quotation->currency->format($quotation->total_amount) }}</td>
                                <td class="text-right font-mono whitespace-nowrap">{{ $quotation->currency->format($quotation->amountPaid()) }}</td>
                                <td class="text-right font-mono whitespace-nowrap text-danger">{{ $quotation->currency->format($quotation->balance()) }}</td>
                                <td class="whitespace-nowrap">{{ $quotation->work_due_date?->format('j M Y') ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>
        <section class="card p-5" aria-labelledby="quoted-heading">
            <h2 id="quoted-heading" class="font-serif text-xl">Quoted in this period</h2>
            @if ($quoted->isEmpty())
                <p class="mt-3 py-2 text-sm text-muted">None.</p>
            @else
                <dl class="mt-3 divide-y divide-line-soft text-sm">
                    @foreach ($quoted as $code => $row)
                        <div class="flex justify-between gap-3 py-2"><dt>{{ $row['count'] }} {{ Str::plural('quotation', $row['count']) }}</dt><dd class="font-mono">{{ \App\Enums\Currency::from($code)->format($row['amount']) }}</dd></div>
                    @endforeach
                </dl>
            @endif
            <h2 class="mt-6 font-serif text-xl">Outstanding by currency</h2>
            @if ($outstandingByCurrency->isEmpty())
                <p class="mt-3 py-2 text-sm text-muted">None.</p>
            @else
                <dl class="mt-3 divide-y divide-line-soft text-sm">
                    @foreach ($outstandingByCurrency as $code => $row)
                        <div class="flex justify-between gap-3 py-2"><dt class="font-mono">{{ \App\Enums\Currency::from($code)->format($row['amount']) }}</dt><dd class="font-mono text-muted">{{ $row['rate'] ? '≈ '.$bdt($row['amount'] * $row['rate']) : 'rate unavailable' }}</dd></div>
                    @endforeach
                </dl>
            @endif
        </section>
    </div>

    <section class="card mt-6 overflow-x-auto" aria-labelledby="ledger-heading">
        <h2 id="ledger-heading" class="border-b border-line px-5 py-4 font-serif text-xl">Transactions</h2>
        @if ($ledger->isEmpty())
            <p class="p-5 text-sm text-muted">No payments in this period.</p>
        @else
            <table class="data-table">
                <thead><tr><th scope="col">Date</th><th scope="col">Submission</th><th scope="col">Country</th><th scope="col">Type</th><th scope="col" class="text-right">Amount</th><th scope="col" class="text-right">Rate</th><th scope="col" class="text-right">BDT</th><th scope="col">Method</th></tr></thead>
                <tbody>
                    @foreach ($ledger as $payment)
                        <tr>
                            <td class="whitespace-nowrap">{{ $payment->paid_on->format('j M Y') }}</td>
                            <td>@if ($payment->submission_id)<a href="{{ route('admin.submissions.show', $payment->submission_reference) }}#billing" class="font-mono">{{ $payment->submission_reference }}</a>@else<span class="font-mono text-muted" title="Submission deleted under the retention policy">{{ $payment->submission_reference }}</span>@endif</td>
                            <td>{{ $payment->country ?? '—' }}</td>
                            <td>{{ $payment->type->label() }}</td>
                            <td class="text-right font-mono whitespace-nowrap {{ $payment->type === \App\Enums\PaymentType::Refund ? 'text-danger' : '' }}">{{ $payment->type === \App\Enums\PaymentType::Refund ? '−' : '' }}{{ $payment->currency->format($payment->amount) }}</td>
                            <td class="text-right font-mono text-xs">{{ rtrim(rtrim($payment->rate_to_bdt, '0'), '.') }}</td>
                            <td class="text-right font-mono whitespace-nowrap">{{ $payment->type === \App\Enums\PaymentType::Refund ? '−' : '' }}{{ $bdt($payment->amount_bdt) }}</td>
                            <td>{{ $payment->method->label() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>
</x-layouts.admin>
