@php $submission = $quotation->submission; @endphp
<x-documents.layout title="Quotation" :number="$quotation->number" :date="$quotation->sent_at ?? now()" :business="$business" :logo-path="$logoPath">
    @include('documents.partials.parties')
    @include('documents.partials.item')

    <table class="totals">
        <tr><td>Total price</td><td class="num">{{ $quotation->currency->format($quotation->total_amount) }}</td></tr>
        <tr class="highlight"><td><strong>Deposit to start</strong></td><td class="num"><strong>{{ $quotation->currency->format($quotation->deposit_amount) }}</strong></td></tr>
        @if ($quotation->amountPaid() > 0)
            <tr><td>Paid to date</td><td class="num">{{ $quotation->currency->format($quotation->amountPaid()) }}</td></tr>
        @endif
        <tr class="grand"><td>Balance due</td><td class="num">{{ $quotation->currency->format($quotation->balance()) }}</td></tr>
    </table>

    <table style="margin-top: 22px; border: 2px solid #0a4da6; background: #e8f0fb;">
        <tr>
            <td style="padding: 10px 14px; width: 38%; vertical-align: middle;">
                <div class="label" style="color: #0a4da6;">Payment reference</div>
                <div style="font-size: 20px; font-weight: bold; letter-spacing: 1px;">{{ $submission->reference }}</div>
            </td>
            <td style="padding: 10px 14px; vertical-align: middle; font-size: 10px;">
                @foreach (\App\Support\PaymentReference::instructions($submission->reference) as $line)
                    &bull; {!! str_replace(e($submission->reference), '<strong style="white-space: nowrap;">'.e($submission->reference).'</strong>', e($line)) !!}<br>
                @endforeach
            </td>
        </tr>
    </table>

    <div class="note">
        <div class="label">Payment terms</div>
        Work begins once the deposit has been received. The remaining balance is due before completion. We will confirm payment details with you on WhatsApp or by email.
        @if ($quotation->message)
            <br><br>{!! nl2br(e($quotation->message)) !!}
        @endif
    </div>

    <p class="muted" style="margin-top: 18px;">{{ $business['invoice_note'] }}</p>
</x-documents.layout>
