<x-layouts.public title="Contact" description="Contact Zytecs Research on WhatsApp (preferred) or by email at research@zytecs.com, or send an enquiry using the form." :breadcrumbs="[['Contact']]">
    <x-page-header eyebrow="Contact" title="Talk to us" :breadcrumbs="[['Contact']]"
        lede="WhatsApp is the quickest way to reach us. You can also email us or use the form below." />

    <section class="container-page grid gap-12 py-16 lg:grid-cols-12">
        <div class="space-y-6 lg:col-span-5">
            <div class="card p-6">
                <p class="text-xs font-semibold tracking-[0.16em] text-bronze uppercase">Preferred</p>
                <h2 class="mt-2 font-serif text-2xl">WhatsApp</h2>
                <p class="mt-2 text-ink-soft">For enquiries, quotes and updates on your requests.</p>
                <a href="{{ config('zytecs.business.whatsapp_url') }}" class="btn btn-whatsapp mt-4" rel="noopener" target="_blank"><x-icon name="message-circle" class="size-5" />Open WhatsApp<span class="sr-only"> (opens in a new tab)</span></a>
            </div>
            <div class="card p-6">
                <h2 class="font-serif text-2xl">Email</h2>
                <p class="mt-2 text-ink-soft">For longer questions or documents you prefer to send by email.</p>
                <a href="mailto:{{ config('zytecs.business.email') }}" class="mt-3 inline-flex items-center gap-2 font-semibold"><x-icon name="mail" class="size-5" />{{ config('zytecs.business.email') }}</a>
            </div>
            <div class="rounded-lg border border-warning/40 bg-warning-bg p-6">
                <h2 class="flex items-center gap-2 font-serif text-xl text-warning"><x-icon name="phone" class="size-5" />Emergency contact only</h2>
                <p class="mt-2 text-sm text-ink-soft">Use this number only for an urgent problem with an active request. For all other enquiries please use WhatsApp or email.</p>
                <a href="tel:{{ config('zytecs.business.emergency_phone') }}" class="mt-3 inline-block font-mono text-lg font-medium text-ink">{{ config('zytecs.business.emergency_phone_display') }}</a>
            </div>
        </div>

        <div class="lg:col-span-7">
            <h2 class="font-serif text-2xl">Send an enquiry</h2>
            <p class="mt-2 text-ink-soft">To share a brief securely, please <a href="{{ route('register') }}">create a student account</a> instead. Never send passwords or login details.</p>
            <div class="mt-5"><x-error-summary /></div>
            <form method="POST" action="{{ route('contact.store') }}" class="mt-5 grid gap-5" novalidate data-submit-once>
                @csrf
                <div class="hidden" aria-hidden="true">
                    <label for="website">Leave this field empty</label>
                    <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                </div>
                <x-text-field name="name" label="Your name" autocomplete="name" />
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-text-field name="email" label="Email" type="email" autocomplete="email" optional hint="Give an email, a WhatsApp number, or both." />
                    <x-text-field name="whatsapp_number" label="WhatsApp number" type="tel" inputmode="tel" autocomplete="tel" optional placeholder="+44 7700 900123" hint="Include your country code." />
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-select-field name="enquiry_type" label="Enquiry type" :options="collect($types)->mapWithKeys(fn ($type) => [$type->value => $type->label()])->all()" />
                    <x-select-field name="country" label="Country of study" :options="$countries" optional />
                </div>
                <x-text-field name="subject" label="Subject or module" maxlength="100" optional placeholder="e.g. Marketing, MGMT403" />
                <div class="field">
                    <label for="message" class="label">Message</label>
                    <textarea id="message" name="message" rows="6" maxlength="3000" required aria-required="true" class="input" @error('message') aria-invalid="true" aria-describedby="message-error" @enderror>{{ old('message') }}</textarea>
                    <x-field-error name="message" />
                </div>
                <p class="text-sm text-muted">We use these details only to reply to you. See our <a href="{{ route('privacy') }}">Privacy Policy</a>.</p>
                <div><button type="submit" class="btn btn-primary"><span data-busy-label="Sending…">Send enquiry</span></button></div>
            </form>
        </div>
    </section>
</x-layouts.public>
