<x-mail::message>
# {{ $headline }}

{{ \App\Support\MarkdownText::escape($body) }}

<x-mail::button :url="$url">
{{ $button }}
</x-mail::button>

For your privacy, we do not include message contents or files in email.

Zytecs Research
</x-mail::message>
