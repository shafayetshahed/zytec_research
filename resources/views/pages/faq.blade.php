<x-layouts.public title="Frequently asked questions" description="Answers about Zytecs Research services, subjects, submitting a brief, quotations, turnaround, confidentiality, revisions, referencing, originality, AI use and academic integrity." :breadcrumbs="[['FAQs']]">
    <x-page-header eyebrow="FAQs" title="Frequently asked questions" :breadcrumbs="[['FAQs']]" />

    <section class="container-page grid gap-12 py-16 lg:grid-cols-12">
        <nav aria-label="FAQ topics" class="lg:col-span-3">
            <ul class="space-y-2 lg:sticky lg:top-28">
                @foreach ($groups as $group => $items)
                    <li><a href="#{{ Str::slug($group) }}" class="text-ink no-underline hover:text-royal">{{ $group }}</a></li>
                @endforeach
            </ul>
        </nav>
        <div class="space-y-14 lg:col-span-8 lg:col-start-5">
            @foreach ($groups as $group => $items)
                <section id="{{ Str::slug($group) }}" class="scroll-mt-28" aria-labelledby="{{ Str::slug($group) }}-heading">
                    <h2 id="{{ Str::slug($group) }}-heading" class="font-serif text-2xl">{{ $group }}</h2>
                    <div class="mt-4 divide-y divide-line border-y border-line">
                        @foreach ($items as $faq)
                            <details class="group py-4">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-medium [&::-webkit-details-marker]:hidden">
                                    {{ $faq['q'] }}
                                    <x-icon name="chevron-down" class="size-5 text-slate transition-transform group-open:rotate-180" />
                                </summary>
                                <p class="mt-3 text-ink-soft">{{ $faq['a'] }}</p>
                            </details>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </section>

    @include('pages.partials.contact-band')
</x-layouts.public>
