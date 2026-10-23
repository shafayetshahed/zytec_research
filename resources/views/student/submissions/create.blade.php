<x-layouts.student title="Submit a brief">
    <div class="max-w-3xl">
        <p class="eyebrow">New request</p>
        <h1 class="mt-3 font-serif text-3xl sm:text-4xl">Submit your brief</h1>
        <p class="mt-3 text-ink-soft">Tell us what you are working on. We review every brief and confirm scope, timeline and price with you before any work starts. Fields marked optional can be left blank.</p>
    </div>

    <div class="mt-6 max-w-3xl"><x-error-summary /></div>

    <form method="POST" action="{{ route('student.submissions.store') }}" enctype="multipart/form-data" class="mt-6 grid max-w-3xl gap-10" novalidate data-submit-once>
        @csrf
        <input type="hidden" name="client_token" value="{{ old('client_token', $clientToken) }}">

        <fieldset class="grid gap-5">
            <legend class="mb-4 font-serif text-xl">About the assignment</legend>
            <x-text-field name="assignment_name" label="Assignment or project name" maxlength="200" placeholder="e.g. Strategic analysis report" />
            <x-text-field name="module_code" label="Module code" maxlength="40" placeholder="e.g. MGMT402" class="max-w-xs font-mono uppercase placeholder:normal-case" />
            <div class="grid gap-5 sm:grid-cols-2">
                <x-select-field name="subject" label="Subject area" :options="$subjects" optional />
                <x-select-field name="assessment_type" label="Assessment type" :options="$assessmentTypes" optional />
                <x-text-field name="institution" label="University or college" maxlength="150" optional hint="Leave blank if you prefer not to say." />
                <x-select-field name="country" label="Country of study" :options="$countries" optional />
            </div>
            <x-text-field name="deadline" label="Your deadline" type="date" optional class="max-w-xs" :min="now()->toDateString()" />
            <div class="field">
                <label for="description" class="label">What would you like help with?<span class="label-optional">(optional)</span></label>
                <p id="description-hint" class="hint">For example: understanding the brief, feedback on a draft, referencing, or data analysis guidance.</p>
                <textarea id="description" name="description" rows="5" maxlength="5000" class="input" aria-describedby="description-hint @error('description') description-error @enderror" @error('description') aria-invalid="true" @enderror>{{ old('description') }}</textarea>
                <x-field-error name="description" />
            </div>
        </fieldset>

        <fieldset class="grid gap-5">
            <legend class="mb-4 font-serif text-xl">Files</legend>
            <div class="field">
                <label for="brief" class="label">Assignment brief</label>
                <p id="brief-hint" class="hint">PDF, Word, PowerPoint, Excel, JPG or PNG. Maximum {{ \Illuminate\Support\Number::fileSize(config('zytecs.uploads.max_kb') * 1024) }}.</p>
                <div class="rounded-lg border border-dashed border-slate/50 bg-card p-5">
                    <input id="brief" name="brief" type="file" required aria-required="true"
                           accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png"
                           data-file-input="brief-selected"
                           aria-describedby="brief-hint brief-selected @error('brief') brief-error @enderror" @error('brief') aria-invalid="true" @enderror
                           class="block w-full text-sm file:mr-4 file:rounded-md file:border-0 file:bg-ink file:px-4 file:py-2 file:font-semibold file:text-white hover:file:bg-ink-soft">
                    <p id="brief-selected" class="mt-2 text-sm text-muted" aria-live="polite">No file selected</p>
                </div>
                <x-field-error name="brief" />
            </div>
            <div class="field">
                <label for="supplementary" class="label">Supporting files<span class="label-optional">(optional, up to {{ config('zytecs.uploads.max_supplementary_files') }})</span></label>
                <p id="supplementary-hint" class="hint">For example the marking rubric, lecture slides or your current draft.</p>
                <input id="supplementary" name="supplementary[]" type="file" multiple
                       accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png"
                       data-file-input="supplementary-selected" aria-describedby="supplementary-hint supplementary-selected"
                       class="block w-full text-sm file:mr-4 file:rounded-md file:border file:border-line file:bg-card file:px-4 file:py-2 file:font-semibold file:text-ink">
                <p id="supplementary-selected" class="text-sm text-muted" aria-live="polite">No file selected</p>
                <x-field-error name="supplementary" />
                @foreach ($errors->get('supplementary.*') as $messages)
                    @foreach ($messages as $message)
                        <p class="field-error"><x-icon name="circle-alert" class="mt-0.5 size-4" />{{ $message }}</p>
                    @endforeach
                @endforeach
            </div>
        </fieldset>

        <fieldset class="grid gap-5">
            <legend class="mb-4 font-serif text-xl">Contact and confirmation</legend>
            <x-text-field name="whatsapp_number" label="WhatsApp number" type="tel" inputmode="tel" autocomplete="tel" :value="$user->whatsapp_number" class="max-w-sm font-mono"
                          hint="We will contact you here about this brief. Changing it also updates your profile." />
            <div class="field">
                <label class="flex items-start gap-3 text-sm text-ink-soft">
                    <input type="checkbox" name="confirm_whatsapp" value="1" class="checkbox" required aria-required="true" @checked(old('confirm_whatsapp')) @error('confirm_whatsapp') aria-invalid="true" aria-describedby="confirm_whatsapp-error" @enderror>
                    <span>This WhatsApp number is correct and I am happy to be contacted on it.</span>
                </label>
                <x-field-error name="confirm_whatsapp" />
            </div>
            <div class="alert-info alert">
                <x-icon name="lock" class="mt-0.5 size-5" />
                <div class="text-sm">
                    <p class="font-semibold">How we handle your documents</p>
                    <p class="mt-1">Files are stored privately, are only visible to you and authorised Zytecs Research staff, are never published, and are not used to train AI systems. Do not include passwords or login details in any file. See our <a href="{{ route('confidentiality') }}" target="_blank" rel="noopener">confidentiality commitment</a> and <a href="{{ route('privacy') }}" target="_blank" rel="noopener">Privacy Policy</a>.</p>
                </div>
            </div>
            <div class="field">
                <label class="flex items-start gap-3 text-sm text-ink-soft">
                    <input type="checkbox" name="terms" value="1" class="checkbox" required aria-required="true" @checked(old('terms')) @error('terms') aria-invalid="true" aria-describedby="terms-error" @enderror>
                    <span>I have read the document-handling notice, the <a href="{{ route('terms') }}" target="_blank" rel="noopener">Terms of Service</a> and the <a href="{{ route('academic-integrity') }}" target="_blank" rel="noopener">academic integrity commitment</a>.</span>
                </label>
                <x-field-error name="terms" />
            </div>
        </fieldset>

        <div class="flex flex-wrap items-center gap-4 border-t border-line pt-6">
            <button type="submit" class="btn btn-primary"><x-icon name="upload" class="size-5" /><span data-busy-label="Uploading…">Submit brief</span></button>
            <a href="{{ route('student.dashboard') }}" class="text-sm font-medium">Cancel</a>
        </div>
    </form>
</x-layouts.student>
