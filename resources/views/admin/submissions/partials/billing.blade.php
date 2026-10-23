@php
    $quotation = $submission->quotation;
    $sent = $quotation?->sent_at !== null;
@endphp
<section class="card min-w-0 p-6" aria-labelledby="billing-heading" id="billing">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 id="billing-heading" class="font-serif text-xl">Quotation and payments</h2>
        <x-payment-badge :status="$submission->payment_status" />
    </div>

    @if ($quotation)
        <dl class="mt-5 grid gap-px overflow-hidden rounded-md border border-line bg-line sm:grid-cols-4">
            <div class="bg-paper p-4"><dt class="text-xs text-muted uppercase">Total</dt><dd class="mt-1 font-mono text-lg font-medium">{{ $quotation->currency->format($quotation->total_amount) }}</dd></div>
            <div class="bg-paper p-4"><dt class="text-xs text-muted uppercase">Deposit</dt><dd class="mt-1 font-mono text-lg">{{ $quotation->currency->format($quotation->deposit_amount) }}</dd></div>
            <div class="bg-paper p-4"><dt class="text-xs text-muted uppercase">Paid</dt><dd class="mt-1 font-mono text-lg text-success">{{ $quotation->currency->format($quotation->amountPaid()) }}</dd></div>
            <div class="bg-paper p-4"><dt class="text-xs text-muted uppercase">Balance</dt><dd class="mt-1 font-mono text-lg {{ $quotation->balance() > 0 ? 'text-danger' : 'text-success' }}">{{ $quotation->currency->format($quotation->balance()) }}</dd></div>
        </dl>
        <p class="mt-2 text-sm text-muted">
            @if ($sent)
                Sent to the student {{ $quotation->sent_at->format('j M Y H:i') }}.
            @else
                <strong class="text-warning">Draft</strong>: not yet visible to the student.
            @endif
            @if ($quotation->work_due_date) Delivery date {{ $quotation->work_due_date->format('j M Y') }}. @endif
            @if ($quotation->country) Country: {{ $quotation->country }}. @endif
        </p>
    @endif

    <details class="mt-5 rounded-md border border-line" @if (! $quotation || $errors->hasAny(['currency', 'total_amount', 'deposit_amount', 'work_due_date', 'message'])) open @endif>
        <summary class="cursor-pointer px-4 py-3 font-semibold">{{ $quotation ? 'Edit quotation' : 'Create quotation' }}</summary>
        <form method="POST" action="{{ route('admin.submissions.quotation', $submission) }}" class="grid gap-4 border-t border-line p-4 sm:grid-cols-2" novalidate>
            @csrf
            @method('PUT')
            <x-select-field name="currency" label="Currency" :options="collect($currencies)->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all()" :value="$quotation?->currency->value" />
            <x-select-field name="country" label="Country (for accounting)" :options="$countries" :value="$quotation?->country ?? $submission->country" optional />
            <x-text-field name="total_amount" label="Total price" type="number" step="0.01" min="0.01" inputmode="decimal" :value="$quotation?->total_amount" />
            <x-text-field name="deposit_amount" label="Deposit to start" type="number" step="0.01" min="0" inputmode="decimal" :value="$quotation?->deposit_amount ?? '0'" hint="Use 0 if no deposit is needed." />
            <x-text-field name="work_due_date" label="Delivery date" type="date" optional :value="$quotation?->work_due_date?->toDateString() ?? $submission->deadline?->toDateString()" />
            <div class="field sm:col-span-2">
                <label for="message" class="label">Message to the student<span class="label-optional">(optional, shown in the email and portal)</span></label>
                <textarea id="message" name="message" rows="3" maxlength="3000" class="input" placeholder="For example: payment details, what is included, or how to confirm.">{{ old('message', $quotation?->message) }}</textarea>
                <x-field-error name="message" />
            </div>
            <div class="flex flex-wrap gap-3 sm:col-span-2">
                <button type="submit" name="action" value="send" class="btn btn-primary btn-sm"><x-icon name="mail" class="size-4" />{{ $sent ? 'Save and re-send to student' : 'Save and send to student' }}</button>
                <button type="submit" name="action" value="save" class="btn btn-outline btn-sm">Save as draft</button>
            </div>
        </form>
    </details>

    @if ($sent)
        <h3 class="mt-6 font-sans text-sm font-semibold">Payments</h3>
        @if ($quotation->payments->isEmpty())
            <p class="mt-2 text-sm text-muted">No payments recorded yet.</p>
        @else
            <div class="mt-2 overflow-x-auto">
                <table class="data-table">
                    <thead><tr><th scope="col">Date</th><th scope="col">Type</th><th scope="col">Amount</th><th scope="col">Rate</th><th scope="col">BDT</th><th scope="col">Method</th><th scope="col"><span class="sr-only">Actions</span></th></tr></thead>
                    <tbody>
                        @foreach ($quotation->payments as $payment)
                            <tr>
                                <td class="whitespace-nowrap">{{ $payment->paid_on->format('j M Y') }}</td>
                                <td>{{ $payment->type->label() }}</td>
                                <td class="font-mono whitespace-nowrap {{ $payment->type === \App\Enums\PaymentType::Refund ? 'text-danger' : '' }}">{{ $payment->type === \App\Enums\PaymentType::Refund ? '−' : '' }}{{ $payment->currency->format($payment->amount) }}</td>
                                <td class="font-mono text-xs">{{ rtrim(rtrim($payment->rate_to_bdt, '0'), '.') }} <span class="text-muted">({{ $payment->rate_source }})</span></td>
                                <td class="font-mono whitespace-nowrap">BDT {{ number_format((float) $payment->amount_bdt, 2) }}</td>
                                <td>{{ $payment->method->label() }}@if ($payment->reference)<br><span class="text-xs text-muted">{{ $payment->reference }}</span>@endif</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.submissions.payments.destroy', [$submission, $payment]) }}" data-confirm="Delete this payment record?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-danger underline underline-offset-4">Delete<span class="sr-only"> payment of {{ $payment->amount }}</span></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <details class="mt-4 rounded-md border border-line" @if ($errors->hasAny(['amount', 'paid_on', 'method', 'rate_to_bdt', 'type'])) open @endif>
            <summary class="cursor-pointer px-4 py-3 font-semibold">Record a payment or refund</summary>
            <form method="POST" action="{{ route('admin.submissions.payments.store', $submission) }}" class="grid gap-4 border-t border-line p-4 sm:grid-cols-2" novalidate>
                @csrf
                <x-select-field name="type" label="Type" :options="['payment' => 'Payment received', 'refund' => 'Refund given']" value="payment" />
                <x-text-field name="amount" :label="'Amount ('.$quotation->currency->value.')'" type="number" step="0.01" min="0.01" inputmode="decimal" :value="$quotation->balance() > 0 ? number_format($quotation->balance(), 2, '.', '') : null" />
                <x-text-field name="paid_on" label="Date received" type="date" :value="now()->toDateString()" :max="now()->toDateString()" />
                <x-select-field name="method" label="Method" :options="collect($methods)->mapWithKeys(fn ($m) => [$m->value => $m->label()])->all()" />
                <x-text-field name="reference" label="Transaction reference" optional maxlength="120" />
                @if ($quotation->currency !== \App\Enums\Currency::BDT)
                    <x-text-field name="rate_to_bdt" :label="'Rate (BDT per 1 '.$quotation->currency->value.')'" type="number" step="0.000001" min="0" inputmode="decimal" optional hint="Leave blank to use the market rate for that date, or enter the rate you actually received." />
                @endif
                <x-text-field name="note" label="Internal note" optional maxlength="2000" class="sm:col-span-2" />
                <label class="flex items-start gap-3 text-sm text-ink-soft sm:col-span-2">
                    <input type="hidden" name="notify_student" value="0">
                    <input type="checkbox" name="notify_student" value="1" class="checkbox" checked>
                    <span>Email the student a payment receipt (payments only)</span>
                </label>
                <div class="sm:col-span-2"><button type="submit" class="btn btn-ink btn-sm">Record</button></div>
            </form>
        </details>
    @endif
</section>
