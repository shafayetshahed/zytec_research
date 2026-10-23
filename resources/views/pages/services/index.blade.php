@php
    $schema = [[
        '@type' => 'ItemList',
        'name' => 'Zytecs Research services',
        'itemListElement' => collect($researchServices)->merge($disciplineServices)->keys()->values()->map(fn ($slug, $i) => [
            '@type' => 'ListItem', 'position' => $i + 1, 'url' => route('services.show', $slug),
        ])->all(),
    ]];
@endphp
<x-layouts.public title="Services" description="Assignment planning, research proposals, literature reviews, dissertation guidance, data analysis, proofreading and subject support in business, IT, engineering, hospitality and law." :breadcrumbs="[['Services']]" :schema="$schema">
    <x-page-header eyebrow="Services" title="Guidance, feedback and research support" :breadcrumbs="[['Services']]"
        lede="Choose a research or writing skill, or a subject area. Every request is reviewed first, and we confirm scope, timeline and price before any work starts." />

    <section class="container-page py-16" aria-labelledby="research-services">
        <h2 id="research-services" class="section-title">Research and writing skills</h2>
        <div class="mt-8 grid gap-px overflow-hidden rounded-lg border border-line bg-line md:grid-cols-2 lg:grid-cols-3">
            @foreach ($researchServices as $slug => $service)
                <a href="{{ route('services.show', $slug) }}" class="group flex flex-col gap-3 bg-card p-6 text-ink no-underline hover:bg-paper">
                    <x-icon :name="$service['icon']" class="size-6 text-royal" />
                    <h3 class="text-xl group-hover:text-royal">{{ $service['title'] }}</h3>
                    <p class="text-[0.95rem] text-ink-soft">{{ $service['short'] }}</p>
                    <span class="link-arrow mt-auto pt-2 text-sm">Scope and process<x-icon name="arrow-right" class="size-4" /></span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="border-t border-line bg-card" aria-labelledby="subject-services">
        <div class="container-page grid gap-10 py-16 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <h2 id="subject-services" class="section-title">Subject support</h2>
                <p class="mt-4 text-ink-soft">Discipline-specific explanations and feedback, informed by the modules we have recorded working on.</p>
                <div class="photo-frame mt-8 hidden aspect-[3/4] overflow-hidden rounded-lg lg:block">
                    <x-photo name="desk-books-notes" alt="A study desk with open books, sticky notes and handwritten research notes" sizes="30vw" :width="1400" :height="2489" />
                </div>
            </div>
            <ul class="divide-y divide-line border-y border-line lg:col-span-8">
                @foreach ($disciplineServices as $slug => $service)
                    <li>
                        <a href="{{ route('services.show', $slug) }}" class="group grid grid-cols-[auto_1fr_auto] items-start gap-4 py-5 text-ink no-underline">
                            <x-icon :name="$service['icon']" class="mt-1 size-6 text-royal" />
                            <span>
                                <span class="block font-serif text-xl group-hover:text-royal">{{ $service['title'] }}</span>
                                <span class="mt-1 block text-[0.95rem] text-ink-soft">{{ $service['short'] }}</span>
                            </span>
                            <x-icon name="arrow-up-right" class="mt-1.5 size-5 text-slate group-hover:text-royal" />
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    <section class="container-page py-16">
        <div class="alert-info alert max-w-3xl">
            <x-icon name="info" class="mt-0.5 size-5" />
            <p>All services are learning support. We do not write work for you to submit as your own, sit tests or exams, or log in to university systems. <a href="{{ route('academic-integrity') }}">Read our academic integrity commitment</a>.</p>
        </div>
    </section>

    @include('pages.partials.contact-band')
</x-layouts.public>
