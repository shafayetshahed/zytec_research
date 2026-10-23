<x-layouts.student :title="$submission->reference">
    @if ($justSubmitted)
        <div class="alert-success alert mb-8" role="status">
            <x-icon name="circle-check" class="mt-0.5 size-6" />
            <div>
                <p class="text-lg font-semibold">Your brief has been submitted.</p>
                <p class="mt-1">Reference <strong class="font-mono">{{ $submission->reference }}</strong>. We will review it and contact you on WhatsApp ({{ $submission->whatsapp_number }}) or by email. Keep the reference for any questions.</p>
            </div>
        </div>
    @endif

    <nav aria-label="Breadcrumb" class="text-sm"><a href="{{ route('student.dashboard') }}">Dashboard</a> <span class="text-muted">/</span> <span aria-current="page">{{ $submission->reference }}</span></nav>

    <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="font-mono text-sm text-muted">{{ $submission->reference }}</p>
            <h1 class="mt-1 font-serif text-3xl">{{ $submission->assignment_name }}</h1>
        </div>
        <x-status-badge :status="$submission->status" class="text-sm" />
    </div>
    <p class="mt-3 max-w-2xl text-ink-soft">{{ $submission->status->studentDescription() }}</p>

    <div class="mt-8 grid gap-8 lg:grid-cols-3">
        <section class="card p-6 lg:col-span-2" aria-labelledby="details-heading">
            <h2 id="details-heading" class="font-serif text-xl">Details</h2>
            <dl class="mt-4 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                <div><dt class="text-sm text-muted">Module code</dt><dd class="mt-1"><span class="code-tag">{{ $submission->module_code }}</span></dd></div>
                <div><dt class="text-sm text-muted">Submitted</dt><dd class="mt-1"><time datetime="{{ $submission->submitted_at->toIso8601String() }}">{{ $submission->submitted_at->format('j F Y, H:i') }} ({{ config('app.timezone') }})</time></dd></div>
                <div><dt class="text-sm text-muted">Subject area</dt><dd class="mt-1">{{ $submission->subject ?? 'Not given' }}</dd></div>
                <div><dt class="text-sm text-muted">Assessment type</dt><dd class="mt-1">{{ $submission->assessment_type ?? 'Not given' }}</dd></div>
                <div><dt class="text-sm text-muted">University or college</dt><dd class="mt-1">{{ $submission->institution ?? 'Not given' }}</dd></div>
                <div><dt class="text-sm text-muted">Country of study</dt><dd class="mt-1">{{ $submission->country ?? 'Not given' }}</dd></div>
                <div><dt class="text-sm text-muted">Deadline</dt><dd class="mt-1">{{ $submission->deadline?->format('j F Y') ?? 'Not given' }}</dd></div>
                <div><dt class="text-sm text-muted">Contact WhatsApp</dt><dd class="mt-1 font-mono">{{ $submission->whatsapp_number }}</dd></div>
                @if ($submission->description)
                    <div class="sm:col-span-2"><dt class="text-sm text-muted">What you asked for</dt><dd class="mt-1 whitespace-pre-line">{{ $submission->description }}</dd></div>
                @endif
            </dl>
        </section>

        <section class="card p-6" aria-labelledby="files-heading">
            <h2 id="files-heading" class="font-serif text-xl">Files</h2>
            <ul class="mt-4 space-y-3">
                @foreach ($submission->files as $file)
                    <li class="flex items-start gap-3">
                        <x-icon name="file-text" class="mt-0.5 size-5 text-slate" />
                        <div class="min-w-0">
                            <a href="{{ route('student.submissions.files.download', [$submission->reference, $file->id]) }}" class="font-medium break-all">{{ $file->original_name }}</a>
                            <p class="text-sm text-muted">{{ $file->is_brief ? 'Brief' : 'Supporting file' }} · {{ strtoupper($file->extension) }} · {{ $file->humanSize() }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>

    <p class="mt-10"><a href="{{ route('student.dashboard') }}" class="link-arrow"><x-icon name="arrow-right" class="size-4 rotate-180" />Back to dashboard</a></p>
</x-layouts.student>
