<x-mail::message>
# Your quotation

Hello {{ \App\Support\MarkdownText::escape($submission->student->name) }},

Thank you for your request. We have reviewed it and here is our quotation.

<x-mail::panel>
**{{ \App\Support\MarkdownText::escape($submission->assignment_name) }}** ({{ \App\Support\MarkdownText::escape($submission->module_code) }})

**Total price:** {{ $quotation->currency->format($quotation->total_amount) }}
@if ((float) $quotation->deposit_amount > 0)
**Deposit to start:** {{ $quotation->currency->format($quotation->deposit_amount) }}
@endif
@if ($quotation->work_due_date)
**Delivery date:** {{ $quotation->work_due_date->format('j F Y') }}
@endif
</x-mail::panel>

## Your payment reference: {{ $submission->reference }}

@foreach (\App\Support\PaymentReference::instructions($submission->reference) as $line)
- {{ $line }}
@endforeach

This helps us match your payment to your order quickly.

@if ($quotation->message)
{{ \App\Support\MarkdownText::escape($quotation->message) }}
@endif

The quotation is attached as a PDF. We will confirm with you on WhatsApp before we start. Work begins once the deposit has been received, and the balance is due before completion.

<x-mail::button :url="$portalUrl">
View your quotation
</x-mail::button>

If you have any questions, reply to this email or message us on WhatsApp.

Zytecs Research
</x-mail::message>
