<x-layouts.public title="Down for maintenance" description="We are making improvements and will be back shortly." :noindex="true">
    <section class="container-page py-20">
        <p class="eyebrow">Error 503</p>
        <h1 class="display mt-4">Down for maintenance</h1>
        <p class="lede mt-5 max-w-2xl">We are making improvements and will be back shortly.</p>
        <p class="mt-8 flex flex-wrap gap-3">
            <a class="btn btn-primary" href="{{ route('home') }}">Go to the home page</a>
            <a class="btn btn-whatsapp" href="{{ config('zytecs.business.whatsapp_url') }}" rel="noopener" target="_blank">Message us on WhatsApp</a>
        </p>
    </section>
</x-layouts.public>
