@props([
    'messages',
    'viewer',
    'perspective',
    'action',
    'attachmentUrl',
    'id' => 'chat',
    'title' => 'Messages',
    'intro' => null,
    'placeholder' => 'Write a message…',
])
{{--
    perspective: "student" or "team" see the other side only as "Zytecs Research";
    "admin" sees real names. Team members and students never see each other.
--}}
<section class="card scroll-mt-28 p-6" id="{{ $id }}" aria-labelledby="{{ $id }}-heading">
    <h2 id="{{ $id }}-heading" class="font-serif text-xl">{{ $title }}</h2>
    @if ($intro)<p class="mt-1 text-sm text-muted">{{ $intro }}</p>@endif

    <ol class="mt-5 flex max-h-[32rem] flex-col gap-3 overflow-y-auto rounded-md border border-line bg-paper p-4" aria-label="Message history">
        @forelse ($messages as $message)
            @php
                $mine = $message->sender_id === $viewer->id;
                $name = $mine ? 'You' : ($perspective === 'admin' ? ($message->sender?->name ?? 'Former user').($message->sender?->isAdmin() ? ' (team)' : '') : 'Zytecs Research');
            @endphp
            <li class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[85%] rounded-lg px-4 py-2.5 shadow-sm sm:max-w-[75%] {{ $mine ? 'bg-[#dcf2e3] text-ink' : 'bg-card text-ink' }}">
                    <p class="text-xs font-semibold {{ $mine ? 'text-success' : 'text-royal' }}">{{ $name }}</p>
                    @if ($message->body)
                        <p class="mt-1 text-[0.95rem] break-words whitespace-pre-line">{{ $message->body }}</p>
                    @endif
                    @if ($message->attachment_path)
                        <a href="{{ $attachmentUrl($message) }}" class="mt-2 flex items-center gap-2 rounded-md border border-line bg-paper px-3 py-2 text-sm no-underline">
                            <x-icon name="file-text" class="size-4 text-slate" />
                            <span class="min-w-0 break-all">{{ $message->attachment_name }}</span>
                            <span class="shrink-0 text-xs text-muted">{{ $message->humanAttachmentSize() }}</span>
                        </a>
                    @endif
                    <p class="mt-1 text-right text-[0.7rem] text-muted"><time datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->format('j M, H:i') }}</time></p>
                </div>
            </li>
        @empty
            <li class="py-6 text-center text-sm text-muted">No messages yet.</li>
        @endforelse
    </ol>

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="mt-4 grid gap-3" novalidate data-submit-once>
        @csrf
        <label for="{{ $id }}-body" class="sr-only">Your message</label>
        <textarea id="{{ $id }}-body" name="body" rows="3" maxlength="5000" class="input" placeholder="{{ $placeholder }}" @error('body') aria-invalid="true" aria-describedby="{{ $id }}-body-error" @enderror>{{ old('body') }}</textarea>
        <x-field-error name="body" :id="$id.'-body-error'" />
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
                <label for="{{ $id }}-attachment" class="text-sm font-medium">Attach a file <span class="text-muted">(optional)</span></label>
                <input id="{{ $id }}-attachment" name="attachment" type="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png" class="mt-1 block w-full text-sm file:mr-3 file:rounded-md file:border file:border-line file:bg-card file:px-3 file:py-1.5 file:text-sm">
                <x-field-error name="attachment" :id="$id.'-attachment-error'" />
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><x-icon name="arrow-up-right" class="size-4" /><span data-busy-label="Sending…">Send</span></button>
        </div>
    </form>
</section>
