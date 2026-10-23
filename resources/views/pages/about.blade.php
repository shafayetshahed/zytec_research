<x-layouts.public title="About us" description="Zytecs Research is a multidisciplinary academic support and research services team with around five years of experience helping international students." :breadcrumbs="[['About us']]">
    <x-page-header eyebrow="About us" title="Academic support built on careful reading and honest advice" :breadcrumbs="[['About us']]"
        lede="Zytecs Research is an academic support and research services business with around five years of experience working with students across many disciplines." />

    <section class="container-page grid gap-12 py-16 lg:grid-cols-12">
        <div class="prose-zr lg:col-span-7">
            <h2>What we do</h2>
            <p>We help students understand assignment briefs, plan their work, develop research proposals, choose and apply methods, interpret data, and improve the clarity and referencing of their own writing. Our support takes the form of explanations, structured feedback, guidance sessions and proofreading.</p>
            <p>Most of the students we work with are from Bangladesh and elsewhere in South Asia and study at colleges and universities abroad. Our recorded work shows students studying in the United Kingdom and Australia, and we also welcome students studying in Canada and the United States.</p>

            <h2>Experience across disciplines</h2>
            <p>Over roughly five years the team has worked on modules, reports, projects and research tasks in:</p>
            <ul>
                @foreach ($areas as $area)
                    <li>{{ $area }}</li>
                @endforeach
            </ul>
            <p>Our <a href="{{ route('portfolio') }}">anonymised portfolio</a> lists the modules and task types recorded in our own work records since late 2023.</p>

            <h2>How we work as a team</h2>
            <p>Requests are reviewed before anything is agreed, so that each one goes to a team member with relevant subject experience. Where a request falls outside our experience, we say so rather than accept it.</p>

            <h2>Our quality process</h2>
            <ol>
                <li><strong>Read the requirements properly.</strong> The brief, marking criteria, word count, referencing style and any lecturer guidance.</li>
                <li><strong>Agree the scope in writing.</strong> What we will help with, the format of the support and the timeline.</li>
                <li><strong>Use credible sources.</strong> Academic journals, textbooks and authoritative organisations, cited in your required style.</li>
                <li><strong>Review before delivery.</strong> Guidance is checked against the agreed scope and your brief.</li>
                <li><strong>Follow up.</strong> Questions are answered and agreed revisions handled under the agreed terms.</li>
            </ol>

            <h2>Our values</h2>
            <ul>
                <li><strong>Honesty:</strong> clear quotes, realistic timelines and no promises about grades.</li>
                <li><strong>Confidentiality:</strong> your documents and details are private.</li>
                <li><strong>Integrity:</strong> we support learning and do not produce work for you to submit as your own. See our <a href="{{ route('academic-integrity') }}">academic integrity commitment</a>.</li>
                <li><strong>Respect:</strong> plain-English communication for students writing in English as an additional language.</li>
            </ul>
        </div>
        <aside class="space-y-6 lg:col-span-4 lg:col-start-9">
            <div class="photo-frame aspect-[4/5] overflow-hidden rounded-lg">
                <x-photo name="reading-room-rotunda" alt="A circular university reading room lined with bookshelves under tall arched windows" sizes="(min-width: 1024px) 30vw, 100vw" class="h-full w-full object-cover object-[60%_50%]" />
            </div>
            <div class="card p-6">
                <p class="text-xs font-semibold tracking-[0.16em] text-bronze uppercase">What we do not claim</p>
                <p class="mt-3 text-sm text-ink-soft">We have no formal partnership with, or endorsement from, any university or college. Institution names that appear on this site describe where students we have supported were studying, not an affiliation.</p>
            </div>
        </aside>
    </section>

    @include('pages.partials.contact-band')
</x-layouts.public>
