<x-layouts.public title="Portfolio" description="An anonymised record of the modules, assessment types and subject areas Zytecs Research has worked on since late 2023, filterable by subject, task type and recorded country." :breadcrumbs="[['Portfolio']]">
    <x-page-header eyebrow="Portfolio" title="An anonymised record of our work" :breadcrumbs="[['Portfolio']]"
        lede="Every row comes from our own work records ({{ $yearRange }}). We publish only module codes, titles where recorded, broad task types, years and, where explicitly recorded, the country of study." />

    <section class="container-page py-12">
        <div class="grid gap-4 md:grid-cols-3">
            <div class="card p-5">
                <p class="text-xs font-semibold tracking-[0.16em] text-bronze uppercase">What a row means</p>
                <p class="mt-2 text-sm text-ink-soft">A module or task type that appears in our records. It is not a published case study, and it does not describe the outcome or the grade.</p>
            </div>
            <div class="card p-5">
                <p class="text-xs font-semibold tracking-[0.16em] text-bronze uppercase">What we never publish</p>
                <p class="mt-2 text-sm text-ink-soft">Student names or IDs, institutions, documents, grades, fees, staff names or private communications.</p>
            </div>
            <div class="card p-5">
                <p class="text-xs font-semibold tracking-[0.16em] text-bronze uppercase">Country of study</p>
                <p class="mt-2 text-sm text-ink-soft">Shown only where our records state it explicitly. It is never inferred from a module code.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('portfolio') }}" class="card mt-10 grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-4 lg:items-end" aria-label="Filter the portfolio">
            <x-select-field name="area" label="Subject area" :options="$areas" :value="$filters['area'] ?? ''" optional placeholder="All subject areas" />
            <x-select-field name="task" label="Task type" :options="$tasks->all()" :value="$filters['task'] ?? ''" optional placeholder="All task types" />
            <x-select-field name="country" label="Recorded country" :options="$countries->all()" :value="$filters['country'] ?? ''" optional placeholder="Any" />
            <div class="flex items-center gap-3">
                <button type="submit" class="btn btn-ink"><x-icon name="filter" class="size-4" />Filter</button>
                @if (array_filter($filters))
                    <a href="{{ route('portfolio') }}" class="text-sm">Clear</a>
                @endif
            </div>
        </form>

        <p class="mt-8 text-sm text-muted" role="status">Showing {{ $entries->count() }} of {{ $total }} records.</p>

        <div class="card mt-3 overflow-x-auto">
            <table class="data-table">
                <caption class="sr-only">Anonymised portfolio records</caption>
                <thead>
                    <tr>
                        <th scope="col">Code</th>
                        <th scope="col">Module or task (as recorded)</th>
                        <th scope="col">Subject area</th>
                        <th scope="col">Task types</th>
                        <th scope="col">Year</th>
                        <th scope="col">Country</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entries as $entry)
                        <tr>
                            <td>@if ($entry['code'])<span class="code-tag whitespace-nowrap">{{ $entry['code'] }}</span>@else<span class="text-muted">—</span>@endif</td>
                            <td class="min-w-52">
                                @if ($entry['title'])
                                    {{ $entry['title'] }}
                                    @if ($entry['title_status'] === 'to_confirm')<span class="badge ml-1 bg-line-soft text-muted" title="Our records give this title inconsistently">title to be confirmed</span>@endif
                                @else
                                    <span class="text-muted italic">Title not recorded</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap">{{ $areas[$entry['area']] }}</td>
                            <td>{{ implode(', ', $entry['tasks']) }}</td>
                            <td class="font-mono whitespace-nowrap">{{ implode(', ', $entry['years']) }}</td>
                            <td class="whitespace-nowrap">@if ($entry['countries']){{ implode(', ', $entry['countries']) }}@else<span class="text-muted" aria-label="No country recorded">—</span>@endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-muted">No records match these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <section class="mt-16 grid gap-8 lg:grid-cols-12" aria-labelledby="case-studies-heading">
            <div class="lg:col-span-5">
                <h2 id="case-studies-heading" class="section-title">Case studies</h2>
            </div>
            <div class="text-ink-soft lg:col-span-7">
                <p>We have not yet published any case studies. A case study will only appear here with the student's written permission and with all identifying details removed.</p>
            </div>
        </section>
    </section>
</x-layouts.public>
