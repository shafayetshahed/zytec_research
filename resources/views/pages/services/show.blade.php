@php
    $schema = [[
        '@type' => 'Service',
        'name' => $service['title'],
        'description' => $service['short'],
        'serviceType' => $service['title'],
        'provider' => ['@id' => url('/').'/#organization'],
        'areaServed' => ['United Kingdom', 'Australia', 'Canada', 'United States'],
        'url' => route('services.show', $slug),
    ]];
@endphp
<x-layouts.public :title="$service['title']" :description="$service['short'].' '.Str::limit($service['scope'], 90)" :breadcrumbs="[['Services', route('services.index')], [$service['title']]]" :schema="$schema">
    <x-page-header eyebrow="Service" :title="$service['title']" :lede="$service['short']" :breadcrumbs="[['Services', route('services.index')], [$service['title']]]">
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ auth()->check() && ! auth()->user()->isAdmin() ? route('student.submissions.create') : route('register') }}" class="btn btn-primary">Submit your brief</a>
            <a href="{{ config('zytecs.business.whatsapp_url') }}" class="btn btn-whatsapp" rel="noopener" target="_blank"><x-icon name="message-circle" class="size-5" />Ask on WhatsApp</a>
        </div>
    </x-page-header>

    <div class="container-page grid gap-12 py-16 lg:grid-cols-12">
        <article class="lg:col-span-8">
            <section aria-labelledby="scope-heading">
                <h2 id="scope-heading" class="font-serif text-2xl">Scope</h2>
                <p class="mt-3 text-ink-soft">{{ $service['scope'] }}</p>
            </section>

            <section class="mt-12" aria-labelledby="audience-heading">
                <h2 id="audience-heading" class="font-serif text-2xl">Who it is for</h2>
                <p class="mt-3 text-ink-soft">{{ $service['audience'] }}</p>
            </section>

            <section class="mt-12" aria-labelledby="process-heading">
                <h2 id="process-heading" class="font-serif text-2xl">Process</h2>
                <ol class="mt-5 space-y-4">
                    @foreach ($service['process'] as $step)
                        <li class="grid grid-cols-[2.25rem_1fr] gap-3">
                            <span class="flex size-8 items-center justify-center rounded-full border border-bronze/50 font-serif text-bronze">{{ $loop->iteration }}</span>
                            <span class="pt-1 text-ink-soft">{{ $step }}</span>
                        </li>
                    @endforeach
                </ol>
            </section>

            <section class="mt-12" aria-labelledby="deliverables-heading">
                <h2 id="deliverables-heading" class="font-serif text-2xl">What you might receive</h2>
                <ul class="mt-4 space-y-2">
                    @foreach ($service['deliverables'] as $item)
                        <li class="flex gap-3 text-ink-soft"><x-icon name="check" class="mt-1 size-5 text-success" />{{ $item }}</li>
                    @endforeach
                </ul>
                <p class="mt-4 text-sm text-muted">The exact deliverables are confirmed for each request.</p>
            </section>

            <section class="mt-12" aria-labelledby="limits-heading">
                <h2 id="limits-heading" class="font-serif text-2xl">Limitations</h2>
                <div class="alert-warning alert mt-4"><x-icon name="info" class="mt-0.5 size-5" /><p>{{ $service['limits'] }}</p></div>
            </section>

            @if ($recordedModules->isNotEmpty())
                <section class="mt-12" aria-labelledby="recorded-heading">
                    <h2 id="recorded-heading" class="font-serif text-2xl">Modules in our records</h2>
                    <p class="mt-2 text-sm text-muted">Anonymised from our work records. Titles are shown only where they were recorded.</p>
                    <ul class="mt-4 flex flex-wrap gap-2">
                        @foreach ($recordedModules->take(14) as $entry)
                            <li class="rounded-md border border-line bg-card px-3 py-1.5 text-sm">
                                @if ($entry['code'])<span class="font-mono font-medium">{{ $entry['code'] }}</span>@endif
                                @if ($entry['title']) <span class="text-ink-soft">{{ $entry['title'] }}</span>@endif
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('portfolio', ['area' => $areaKey]) }}" class="link-arrow mt-4 text-sm">All {{ $areaName }} records<x-icon name="arrow-right" class="size-4" /></a>
                </section>
            @endif
        </article>

        <aside class="space-y-6 lg:col-span-4">
            <div class="card p-6 lg:sticky lg:top-28">
                <h2 class="font-serif text-xl">Start with your brief</h2>
                <p class="mt-2 text-sm text-ink-soft">Upload it securely and we will reply on WhatsApp or email with what we can help with, the timeline and the price.</p>
                <a href="{{ auth()->check() && ! auth()->user()->isAdmin() ? route('student.submissions.create') : route('register') }}" class="btn btn-primary mt-5 w-full">Submit your brief</a>
                <a href="{{ route('how-it-works') }}" class="link-arrow mt-4 text-sm">How it works<x-icon name="arrow-right" class="size-4" /></a>
            </div>
            @if ($related->isNotEmpty())
                <nav aria-labelledby="related-heading" class="card p-6">
                    <h2 id="related-heading" class="font-serif text-xl">Related services</h2>
                    <ul class="mt-3 space-y-2">
                        @foreach ($related as $relatedSlug => $relatedService)
                            <li><a href="{{ route('services.show', $relatedSlug) }}">{{ $relatedService['title'] }}</a></li>
                        @endforeach
                    </ul>
                </nav>
            @endif
        </aside>
    </div>
</x-layouts.public>
