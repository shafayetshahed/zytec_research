<x-layouts.team :title="$order->reference">
    <nav aria-label="Breadcrumb" class="text-sm"><a href="{{ route('team.dashboard') }}">Assigned orders</a> <span class="text-muted">/</span> <span aria-current="page" class="font-mono">{{ $order->reference }}</span></nav>
    <h1 class="mt-3 font-serif text-3xl">{{ $order->assignment_name }}</h1>
    <p class="mt-2 flex flex-wrap items-center gap-2 text-sm text-muted">
        @if ($order->module_code)<span class="code-tag">{{ $order->module_code }}</span>@endif
        <span>Delivery {{ ($order->quotation?->work_due_date ?? $order->deadline)?->format('j F Y') ?? 'not set' }}</span>
    </p>

    <div class="mt-8 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="grid min-w-0 content-start gap-6 xl:col-span-2">
            <section class="card p-6" aria-labelledby="instructions-heading">
                <h2 id="instructions-heading" class="font-serif text-xl">Instructions from Zytecs Research</h2>
                <p class="mt-3 whitespace-pre-line text-ink-soft">{{ $order->expert_instructions ?: 'No specific instructions. Follow the brief below and ask in Messages if anything is unclear.' }}</p>
            </section>

            <section class="card p-6" aria-labelledby="brief-heading">
                <h2 id="brief-heading" class="font-serif text-xl">The brief</h2>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div><dt class="text-sm text-muted">Subject</dt><dd class="mt-1">{{ $order->subject ?? '—' }}</dd></div>
                    <div><dt class="text-sm text-muted">Assessment type</dt><dd class="mt-1">{{ $order->assessment_type ?? '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-sm text-muted">What the student asked for</dt><dd class="mt-1 whitespace-pre-line">{{ $order->description ?? '—' }}</dd></div>
                </dl>
                <h3 class="mt-6 font-sans text-sm font-semibold">Files</h3>
                <ul class="mt-2 space-y-2">
                    @forelse ($order->files as $file)
                        <li><a href="{{ route('team.orders.files.download', [$order->reference, $file->id]) }}">{{ $file->original_name }}</a> <span class="text-xs text-muted">{{ $file->humanSize() }}</span></li>
                    @empty
                        <li class="text-sm text-muted">No files. Check the instructions or ask in Messages.</li>
                    @endforelse
                </ul>
            </section>

            <section class="card p-6" aria-labelledby="upload-heading">
                <h2 id="upload-heading" class="font-serif text-xl">Upload guidance and feedback</h2>
                <p class="mt-1 text-sm text-muted">For example: step-by-step process notes, suggestions, a structure outline, or feedback on the student's draft. An administrator reviews every upload before the student sees it.</p>
                <div class="mt-4"><x-error-summary /></div>
                <form method="POST" action="{{ route('team.orders.materials.store', $order->reference) }}" enctype="multipart/form-data" class="mt-4 grid gap-4" novalidate data-submit-once>
                    @csrf
                    <div class="field">
                        <label for="material" class="label">File</label>
                        <input id="material" name="material" type="file" required accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png" class="block w-full text-sm file:mr-4 file:rounded-md file:border-0 file:bg-ink file:px-4 file:py-2 file:font-semibold file:text-white">
                        <x-field-error name="material" />
                    </div>
                    <div class="field">
                        <label for="note" class="label">Note for the reviewer<span class="label-optional">(optional)</span></label>
                        <textarea id="note" name="note" rows="2" maxlength="3000" class="input">{{ old('note') }}</textarea>
                    </div>
                    <label class="flex items-start gap-3 text-sm text-ink-soft">
                        <input type="checkbox" name="confirm_guidance" value="1" class="checkbox" required @checked(old('confirm_guidance'))>
                        <span>This file is guidance or feedback to help the student learn and improve their own work. It is not a finished assignment for submission.</span>
                    </label>
                    <x-field-error name="confirm_guidance" />
                    <div><button type="submit" class="btn btn-primary btn-sm"><x-icon name="upload" class="size-4" /><span data-busy-label="Uploading…">Upload for review</span></button></div>
                </form>

                @php $mine = $order->deliverables->where('uploaded_by', auth()->id()); @endphp
                @if ($mine->isNotEmpty())
                    <h3 class="mt-8 font-sans text-sm font-semibold">Your uploads</h3>
                    <ul class="mt-2 divide-y divide-line-soft rounded-md border border-line">
                        @foreach ($mine as $deliverable)
                            <li class="px-4 py-3">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <a href="{{ route('team.orders.materials.download', [$order->reference, $deliverable->id]) }}" class="font-medium break-all">{{ $deliverable->original_name }}</a>
                                    <x-deliverable-badge :status="$deliverable->status" />
                                </div>
                                <p class="text-xs text-muted">Uploaded {{ $deliverable->created_at->format('j M Y H:i') }}@if ($deliverable->released_at) · shared with the student {{ $deliverable->released_at->format('j M Y') }}@endif</p>
                                @if ($deliverable->review_note)
                                    <p class="mt-2 rounded-md bg-warning-bg px-3 py-2 text-sm text-warning"><strong>Reviewer's note:</strong> {{ $deliverable->review_note }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        <div class="min-w-0">
            <x-chat-thread :messages="$messages" :viewer="auth()->user()" perspective="team"
                :action="route('team.orders.messages.store', $order->reference)"
                :attachment-url="fn ($m) => route('team.orders.messages.attachment', [$order->reference, $m->id])"
                title="Messages with Zytecs Research" intro="Ask questions or share drafts with the administrators. The student cannot see this thread." />
        </div>
    </div>
</x-layouts.team>
