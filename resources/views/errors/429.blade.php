<x-layouts.public title="Too many requests" description="You have made too many attempts in a short time. Please wait a few minutes and try again." :noindex="true">
    <section class="container-page py-20">
        <p class="eyebrow">Error 429</p>
        <h1 class="display mt-4">Too many requests</h1>
        <p class="lede mt-5 max-w-2xl">You have made too many attempts in a short time. Please wait a few minutes and try again.</p>
        <p class="mt-8 flex flex-wrap gap-3">
            <a class="btn btn-primary" href="{{ route('home') }}">Go to the home page</a>
            <a class="btn btn-whatsapp" href="{{ config('zytecs.business.whatsapp_url') }}" rel="noopener" target="_blank">Message us on WhatsApp</a>
        </p>
    </section>
</x-layouts.public>
