<x-layouts.public title="Something went wrong" description="An unexpected error occurred on our side. Please try again shortly, or contact us on WhatsApp if the problem continues." :noindex="true">
    <section class="container-page py-20">
        <p class="eyebrow">Error 500</p>
        <h1 class="display mt-4">Something went wrong</h1>
        <p class="lede mt-5 max-w-2xl">An unexpected error occurred on our side. Please try again shortly, or contact us on WhatsApp if the problem continues.</p>
        <p class="mt-8 flex flex-wrap gap-3">
            <a class="btn btn-primary" href="{{ route('home') }}">Go to the home page</a>
            <a class="btn btn-whatsapp" href="{{ config('zytecs.business.whatsapp_url') }}" rel="noopener" target="_blank">Message us on WhatsApp</a>
        </p>
    </section>
</x-layouts.public>
