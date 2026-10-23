<x-layouts.admin title="Team">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-serif text-3xl">Team</h1>
            <p class="mt-1 max-w-2xl text-muted">Team members prepare guidance and feedback materials for the orders you assign them. They never see a student's name or contact details, and students never see theirs. Everything they upload waits for your approval.</p>
        </div>
        <a href="{{ route('admin.team.create') }}" class="btn btn-primary btn-sm"><x-icon name="user" class="size-4" />Add team member</a>
    </div>

    @if ($awaitingReview > 0)
        <div class="alert-warning alert mt-6"><x-icon name="clock" class="mt-0.5 size-5" /><p>{{ $awaitingReview }} {{ Str::plural('upload', $awaitingReview) }} awaiting your review. <a href="{{ route('admin.submissions.index', ['materials' => 'pending']) }}">Show orders</a></p></div>
    @endif

    <div class="card mt-6 overflow-x-auto">
        @if ($members->isEmpty())
            <p class="p-6 text-muted">No team members yet.</p>
        @else
            <table class="data-table">
                <caption class="sr-only">Team members</caption>
                <thead><tr><th scope="col">Name</th><th scope="col">Email</th><th scope="col">WhatsApp</th><th scope="col">Open orders</th><th scope="col">Total orders</th><th scope="col">Account</th><th scope="col"><span class="sr-only">Actions</span></th></tr></thead>
                <tbody>
                    @foreach ($members as $member)
                        <tr>
                            <td class="font-semibold">{{ $member->name }}</td>
                            <td class="break-all">{{ $member->email }}</td>
                            <td class="font-mono whitespace-nowrap">{{ $member->whatsapp_number ?? '—' }}</td>
                            <td class="font-mono">{{ $member->open_orders_count }}</td>
                            <td class="font-mono">{{ $member->total_orders_count }}</td>
                            <td>
                                <span class="badge {{ $member->isActive() ? 'bg-success-bg text-success' : 'bg-danger-bg text-danger' }}">{{ $member->isActive() ? 'Active' : 'Suspended' }}</span>
                                <span class="block text-xs text-muted">{{ $member->last_login_at ? 'Last sign-in '.$member->last_login_at->format('j M Y') : 'Not signed in yet' }}</span>
                            </td>
                            <td class="whitespace-nowrap">
                                <div class="flex flex-wrap gap-2">
                                    <form method="POST" action="{{ route('admin.team.invite', $member) }}">@csrf<button type="submit" class="btn btn-outline btn-sm">Resend invite</button></form>
                                    @if ($member->isActive())
                                        <form method="POST" action="{{ route('admin.team.suspend', $member) }}" data-confirm="Stop {{ $member->name }} signing in?">@csrf<button type="submit" class="btn btn-outline btn-sm">Suspend</button></form>
                                    @else
                                        <form method="POST" action="{{ route('admin.team.reactivate', $member) }}">@csrf<button type="submit" class="btn btn-outline btn-sm">Reactivate</button></form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-layouts.admin>
