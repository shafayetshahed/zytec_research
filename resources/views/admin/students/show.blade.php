<x-layouts.admin :title="$student->name">
    <nav aria-label="Breadcrumb" class="text-sm"><a href="{{ route('admin.students.index') }}">Students</a> <span class="text-muted">/</span> <span aria-current="page">{{ $student->name }}</span></nav>
    <div class="mt-3 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-serif text-3xl">{{ $student->name }}</h1>
            @if ($student->isManualClient())<p class="mt-1 text-sm text-muted">Client added by an administrator{{ $student->email_verified_at ? ', now using the portal' : '' }}.</p>@endif
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.clients.edit', $student) }}" class="btn btn-outline btn-sm">Edit details</a>
            <a href="{{ route('admin.orders.create', ['client' => $student->id]) }}" class="btn btn-primary btn-sm"><x-icon name="file-text" class="size-4" />New manual order</a>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <section class="card p-6 xl:col-span-2" aria-labelledby="profile-heading">
            <h2 id="profile-heading" class="font-serif text-xl">Profile</h2>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div><dt class="text-sm text-muted">Email</dt><dd class="mt-1 break-all">{{ $student->email ?? 'None' }}</dd></div>
                <div><dt class="text-sm text-muted">Portal access</dt><dd class="mt-1">{{ $student->email_verified_at ? 'Verified '.$student->email_verified_at->format('j M Y') : ($student->email ? 'Not signed up yet' : 'No email, no portal access') }}</dd></div>
                <div><dt class="text-sm text-muted">WhatsApp</dt><dd class="mt-1 font-mono">{{ $student->whatsapp_number ?? 'None' }}</dd></div>
                <div><dt class="text-sm text-muted">Country</dt><dd class="mt-1">{{ $student->country ?? 'Not set' }}</dd></div>
                <div><dt class="text-sm text-muted">Registered</dt><dd class="mt-1">{{ $student->created_at->format('j M Y H:i') }}</dd></div>
                <div><dt class="text-sm text-muted">Last sign-in</dt><dd class="mt-1">{{ $student->last_login_at?->format('j M Y H:i') ?? 'Never' }}</dd></div>
                <div><dt class="text-sm text-muted">Account status</dt><dd class="mt-1"><span class="badge {{ $student->isActive() ? 'bg-success-bg text-success' : 'bg-danger-bg text-danger' }}">{{ $student->status->label() }}</span></dd></div>
            </dl>

            <h2 class="mt-8 font-serif text-xl">Orders</h2>
            <ul class="mt-3 divide-y divide-line-soft">
                @forelse ($submissions as $submission)
                    <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                        <span><a href="{{ route('admin.submissions.show', $submission) }}" class="font-mono">{{ $submission->reference }}</a> <span class="ml-2">{{ $submission->assignment_name }}</span>@if ($submission->module_code) <span class="code-tag ml-1">{{ $submission->module_code }}</span>@endif <span class="badge ml-1 bg-paper-deep text-ink">{{ $submission->source->label() }}</span></span>
                        <span class="flex flex-wrap items-center gap-2">
                            @if ($submission->quotation?->sent_at)<span class="font-mono text-xs {{ $submission->quotation->balance() > 0 ? 'text-danger' : 'text-success' }}">{{ $submission->quotation->balance() > 0 ? $submission->quotation->currency->format($submission->quotation->balance()).' due' : 'Paid' }}</span>@endif
                            <x-status-badge :status="$submission->status" />
                        </span>
                    </li>
                @empty
                    <li class="py-3 text-muted">No submissions.</li>
                @endforelse
            </ul>
        </section>

        <section class="card grid content-start gap-5 p-6" aria-labelledby="actions-heading">
            <h2 id="actions-heading" class="font-serif text-xl">Account actions</h2>
            @if ($student->isActive())
                <form method="POST" action="{{ route('admin.students.suspend', $student) }}" data-confirm="Suspend this account? The student will be signed out immediately.">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm"><x-icon name="user-x" class="size-4" />Suspend account</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.students.reactivate', $student) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm"><x-icon name="user-check" class="size-4" />Reactivate account</button>
                </form>
            @endif

            <div class="border-t border-line pt-5">
                <h3 class="font-sans text-sm font-semibold text-danger">Permanently delete</h3>
                @if ($student->deletion_requested_at)
                    <p class="mt-1 text-sm">The student requested deletion on {{ $student->deletion_requested_at->format('j M Y') }}.</p>
                @endif
                <p class="mt-1 text-sm text-muted">Deletes the account, all submissions, notes and stored files. This cannot be undone. Check the retention policy before deleting.</p>
                <form method="POST" action="{{ route('admin.students.destroy', $student) }}" class="mt-3 grid gap-3" data-confirm="Permanently delete this student and all their files?">
                    @csrf
                    @method('DELETE')
                    <x-text-field name="confirm_email" :label="$student->email ? 'Type the student\'s email to confirm' : 'Type the client\'s name to confirm'" autocomplete="off" />
                    <div><button type="submit" class="btn btn-danger btn-sm"><x-icon name="trash-2" class="size-4" />Delete permanently</button></div>
                </form>
            </div>
        </section>
    </div>
</x-layouts.admin>
