<x-layouts.public title="Subjects and modules" description="A searchable directory of the subject areas and modules recorded in Zytecs Research's anonymised work records, from business and IT to engineering, hospitality and law." :breadcrumbs="[['Subjects']]">
    <x-page-header eyebrow="Subjects" title="Subjects and modules" :breadcrumbs="[['Subjects']]"
        lede="Search the modules and task types recorded in our anonymised work records, grouped by subject area. Module codes are only shown with a title where the title was recorded." />

    <section class="container-page py-12">
        <div class="card flex flex-col gap-3 p-5 sm:flex-row sm:items-end">
            <div class="field flex-1">
                <label for="subject-search" class="label">Filter subjects and modules</label>
                <input id="subject-search" type="search" class="input" placeholder="Try “marketing”, “MBIS” or “report”" data-subject-filter aria-describedby="subject-filter-help">
                <p id="subject-filter-help" class="hint">Results update as you type. Without JavaScript, the full directory is shown below.</p>
            </div>
            <p id="subject-filter-status" class="text-sm font-medium text-muted sm:pb-8" role="status" aria-live="polite"></p>
        </div>

        <nav aria-label="Subject areas" class="mt-8 flex flex-wrap gap-2">
            @foreach ($areas as $key => $name)
                <a href="#area-{{ $key }}" class="rounded-full border border-line bg-card px-3 py-1.5 text-sm text-ink no-underline hover:border-royal hover:text-royal">{{ $name }}</a>
            @endforeach
        </nav>

        <div class="mt-12 space-y-14">
            @foreach ($areas as $key => $name)
                @php $entries = $grouped->get($key, collect()); @endphp
                <section id="area-{{ $key }}" class="scroll-mt-28" data-subject-group aria-labelledby="area-{{ $key }}-heading">
                    <div class="flex flex-wrap items-baseline justify-between gap-3 border-b border-ink/80 pb-3">
                        <h2 id="area-{{ $key }}-heading" class="font-serif text-2xl">{{ $name }}</h2>
                        @isset($areaServices[$key], $services[$areaServices[$key]])
                            <a href="{{ route('services.show', $areaServices[$key]) }}" class="link-arrow text-sm">{{ $services[$areaServices[$key]]['title'] }} service<x-icon name="arrow-right" class="size-4" /></a>
                        @endisset
                    </div>
                    <ul class="grid gap-x-10 sm:grid-cols-2">
                        @foreach ($entries as $entry)
                            <li class="flex items-baseline gap-3 border-b border-line-soft py-3"
                                data-subject-item="{{ Str::lower(implode(' ', array_filter([$name, $entry['code'], $entry['title'], implode(' ', $entry['tasks'])]))) }}">
                                @if ($entry['code'])
                                    <span class="code-tag shrink-0">{{ $entry['code'] }}</span>
                                @endif
                                <span>
                                    <span class="{{ $entry['title'] ? '' : 'text-muted italic' }}">{{ $entry['title'] ?? 'Title not recorded' }}</span>
                                    <span class="block text-sm text-muted">{{ implode(', ', $entry['tasks']) }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>

        <p class="mt-14 max-w-3xl text-sm text-muted">Do not see your module? The directory only shows what our records contain. Send us your brief and we will tell you honestly whether it is within our experience.</p>
    </section>
</x-layouts.public>
