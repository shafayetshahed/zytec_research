<x-layouts.public title="Access denied" description="You do not have permission to view this page. If you think this is wrong, sign in with the correct account or contact us." :noindex="true">
    <section class="container-page py-20">
        <p class="eyebrow">Error 403</p>
        <h1 class="display mt-4">Access denied</h1>
        <p class="lede mt-5 max-w-2xl">You do not have permission to view this page. If you think this is wrong, sign in with the correct account or contact us.</p>
        <p class="mt-8 flex flex-wrap gap-3">
            <a class="btn btn-primary" href="{{ route('home') }}">Go to the home page</a>
            <a class="btn btn-whatsapp" href="{{ config('zytecs.business.whatsapp_url') }}" rel="noopener" target="_blank">Message us on WhatsApp</a>
        </p>
    </section>
</x-layouts.public>
