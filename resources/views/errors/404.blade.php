<x-layouts.public title="Page not found" description="The page you were looking for could not be found." :noindex="true">
    <section class="container-page grid gap-10 py-20 lg:grid-cols-2 lg:items-center">
        <div>
            <p class="eyebrow">Error 404</p>
            <h1 class="display mt-4">We could not find that page.</h1>
            <p class="lede mt-5">The link may be out of date, or the page may have moved. Try one of these instead.</p>
            <ul class="mt-8 grid gap-3 sm:grid-cols-2">
                <li><a class="link-arrow" href="{{ route('home') }}">Home<x-icon name="arrow-right" class="size-4" /></a></li>
                <li><a class="link-arrow" href="{{ route('services.index') }}">Services<x-icon name="arrow-right" class="size-4" /></a></li>
                <li><a class="link-arrow" href="{{ route('subjects') }}">Subjects<x-icon name="arrow-right" class="size-4" /></a></li>
                <li><a class="link-arrow" href="{{ route('contact') }}">Contact us<x-icon name="arrow-right" class="size-4" /></a></li>
            </ul>
        </div>
        <div class="photo-frame hidden aspect-[4/3] overflow-hidden rounded-lg lg:block">
            <x-photo name="library-hall-tables" alt="" sizes="50vw" />
        </div>
    </section>
</x-layouts.public>
