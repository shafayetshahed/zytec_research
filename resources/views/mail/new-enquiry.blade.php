<x-mail::message>
# New website enquiry

- **Type:** {{ $enquiry->enquiry_type->label() }}
- **Name:** {{ \App\Support\MarkdownText::escape($enquiry->name) }}
@if ($enquiry->email)
- **Email:** {{ \App\Support\MarkdownText::escape($enquiry->email) }}
@endif
@if ($enquiry->whatsapp_number)
- **WhatsApp:** {{ $enquiry->whatsapp_number }}
@endif
@if ($enquiry->country)
- **Country of study:** {{ $enquiry->country }}
@endif
@if ($enquiry->subject)
- **Subject:** {{ \App\Support\MarkdownText::escape($enquiry->subject) }}
@endif
- **Received:** {{ $enquiry->created_at->format('j F Y, H:i') }}

**Message:**

{{ \App\Support\MarkdownText::escape($enquiry->message) }}

<x-mail::button :url="$adminUrl">
Open in admin panel
</x-mail::button>
</x-mail::message>
