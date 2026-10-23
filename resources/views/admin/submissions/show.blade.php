<x-layouts.admin :title="$submission->reference">
    <nav aria-label="Breadcrumb" class="text-sm"><a href="{{ route('admin.submissions.index') }}">Submissions</a> <span class="text-muted">/</span> <span aria-current="page" class="font-mono">{{ $submission->reference }}</span></nav>
    <div class="mt-3 flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="font-mono text-sm text-muted">{{ $submission->reference }}</p>
            <h1 class="mt-1 font-serif text-3xl">{{ $submission->assignment_name }}</h1>
            <p class="mt-2">@if ($submission->module_code)<span class="code-tag">{{ $submission->module_code }}</span> @endif<span class="badge bg-paper-deep text-ink">{{ $submission->source->label() }}</span> <span class="ml-2 text-sm text-muted">{{ $submission->source === \App\Enums\OrderSource::Portal ? 'Submitted' : 'Ordered' }} {{ $submission->submitted_at->format('j F Y, H:i') }}</span></p>
        </div>
        <x-status-badge :status="$submission->status" class="text-sm" />
    </div>

    <div class="mt-8 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="grid min-w-0 content-start gap-6 xl:col-span-2">
            <section class="card p-6" aria-labelledby="student-heading">
                <h2 id="student-heading" class="font-serif text-xl">Student</h2>
                <dl class="mt-4 grid gap-4 sm:grid-cols-3">
                    <div><dt class="text-sm text-muted">Name</dt><dd class="mt-1"><a href="{{ route('admin.students.show', $submission->student) }}">{{ $submission->student->name }}</a></dd></div>
                    <div><dt class="text-sm text-muted">Email</dt><dd class="mt-1 break-all">@if ($submission->contact_email)<a href="mailto:{{ $submission->contact_email }}">{{ $submission->contact_email }}</a>@else<span class="text-muted">None</span>@endif</dd></div>
                    <div><dt class="text-sm text-muted">WhatsApp</dt><dd class="mt-1 font-mono">@if ($submission->whatsapp_number)<a href="https://wa.me/{{ ltrim($submission->whatsapp_number, '+') }}" rel="noopener noreferrer" target="_blank">{{ $submission->whatsapp_number }}<span class="sr-only"> (opens WhatsApp in a new tab)</span></a>@else<span class="font-sans text-muted">None</span>@endif</dd></div>
                </dl>
            </section>

            <section class="card p-6" aria-labelledby="brief-heading">
                <h2 id="brief-heading" class="font-serif text-xl">Brief details</h2>
                <dl class="mt-4 grid gap-4 sm:grid-cols-3">
                    <div><dt class="text-sm text-muted">Institution</dt><dd class="mt-1">{{ $submission->institution ?? '—' }}</dd></div>
                    <div><dt class="text-sm text-muted">Country</dt><dd class="mt-1">{{ $submission->country ?? '—' }}</dd></div>
                    <div><dt class="text-sm text-muted">Deadline</dt><dd class="mt-1">{{ $submission->deadline?->format('j F Y') ?? '—' }}</dd></div>
                    <div><dt class="text-sm text-muted">Subject</dt><dd class="mt-1">{{ $submission->subject ?? '—' }}</dd></div>
                    <div><dt class="text-sm text-muted">Assessment type</dt><dd class="mt-1">{{ $submission->assessment_type ?? '—' }}</dd></div>
                    <div class="sm:col-span-3"><dt class="text-sm text-muted">Description</dt><dd class="mt-1 whitespace-pre-line">{{ $submission->description ?? '—' }}</dd></div>
                </dl>
                <h3 class="mt-6 font-sans text-sm font-semibold">Files</h3>
                <ul class="mt-3 divide-y divide-line-soft rounded-md border border-line">
                    @foreach ($submission->files as $file)
                        <li class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                            <div class="min-w-0">
                                <p class="font-medium break-all">{{ $file->original_name }}</p>
                                <p class="text-xs text-muted">{{ $file->is_brief ? 'Brief' : 'Supporting' }} · {{ $file->mime_type }} · {{ $file->humanSize() }} · SHA-256 <span class="font-mono">{{ Str::limit($file->sha256, 12, '…') }}</span></p>
                            </div>
                            <a href="{{ route('admin.submissions.files.download', [$submission, $file]) }}" class="btn btn-outline btn-sm"><x-icon name="download" class="size-4" />Download</a>
                        </li>
                    @endforeach
                </ul>
            </section>

            @include('admin.submissions.partials.billing')

            <section class="card p-6" aria-labelledby="notes-heading">
                <h2 id="notes-heading" class="font-serif text-xl">Internal notes <span class="ml-2 align-middle text-xs font-sans font-semibold tracking-wide text-muted uppercase">Admin only</span></h2>
                <form method="POST" action="{{ route('admin.submissions.notes', $submission) }}" class="mt-4 grid gap-3">
                    @csrf
                    <label for="body" class="sr-only">New note</label>
                    <textarea id="body" name="body" rows="3" maxlength="5000" class="input" placeholder="Add a note for the team" required @error('body') aria-invalid="true" aria-describedby="body-error" @enderror>{{ old('body') }}</textarea>
                    <x-field-error name="body" />
                    <div><button type="submit" class="btn btn-ink btn-sm">Add note</button></div>
                </form>
                <ol class="mt-6 space-y-4">
                    @forelse ($submission->notes as $note)
                        <li class="border-l-2 border-bronze/50 pl-4">
                            <p class="whitespace-pre-line">{{ $note->body }}</p>
                            <p class="mt-1 text-xs text-muted">{{ $note->admin?->name ?? 'Former administrator' }} · {{ $note->created_at->format('j M Y H:i') }}</p>
                        </li>
                    @empty
                        <li class="text-sm text-muted">No notes yet.</li>
                    @endforelse
                </ol>
            </section>
        </div>

        <div class="grid min-w-0 content-start gap-6">
            <section class="card p-6" aria-labelledby="status-form-heading">
                <h2 id="status-form-heading" class="font-serif text-xl">Update status</h2>
                <form method="POST" action="{{ route('admin.submissions.status', $submission) }}" class="mt-4 grid gap-4">
                    @csrf
                    @method('PUT')
                    <div class="field">
                        <label for="new-status" class="label">Status</label>
                        <select id="new-status" name="status" class="input">
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}" @selected($submission->status === $status)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        <p class="hint">Sending a quotation and recording the deposit update the status automatically. "Completed" is only allowed once the balance is paid.</p>
                    </div>
                    <div class="field">
                        <label for="status-note" class="label">Note<span class="label-optional">(optional, internal)</span></label>
                        <textarea id="status-note" name="note" rows="2" maxlength="5000" class="input"></textarea>
                    </div>
                    <div><button type="submit" class="btn btn-primary btn-sm">Save status</button></div>
                </form>
                @if (! $submission->status->isFinal())
                    <form method="POST" action="{{ route('admin.submissions.status', $submission) }}" class="mt-3" data-confirm="Close this submission?">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="closed">
                        <button type="submit" class="text-sm font-medium text-muted underline underline-offset-4">Close submission</button>
                    </form>
                @endif
            </section>

            <section class="card p-6" aria-labelledby="notify-heading">
                <h2 id="notify-heading" class="font-serif text-xl">Email notification</h2>
                <ul class="mt-4 space-y-3">
                    @forelse ($submission->notificationLogs as $log)
                        <li class="text-sm">
                            <div class="flex flex-wrap items-center gap-2"><x-status-badge :status="$log->status" /><span class="text-muted">to {{ $log->recipient }}</span></div>
                            <p class="mt-1 text-xs text-muted">Attempts: {{ $log->attempts }}@if ($log->sent_at) · sent {{ $log->sent_at->format('j M Y H:i') }}@elseif ($log->last_attempt_at) · last tried {{ $log->last_attempt_at->format('j M Y H:i') }}@endif</p>
                            @if ($log->error_summary)<p class="mt-1 text-xs text-danger">{{ $log->error_summary }}</p>@endif
                            @if ($log->status !== \App\Enums\NotificationStatus::Sent)
                                <form method="POST" action="{{ route('admin.submissions.notifications.retry', [$submission, $log]) }}" class="mt-2">
                                    @csrf
                                    <button type="submit" class="btn btn-outline btn-sm"><x-icon name="refresh-cw" class="size-4" />Retry now</button>
                                </form>
                            @endif
                        </li>
                    @empty
                        <li class="text-sm text-muted">No notification recorded.</li>
                    @endforelse
                </ul>
            </section>

            <section class="card p-6" aria-labelledby="history-heading">
                <h2 id="history-heading" class="font-serif text-xl">Status history</h2>
                <ol class="mt-4 space-y-3 text-sm">
                    @foreach ($submission->statusChanges as $change)
                        <li>
                            <p>{{ $change->from_status ? $change->from_status->label().' → ' : '' }}<strong>{{ $change->to_status->label() }}</strong></p>
                            <p class="text-xs text-muted">{{ $change->admin?->name ?? ($change->from_status ? 'Former administrator' : 'Student submission') }} · {{ $change->created_at->format('j M Y H:i') }}</p>
                        </li>
                    @endforeach
                </ol>
            </section>

            <section class="card p-6" aria-labelledby="others-heading">
                <h2 id="others-heading" class="font-serif text-xl">Other submissions from this student</h2>
                <ul class="mt-4 space-y-2 text-sm">
                    @forelse ($history as $other)
                        <li class="flex items-center justify-between gap-2"><a href="{{ route('admin.submissions.show', $other) }}" class="font-mono">{{ $other->reference }}</a><x-status-badge :status="$other->status" /></li>
                    @empty
                        <li class="text-muted">None.</li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>
</x-layouts.admin>
