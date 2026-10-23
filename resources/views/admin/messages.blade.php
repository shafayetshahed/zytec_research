<x-layouts.admin title="Messages">
    <h1 class="font-serif text-3xl">Messages</h1>
    <p class="mt-1 text-muted">Conversations with students and team members across all orders. Open an order to read and reply.</p>

    <div class="card mt-6 overflow-x-auto">
        @if ($conversations->isEmpty())
            <p class="p-6 text-muted">No messages yet.</p>
        @else
            <table class="data-table">
                <caption class="sr-only">Recent conversations</caption>
                <thead><tr><th scope="col">Order</th><th scope="col">With</th><th scope="col">Latest message</th><th scope="col">When</th><th scope="col"><span class="sr-only">Unread</span></th></tr></thead>
                <tbody>
                    @foreach ($conversations as $conversation)
                        @php $submission = $conversation['submission']; $isTeam = $conversation['channel'] === \App\Enums\MessageChannel::Team; @endphp
                        <tr class="{{ ($unread[$submission->id] ?? 0) > 0 ? 'bg-[#e8f0fb]/60' : '' }}">
                            <td><a href="{{ route('admin.submissions.show', $submission) }}#chat-{{ $conversation['channel']->value }}" class="font-mono">{{ $submission->reference }}</a><span class="block text-xs text-muted">{{ Str::limit($submission->assignment_name, 40) }}</span></td>
                            <td>{{ $isTeam ? ($submission->expert?->name ?? 'Team member').' (team)' : $submission->student->name }}</td>
                            <td class="max-w-md text-sm"><span class="font-medium">{{ $conversation['last']->sender?->name ?? 'Former user' }}:</span> {{ Str::limit($conversation['last']->body ?? '📎 '.$conversation['last']->attachment_name, 90) }}</td>
                            <td class="whitespace-nowrap text-xs">{{ $conversation['last']->created_at->diffForHumans() }}</td>
                            <td>@if (($unread[$submission->id] ?? 0) > 0)<span class="badge bg-royal text-white">{{ $unread[$submission->id] }} unread</span>@endif</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-layouts.admin>
