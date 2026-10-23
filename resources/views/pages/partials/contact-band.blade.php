<section class="border-t border-line" aria-labelledby="contact-band-heading">
    <div class="container-page grid gap-10 py-16 lg:grid-cols-12 lg:items-center">
        <div class="lg:col-span-6">
            <h2 id="contact-band-heading" class="section-title">Have a brief or a question?</h2>
            <p class="mt-3 text-ink-soft">WhatsApp is the quickest way to reach us. Email works too.</p>
        </div>
        <div class="flex flex-col gap-4 lg:col-span-6">
            <div class="flex flex-wrap gap-3">
                <a href="{{ config('zytecs.business.whatsapp_url') }}" class="btn btn-whatsapp" rel="noopener" target="_blank"><x-icon name="message-circle" class="size-5" />Message us on WhatsApp<span class="sr-only"> (opens in a new tab)</span></a>
                <a href="mailto:{{ config('zytecs.business.email') }}" class="btn btn-outline"><x-icon name="mail" class="size-5" />{{ config('zytecs.business.email') }}</a>
            </div>
            <p class="text-sm text-muted"><strong class="font-semibold text-ink">Emergency contact only:</strong> <a href="tel:{{ config('zytecs.business.emergency_phone') }}" class="whitespace-nowrap text-ink">{{ config('zytecs.business.emergency_phone_display') }}</a>, for urgent problems with an active request.</p>
        </div>
    </div>
</section>
