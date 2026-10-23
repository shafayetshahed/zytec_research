<x-layouts.public title="Why choose us" description="Why students work with Zytecs Research: around five years of experience, breadth across subjects, clear communication on WhatsApp, confidentiality and careful quality checks." :breadcrumbs="[['Why choose us']]">
    <x-page-header eyebrow="Why choose us" title="What you can expect from us" :breadcrumbs="[['Why choose us']]"
        lede="We would rather tell you what we actually do than make claims we cannot support." />

    <section class="container-page py-16">
        <div class="grid gap-x-12 gap-y-12 md:grid-cols-2">
            @foreach ([
                ['history', 'Around five years of experience', 'The team has worked with students since before our current records began, across assignments, reports, projects and research tasks.'],
                ['layers', 'Breadth across subjects', 'Our records cover '.count($areas).' subject areas, from strategic management and information systems to thermo-fluids, hospitality and law. See the subject directory for detail.'],
                ['message-circle', 'Clear, direct communication', 'We work mainly over WhatsApp and email, in plain English, and give straight answers about scope, timing and price.'],
                ['lock', 'Confidentiality', 'Your files are stored privately and are only accessible to you and authorised team members. We never publish your details.'],
                ['library', 'Careful research', 'Guidance is grounded in academic literature and credible sources, referenced in the style your institution requires.'],
                ['list-checks', 'Quality checks', 'We review our guidance against your brief and the agreed scope before it reaches you.'],
            ] as [$icon, $title, $text])
                <div class="grid grid-cols-[auto_1fr] gap-5">
                    <span class="flex size-12 items-center justify-center rounded-full bg-paper-deep text-royal"><x-icon :name="$icon" class="size-6" /></span>
                    <div>
                        <h2 class="font-serif text-2xl">{{ $title }}</h2>
                        <p class="mt-2 text-ink-soft">{!! str_replace('subject directory', '<a href="'.e(route('subjects')).'">subject directory</a>', e($text)) !!}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card mt-16 max-w-3xl p-6">
            <h2 class="font-serif text-xl">What we will not promise</h2>
            <p class="mt-2 text-ink-soft">We do not guarantee grades or outcomes, we do not claim partnerships with universities, and we do not publish testimonials we cannot verify. Your results depend on your own work and your institution's assessment.</p>
        </div>
    </section>

    @include('pages.partials.contact-band')
</x-layouts.public>
