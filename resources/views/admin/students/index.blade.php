<x-layouts.admin title="Students">
    <h1 class="font-serif text-3xl">Students</h1>
    <form method="GET" action="{{ route('admin.students.index') }}" class="card mt-6 flex flex-wrap items-end gap-4 p-5" role="search" aria-label="Search students">
        <div class="field min-w-64 flex-1">
            <label for="q" class="label">Search</label>
            <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" class="input" placeholder="Name, email or WhatsApp">
        </div>
        <div class="field">
            <label for="filter" class="label">Show</label>
            <select id="filter" name="filter" class="input">
                <option value="">All students</option>
                <option value="unverified" @selected(($filters['filter'] ?? '') === 'unverified')>Unverified email</option>
                <option value="suspended" @selected(($filters['filter'] ?? '') === 'suspended')>Suspended</option>
                <option value="deletion" @selected(($filters['filter'] ?? '') === 'deletion')>Deletion requested</option>
            </select>
        </div>
        <button type="submit" class="btn btn-ink btn-sm"><x-icon name="search" class="size-4" />Search</button>
    </form>

    <div class="card mt-6 overflow-x-auto">
        @if ($students->isEmpty())
            <p class="p-6 text-muted">No students found.</p>
        @else
            <table class="data-table">
                <caption class="sr-only">Registered students</caption>
                <thead><tr><th scope="col">Name</th><th scope="col">Email</th><th scope="col">WhatsApp</th><th scope="col">Registered</th><th scope="col">Submissions</th><th scope="col">Account</th></tr></thead>
                <tbody>
                    @foreach ($students as $student)
                        <tr>
                            <td><a href="{{ route('admin.students.show', $student) }}" class="font-medium">{{ $student->name }}</a></td>
                            <td class="break-all">{{ $student->email }} @if ($student->email_verified_at)<span class="badge bg-success-bg text-success">Verified</span>@else<span class="badge bg-warning-bg text-warning">Unverified</span>@endif</td>
                            <td class="font-mono whitespace-nowrap">{{ $student->whatsapp_number }}</td>
                            <td class="whitespace-nowrap">{{ $student->created_at->format('j M Y') }}</td>
                            <td class="font-mono">{{ $student->submissions_count }}</td>
                            <td>
                                <span class="badge {{ $student->isActive() ? 'bg-success-bg text-success' : 'bg-danger-bg text-danger' }}">{{ $student->status->label() }}</span>
                                @if ($student->deletion_requested_at)<span class="badge bg-warning-bg text-warning">Deletion requested</span>@endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    {{ $students->links() }}
</x-layouts.admin>
