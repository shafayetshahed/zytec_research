@php $submission = $quotation->submission; @endphp
<x-documents.layout title="Payment receipt" :number="$payment->receipt_number" :date="$payment->paid_on" stamp="RECEIVED" :business="$business" :logo-path="$logoPath">
    @include('documents.partials.parties')

    <table class="items">
        <thead><tr><th>Payment</th><th>Method</th><th class="num" style="width: 28%;">Amount ({{ $payment->currency->value }})</th></tr></thead>
        <tbody>
            <tr>
                <td>
                    <strong>{{ $payment->paid_on->format('j F Y') }}</strong><br>
                    <span class="muted">For: {{ $submission->assignment_name }}@if ($submission->module_code) ({{ $submission->module_code }})@endif</span>
                    @if ($quotation->number)<br><span class="muted">Quotation {{ $quotation->number }}</span>@endif
                </td>
                <td>{{ $payment->method->label() }}@if ($payment->reference)<br><span class="muted">Ref: {{ $payment->reference }}</span>@endif</td>
                <td class="num"><strong>{{ number_format((float) $payment->amount, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    @php
        $paidToDate = round($quotation->payments->filter(fn ($p) => $p->paid_on->lt($payment->paid_on) || ($p->paid_on->eq($payment->paid_on) && $p->id <= $payment->id))->sum(fn ($p) => $p->signedAmount()), 2);
        $balanceAfter = max(0, round((float) $quotation->total_amount - $paidToDate, 2));
    @endphp
    <table class="totals">
        <tr><td>Total price</td><td class="num">{{ $quotation->currency->format($quotation->total_amount) }}</td></tr>
        <tr class="highlight"><td><strong>This payment</strong></td><td class="num"><strong>{{ $payment->currency->format($payment->amount) }}</strong></td></tr>
        <tr><td>Paid to date</td><td class="num">{{ $quotation->currency->format($paidToDate) }}</td></tr>
        <tr class="grand"><td>Balance remaining</td><td class="num">{{ $quotation->currency->format($balanceAfter) }}</td></tr>
    </table>

    <div class="note">
        @if ($balanceAfter <= 0)
            Your balance is fully cleared. Thank you.
        @elseif ($paidToDate >= (float) $quotation->deposit_amount)
            Your deposit has been received and your work can begin. The remaining balance is due before completion.
        @else
            Thank you for your payment. The deposit of {{ $quotation->currency->format($quotation->deposit_amount) }} is required before work begins.
        @endif
    </div>

    <p class="muted" style="margin-top: 18px;">{{ $business['invoice_note'] }}</p>
</x-documents.layout>
