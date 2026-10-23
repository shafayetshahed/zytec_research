<x-mail::message>
# Payment received

Hello {{ \App\Support\MarkdownText::escape($submission->student->name) }},

We have received your payment of **{{ $payment->currency->format($payment->amount) }}** on {{ $payment->paid_on->format('j F Y') }} for **{{ \App\Support\MarkdownText::escape($submission->assignment_name) }}** ({{ $submission->reference }}).

<x-mail::panel>
**Total price:** {{ $quotation->currency->format($quotation->total_amount) }}
**Paid so far:** {{ $quotation->currency->format($quotation->amountPaid()) }}
**Balance due:** {{ $quotation->currency->format($quotation->balance()) }}
</x-mail::panel>

@if ($quotation->balance() <= 0)
Your balance is fully cleared. Thank you. Your receipt and final invoice are attached.
@else
Your receipt is attached. The remaining balance is due before your work is completed. Please use your payment reference **{{ $submission->reference }}** for the next payment and when sending a screenshot.
@endif

<x-mail::button :url="$portalUrl">
View in your portal
</x-mail::button>

Zytecs Research
</x-mail::message>
