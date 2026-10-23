@php $submission = $quotation->submission; @endphp
<x-documents.layout title="Invoice" :number="$quotation->invoice_number" :date="$quotation->invoiced_at ?? now()" stamp="PAID" :business="$business" :logo-path="$logoPath">
    @include('documents.partials.parties')
    @include('documents.partials.item')

    <table class="items" style="margin-top: 16px;">
        <thead><tr><th>Payments received</th><th>Method</th><th>Receipt</th><th class="num" style="width: 28%;">Amount ({{ $quotation->currency->value }})</th></tr></thead>
        <tbody>
            @foreach ($quotation->payments as $payment)
                <tr>
                    <td>{{ $payment->paid_on->format('j F Y') }}@if ($payment->type === \App\Enums\PaymentType::Refund) <span class="muted">(refund)</span>@endif</td>
                    <td>{{ $payment->method->label() }}</td>
                    <td>{{ $payment->receipt_number ?? '—' }}</td>
                    <td class="num">{{ $payment->type === \App\Enums\PaymentType::Refund ? '-' : '' }}{{ number_format((float) $payment->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Total price</td><td class="num">{{ $quotation->currency->format($quotation->total_amount) }}</td></tr>
        <tr><td>Total paid</td><td class="num">{{ $quotation->currency->format($quotation->amountPaid()) }}</td></tr>
        <tr class="grand"><td>Balance due</td><td class="num">{{ $quotation->currency->format($quotation->balance()) }}</td></tr>
    </table>

    <div class="note">Paid in full. Thank you.@if ($quotation->number) This invoice settles quotation {{ $quotation->number }}.@endif</div>

    <p class="muted" style="margin-top: 18px;">{{ $business['invoice_note'] }}</p>
</x-documents.layout>
