<x-layouts.admin :title="$student->name">
    <nav aria-label="Breadcrumb" class="text-sm"><a href="{{ route('admin.students.index') }}">Students</a> <span class="text-muted">/</span> <span aria-current="page">{{ $student->name }}</span></nav>
    <h1 class="mt-3 font-serif text-3xl">{{ $student->name }}</h1>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <section class="card p-6 xl:col-span-2" aria-labelledby="profile-heading">
            <h2 id="profile-heading" class="font-serif text-xl">Profile</h2>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div><dt class="text-sm text-muted">Email</dt><dd class="mt-1 break-all">{{ $student->email }}</dd></div>
                <div><dt class="text-sm text-muted">Email verified</dt><dd class="mt-1">{{ $student->email_verified_at?->format('j M Y H:i') ?? 'Not verified' }}</dd></div>
                <div><dt class="text-sm text-muted">WhatsApp</dt><dd class="mt-1 font-mono">{{ $student->whatsapp_number }}</dd></div>
                <div><dt class="text-sm text-muted">Registered</dt><dd class="mt-1">{{ $student->created_at->format('j M Y H:i') }}</dd></div>
                <div><dt class="text-sm text-muted">Last sign-in</dt><dd class="mt-1">{{ $student->last_login_at?->format('j M Y H:i') ?? 'Never' }}</dd></div>
                <div><dt class="text-sm text-muted">Account status</dt><dd class="mt-1"><span class="badge {{ $student->isActive() ? 'bg-success-bg text-success' : 'bg-danger-bg text-danger' }}">{{ $student->status->label() }}</span></dd></div>
            </dl>

            <h2 class="mt-8 font-serif text-xl">Submissions</h2>
            <ul class="mt-3 divide-y divide-line-soft">
                @forelse ($submissions as $submission)
                    <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                        <span><a href="{{ route('admin.submissions.show', $submission) }}" class="font-mono">{{ $submission->reference }}</a> <span class="ml-2">{{ $submission->assignment_name }}</span> <span class="code-tag ml-1">{{ $submission->module_code }}</span></span>
                        <x-status-badge :status="$submission->status" />
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
                    <x-text-field name="confirm_email" label="Type the student's email to confirm" type="email" autocomplete="off" />
                    <div><button type="submit" class="btn btn-danger btn-sm"><x-icon name="trash-2" class="size-4" />Delete permanently</button></div>
                </form>
            </div>
        </section>
    </div>
</x-layouts.admin>
