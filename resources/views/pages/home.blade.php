<x-layouts.public
    title="Zytecs Research"
    description="Academic guidance, research support, feedback and proofreading for international students in the UK, Australia, Canada and the USA. Submit your brief or message us on WhatsApp.">

    {{-- Hero --}}
    <section class="relative overflow-hidden border-b border-line">
        <div class="container-page grid gap-12 py-14 sm:py-20 lg:grid-cols-12 lg:items-center lg:gap-10">
            <div class="lg:col-span-6">
                <p class="eyebrow">Academic guidance &amp; research support</p>
                <h1 class="display mt-5">
                    Understand the brief. <em class="font-normal text-royal italic">Strengthen</em> your own work.
                </h1>
                <p class="lede mt-6 max-w-xl">
                    Zytecs Research helps international students plan assignments, shape research, analyse data and polish their writing, with clear explanations and honest feedback from a team with around {{ config('zytecs.business.experience_years') }} years of multidisciplinary experience.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ auth()->check() && ! auth()->user()->isAdmin() ? route('student.submissions.create') : route('register') }}" class="btn btn-primary">
                        <x-icon name="file-up" class="size-5" />Submit your brief
                    </a>
                    <a href="{{ config('zytecs.business.whatsapp_url') }}" class="btn btn-whatsapp" rel="noopener" target="_blank">
                        <x-icon name="message-circle" class="size-5" />Chat on WhatsApp<span class="sr-only"> (opens in a new tab)</span>
                    </a>
                </div>
                <dl class="mt-10 grid max-w-lg grid-cols-3 gap-6 border-t border-line pt-6">
                    <div>
                        <dt class="text-xs font-semibold tracking-wider text-muted uppercase">Experience</dt>
                        <dd class="mt-1 font-serif text-2xl">~{{ config('zytecs.business.experience_years') }} years</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold tracking-wider text-muted uppercase">Subject areas</dt>
                        <dd class="mt-1 font-serif text-2xl">{{ $areas->count() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold tracking-wider text-muted uppercase">Recorded modules &amp; tasks</dt>
                        <dd class="mt-1 font-serif text-2xl">{{ $areas->sum('count') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="relative lg:col-span-6">
                <div class="photo-frame aspect-[4/3] overflow-hidden rounded-lg shadow-[var(--shadow-soft)]">
                    <x-photo name="library-study-desk" alt="A student working on a laptop at a long wooden desk in a university library reading room" :eager="true" sizes="(min-width: 1024px) 46vw, 100vw" />
                </div>
                <aside class="card relative -mt-16 ml-4 max-w-sm p-5 shadow-[var(--shadow-soft)] sm:ml-10 lg:absolute lg:-bottom-14 lg:-left-16 lg:mt-0 lg:ml-0 lg:max-w-xs" aria-label="From our records">
                    <p class="text-xs font-semibold tracking-[0.16em] text-bronze uppercase">From our records</p>
                    <ul class="mt-3 space-y-2 text-sm">
                        @foreach ($highlights->take(3) as $entry)
                            <li class="flex items-baseline gap-2"><span class="code-tag">{{ $entry['code'] }}</span><span>{{ $entry['title'] }}</span></li>
                        @endforeach
                    </ul>
                    <a href="{{ route('portfolio') }}" class="link-arrow mt-4 text-sm">See the anonymised portfolio<x-icon name="arrow-right" class="size-4" /></a>
                </aside>
            </div>
        </div>
    </section>

    {{-- Introduction --}}
    <section class="container-page grid gap-10 py-20 lg:grid-cols-12" aria-labelledby="intro-heading">
        <div class="lg:col-span-5">
            <p class="eyebrow">Who we are</p>
            <h2 id="intro-heading" class="section-title mt-4">A small, multidisciplinary team that takes each brief seriously.</h2>
        </div>
        <div class="space-y-5 text-ink-soft lg:col-span-6 lg:col-start-7">
            <p>We are an academic support and research services team based in Bangladesh, working mainly with Bangladeshi and other South Asian students at colleges and universities abroad. Over roughly five years we have worked across business, IT, engineering, hospitality, law, education and research methods.</p>
            <p>Every request starts with a careful read of your brief and marking criteria. We then tell you plainly what we can help with, how long it will take and what it will cost, before anything begins.</p>
            <p><a href="{{ route('about') }}" class="link-arrow">More about our approach<x-icon name="arrow-right" class="size-4" /></a></p>
        </div>
    </section>

    {{-- Services --}}
    <section class="border-y border-line bg-card" aria-labelledby="services-heading">
        <div class="container-page py-20">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <div class="max-w-2xl">
                    <p class="eyebrow">Services</p>
                    <h2 id="services-heading" class="section-title mt-4">Support for each stage of an assignment or research project</h2>
                </div>
                <a href="{{ route('services.index') }}" class="btn btn-outline">All {{ $services->count() }} services</a>
            </div>

            <div class="mt-12 grid gap-x-12 gap-y-2 lg:grid-cols-2">
                @foreach ($services->where('group', 'research')->take(8) as $slug => $service)
                    <a href="{{ route('services.show', $slug) }}" class="group grid grid-cols-[2.5rem_1fr_auto] items-start gap-4 border-b border-line-soft py-5 text-ink no-underline">
                        <span class="font-serif text-xl text-bronze">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <span>
                            <span class="block font-serif text-xl group-hover:text-royal">{{ $service['title'] }}</span>
                            <span class="mt-1 block text-[0.95rem] text-ink-soft">{{ $service['short'] }}</span>
                        </span>
                        <x-icon name="arrow-up-right" class="mt-1.5 size-5 text-slate transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5 group-hover:text-royal" />
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Subjects --}}
    <section class="container-page py-20" aria-labelledby="subjects-heading">
        <div class="grid gap-10 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <p class="eyebrow">Subjects</p>
                <h2 id="subjects-heading" class="section-title mt-4">Breadth across disciplines</h2>
                <p class="mt-4 text-ink-soft">Counts show distinct modules and task types in our anonymised records from late 2023 to 2026, not the number of students.</p>
                <a href="{{ route('subjects') }}" class="link-arrow mt-6">Browse the subject directory<x-icon name="arrow-right" class="size-4" /></a>
            </div>
            <ul class="divide-y divide-line border-y border-line lg:col-span-8">
                @foreach ($areas as $key => $area)
                    <li>
                        <a href="{{ route('portfolio', ['area' => $key]) }}" class="group flex flex-wrap items-baseline justify-between gap-x-6 gap-y-1 py-4 text-ink no-underline">
                            <span class="font-serif text-xl group-hover:text-royal">{{ $area['name'] }}</span>
                            <span class="font-mono text-sm text-muted">{{ $area['count'] }} recorded</span>
                            @if ($area['sample'])
                                <span class="w-full text-sm text-ink-soft">{{ $area['sample'] }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Portfolio highlights --}}
    <section class="border-y border-line bg-paper-deep/60" aria-labelledby="portfolio-heading">
        <div class="container-page grid gap-12 py-20 lg:grid-cols-12 lg:items-start">
            <div class="photo-frame aspect-[4/3] overflow-hidden rounded-lg lg:sticky lg:top-32 lg:col-span-5">
                <x-photo name="analytics-on-laptop" alt="A laptop screen showing analytics charts and histograms" sizes="(min-width: 1024px) 40vw, 100vw" />
            </div>
            <div class="lg:col-span-6 lg:col-start-7">
                <p class="eyebrow">Selected portfolio</p>
                <h2 id="portfolio-heading" class="section-title mt-4">Modules we have recorded working on</h2>
                <p class="mt-4 text-ink-soft">Anonymised from our own work records. We never publish student names, institutions, documents or results.</p>
                <table class="data-table mt-8 bg-card">
                    <caption class="sr-only">Selected recorded modules</caption>
                    <thead><tr><th scope="col">Code</th><th scope="col">Module (as recorded)</th><th scope="col">Year</th></tr></thead>
                    <tbody>
                        @foreach ($highlights as $entry)
                            <tr>
                                <td><span class="code-tag">{{ $entry['code'] }}</span></td>
                                <td>{{ $entry['title'] }}</td>
                                <td class="font-mono whitespace-nowrap text-muted">{{ implode(', ', $entry['years']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <a href="{{ route('portfolio') }}" class="link-arrow mt-6">View the full record<x-icon name="arrow-right" class="size-4" /></a>
            </div>
        </div>
    </section>

    {{-- Process --}}
    <section class="bg-ink text-white" aria-labelledby="process-heading">
        <div class="container-page py-20">
            <p class="eyebrow text-[#d9b98a] before:bg-[#d9b98a]/60">How it works</p>
            <h2 id="process-heading" class="section-title mt-4 max-w-2xl text-white">Nothing starts until you agree the scope, timeline and price.</h2>
            <ol class="mt-12 grid gap-x-10 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['Send your brief', 'Upload it through the student portal or start with a WhatsApp message.'],
                    ['We review it', 'We read the brief, rubric and any guidance to understand what is required.'],
                    ['We confirm the details', 'Scope, our availability, a realistic timeline and the price.'],
                    ['You decide', 'The support arrangement begins only once you agree.'],
                    ['Guidance is delivered', 'Explanations, feedback or proofreading, as agreed.'],
                    ['Follow-up', 'Questions and revisions are handled under the agreed terms.'],
                ] as [$step, $text])
                    <li class="border-t border-white/20 pt-5">
                        <span class="font-serif text-4xl text-[#d9b98a]">{{ $loop->iteration }}</span>
                        <h3 class="mt-3 font-serif text-xl text-white">{{ $step }}</h3>
                        <p class="mt-2 text-white/75">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
            <a href="{{ route('how-it-works') }}" class="btn mt-12 border border-white/40 text-white hover:bg-white/10">The full process</a>
        </div>
    </section>

    {{-- Principles --}}
    <section class="container-page py-20" aria-labelledby="principles-heading">
        <div class="max-w-2xl">
            <p class="eyebrow">Our principles</p>
            <h2 id="principles-heading" class="section-title mt-4">Quality, confidentiality and responsible support</h2>
        </div>
        <div class="mt-12 grid gap-10 md:grid-cols-3">
            <div>
                <x-icon name="list-checks" class="size-7 text-royal" />
                <h3 class="mt-4 text-xl">Careful quality checks</h3>
                <p class="mt-2 text-ink-soft">We check guidance against your brief and marking criteria, use credible academic sources and follow the referencing style you need.</p>
            </div>
            <div>
                <x-icon name="shield-check" class="size-7 text-royal" />
                <h3 class="mt-4 text-xl">Confidential by default</h3>
                <p class="mt-2 text-ink-soft">Briefs are stored privately and seen only by our team. We never publish your details, and never ask for university passwords. <a href="{{ route('confidentiality') }}">How we protect you</a>.</p>
            </div>
            <div>
                <x-icon name="scale" class="size-7 text-royal" />
                <h3 class="mt-4 text-xl">Learning, not shortcuts</h3>
                <p class="mt-2 text-ink-soft">We help you understand and improve your own work. We do not write work for submission, sit exams or impersonate students. <a href="{{ route('academic-integrity') }}">Read our commitment</a>.</p>
            </div>
        </div>
    </section>

    {{-- FAQs --}}
    <section class="border-t border-line bg-card" aria-labelledby="faq-heading">
        <div class="container-page grid gap-10 py-20 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <p class="eyebrow">Questions</p>
                <h2 id="faq-heading" class="section-title mt-4">Frequently asked</h2>
                <a href="{{ route('faq') }}" class="link-arrow mt-6">All FAQs<x-icon name="arrow-right" class="size-4" /></a>
            </div>
            <div class="divide-y divide-line border-y border-line lg:col-span-8">
                @foreach ($faqs as $faq)
                    <details class="group py-4">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-serif text-lg [&::-webkit-details-marker]:hidden">
                            {{ $faq['q'] }}
                            <x-icon name="chevron-down" class="size-5 text-slate transition-transform group-open:rotate-180" />
                        </summary>
                        <p class="mt-3 text-ink-soft">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    @include('pages.partials.contact-band')
</x-layouts.public>
