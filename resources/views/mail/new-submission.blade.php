<x-mail::message>
# New brief received

**Reference:** {{ $submission->reference }}
**Submitted:** {{ $submission->submitted_at->format('j F Y, H:i') }} ({{ config('app.timezone') }})

## Student

- **Name:** {{ \App\Support\MarkdownText::escape($submission->student->name) }}
- **Verified email:** {{ \App\Support\MarkdownText::escape($submission->contact_email) }}
- **WhatsApp:** {{ $submission->whatsapp_number }}

## Assignment

- **Assignment / project:** {{ \App\Support\MarkdownText::escape($submission->assignment_name) }}
- **Module code:** {{ \App\Support\MarkdownText::escape($submission->module_code) }}
@if ($submission->institution)
- **Institution:** {{ \App\Support\MarkdownText::escape($submission->institution) }}
@endif
@if ($submission->country)
- **Country of study:** {{ $submission->country }}
@endif
@if ($submission->subject)
- **Subject:** {{ $submission->subject }}
@endif
@if ($submission->assessment_type)
- **Assessment type:** {{ $submission->assessment_type }}
@endif
@if ($submission->deadline)
- **Deadline:** {{ $submission->deadline->format('j F Y') }}
@endif

@if ($submission->description)
**Description:**

{{ \App\Support\MarkdownText::escape($submission->description) }}
@endif

## Brief file

@if ($brief)
- **File:** {{ \App\Support\MarkdownText::escape($brief->original_name) }}
- **Type:** {{ $brief->mime_type }}
- **Size:** {{ $brief->humanSize() }}
@endif
- **Supporting files:** {{ $submission->files->where('is_brief', false)->count() }}

{{ $attached ? 'The brief is attached to this email. ' : '' }}Open the submission in the admin panel (sign-in required) to download files, add notes and update the status.

<x-mail::button :url="$adminUrl">
Open {{ $submission->reference }}
</x-mail::button>

This message was generated automatically by the Zytecs Research website.
</x-mail::message>
