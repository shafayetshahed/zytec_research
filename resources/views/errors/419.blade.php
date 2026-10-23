<x-layouts.public title="Page expired" description="Your session expired, usually because the page was open for a long time. Go back, refresh the page and try again." :noindex="true">
    <section class="container-page py-20">
        <p class="eyebrow">Error 419</p>
        <h1 class="display mt-4">Page expired</h1>
        <p class="lede mt-5 max-w-2xl">Your session expired, usually because the page was open for a long time. Go back, refresh the page and try again.</p>
        <p class="mt-8 flex flex-wrap gap-3">
            <a class="btn btn-primary" href="{{ route('home') }}">Go to the home page</a>
            <a class="btn btn-whatsapp" href="{{ config('zytecs.business.whatsapp_url') }}" rel="noopener" target="_blank">Message us on WhatsApp</a>
        </p>
    </section>
</x-layouts.public>
