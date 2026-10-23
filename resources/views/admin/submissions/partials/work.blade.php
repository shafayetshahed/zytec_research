@php
    $awaitingPayment = $submission->quotation?->sent_at && $submission->payment_status !== \App\Enums\PaymentStatus::PaidInFull;
@endphp
<section class="card min-w-0 p-6" aria-labelledby="work-heading" id="work">
    <h2 id="work-heading" class="font-serif text-xl">Team and materials</h2>

    <form method="POST" action="{{ route('admin.submissions.assign', $submission) }}" class="mt-4 grid gap-4 rounded-md border border-line p-4 sm:grid-cols-2">
        @csrf
        @method('PUT')
        <div class="field">
            <label for="expert_id" class="label">Assigned team member</label>
            <select id="expert_id" name="expert_id" class="input">
                <option value="">Not assigned</option>
                @foreach ($experts as $expert)
                    <option value="{{ $expert->id }}" @selected($submission->expert_id === $expert->id)>{{ $expert->name }}</option>
                @endforeach
            </select>
            @if ($experts->isEmpty())<p class="hint">No team members yet. <a href="{{ route('admin.team.create') }}">Add one</a>.</p>@endif
            @if ($submission->assigned_at)<p class="hint">Assigned {{ $submission->assigned_at->format('j M Y H:i') }}.</p>@endif
        </div>
        <div class="field sm:col-span-2">
            <label for="expert_instructions" class="label">Instructions for the team member<span class="label-optional">(they cannot see the student's name or contact details)</span></label>
            <textarea id="expert_instructions" name="expert_instructions" rows="3" maxlength="5000" class="input">{{ old('expert_instructions', $submission->expert_instructions) }}</textarea>
        </div>
        <div class="sm:col-span-2"><button type="submit" class="btn btn-ink btn-sm">Save assignment</button></div>
    </form>

    <h3 class="mt-6 font-sans text-sm font-semibold">Guidance and feedback materials</h3>
    @if ($awaitingPayment)
        <p class="mt-1 text-sm text-muted">Approved materials are released to the student automatically once the order is paid in full, unless you tick "release now".</p>
    @endif
    <ul class="mt-3 space-y-3">
        @forelse ($submission->deliverables as $deliverable)
            <li class="rounded-md border border-line p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <a href="{{ route('admin.submissions.materials.download', [$submission, $deliverable]) }}" class="font-medium break-all">{{ $deliverable->original_name }}</a>
                        <p class="text-xs text-muted">{{ $deliverable->humanSize() }} · uploaded by {{ $deliverable->uploader?->name ?? 'former user' }} on {{ $deliverable->created_at->format('j M Y H:i') }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <x-deliverable-badge :status="$deliverable->status" />
                        @if ($deliverable->released_at)
                            <span class="badge bg-success-bg text-success">Shared with student {{ $deliverable->released_at->format('j M') }}</span>
                        @elseif ($deliverable->status === \App\Enums\DeliverableStatus::Approved)
                            <span class="badge bg-warning-bg text-warning">Waiting for full payment</span>
                        @endif
                    </div>
                </div>
                @if ($deliverable->note)<p class="mt-2 text-sm text-ink-soft"><strong>Note:</strong> {{ $deliverable->note }}</p>@endif
                @if ($deliverable->review_note)<p class="mt-2 text-sm text-warning"><strong>Your note:</strong> {{ $deliverable->review_note }}</p>@endif
                @if ($deliverable->status !== \App\Enums\DeliverableStatus::Approved || ! $deliverable->released_at)
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        @if ($deliverable->status !== \App\Enums\DeliverableStatus::Approved || ($awaitingPayment && ! $deliverable->release_before_payment))
                            <form method="POST" action="{{ route('admin.submissions.materials.approve', [$submission, $deliverable]) }}" class="grid gap-2">
                                @csrf
                                @if ($awaitingPayment)
                                    <label class="flex items-start gap-2 text-xs text-ink-soft"><input type="checkbox" name="release_before_payment" value="1" class="checkbox mt-0"> Release now, before full payment</label>
                                @endif
                                <div><button type="submit" class="btn btn-primary btn-sm"><x-icon name="check" class="size-4" />{{ $deliverable->status === \App\Enums\DeliverableStatus::Approved ? 'Update release' : 'Approve' }}</button></div>
                            </form>
                        @endif
                        @if ($deliverable->status === \App\Enums\DeliverableStatus::PendingReview)
                            <form method="POST" action="{{ route('admin.submissions.materials.return', [$submission, $deliverable]) }}" class="grid gap-2">
                                @csrf
                                <label for="review-note-{{ $deliverable->id }}" class="sr-only">What needs changing</label>
                                <input id="review-note-{{ $deliverable->id }}" name="review_note" class="input min-h-9 py-1.5 text-sm" placeholder="What needs changing?" required>
                                <div><button type="submit" class="btn btn-outline btn-sm">Send back</button></div>
                            </form>
                        @endif
                    </div>
                @endif
            </li>
        @empty
            <li class="text-sm text-muted">No materials yet.</li>
        @endforelse
    </ul>

    <details class="mt-4 rounded-md border border-line" @error('material') open @enderror>
        <summary class="cursor-pointer px-4 py-3 font-semibold">Upload materials yourself</summary>
        <form method="POST" action="{{ route('admin.submissions.materials.store', $submission) }}" enctype="multipart/form-data" class="grid gap-3 border-t border-line p-4" novalidate data-submit-once>
            @csrf
            <input name="material" type="file" required accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png" class="block w-full text-sm" aria-label="Material file">
            <x-field-error name="material" />
            <input name="note" class="input" placeholder="Note about this material (optional, internal)" aria-label="Material note">
            <label class="flex items-start gap-3 text-sm text-ink-soft"><input type="checkbox" name="confirm_guidance" value="1" class="checkbox" required> This is guidance or feedback for the student to learn from, not a finished assignment.</label>
            <x-field-error name="confirm_guidance" />
            <div><button type="submit" class="btn btn-ink btn-sm">Upload (approved automatically)</button></div>
        </form>
    </details>
</section>
