<x-mail::message>
# Verify your email address

Hello {{ \App\Support\MarkdownText::escape($recipientName) }},

Use this code to verify your Zytecs Research student account:

<x-mail::panel>
<span style="font-size: 28px; font-weight: 700; letter-spacing: 8px; font-family: monospace;">{{ $code }}</span>
</x-mail::panel>

The code expires in {{ $expiresMinutes }} minutes and can only be used once. If you request a new code, this one will stop working.

If you did not create an account, you can ignore this email. We will never ask you for this code by phone or WhatsApp.

Zytecs Research
</x-mail::message>
