<x-layouts.public title="How it works" description="How to work with Zytecs Research: submit your brief, we review it, confirm scope, timeline and price, and support begins only once you agree." :breadcrumbs="[['How it works']]">
    <x-page-header eyebrow="How it works" title="A clear process, agreed before anything starts" :breadcrumbs="[['How it works']]"
        lede="We never promise a deadline or a price before reading your brief and checking our availability." />

    <section class="container-page grid gap-12 py-16 lg:grid-cols-12">
        <ol class="space-y-0 lg:col-span-7">
            @foreach ([
                ['Send an enquiry or submit your brief', 'Create a student account, verify your email and upload your brief with the assignment name and module code. You can also start with a WhatsApp message or the contact form.', 'file-up'],
                ['We review the requirements', 'We read the brief, the marking criteria and any guidance you include, and check whether the request is within our experience.', 'eye'],
                ['We confirm scope, availability, timeline and price', 'We tell you what we can help with, the format of the support, when it can be delivered and what it costs. If we cannot help, we say so.', 'clipboard-list'],
                ['You agree the support arrangement', 'Nothing begins until you have agreed. You are under no obligation before that point.', 'check'],
                ['The agreed guidance or service is provided', 'Explanations, feedback, guidance sessions or proofreading, as agreed.', 'book-open'],
                ['Follow-up and revisions', 'Questions and any revisions included in the agreement are handled under the agreed terms.', 'refresh-cw'],
            ] as [$title, $text, $icon])
                <li class="grid grid-cols-[3.5rem_1fr] gap-5 border-l border-line pb-10 pl-0 last:pb-0">
                    <span class="-ml-[1.15rem] flex size-9 items-center justify-center rounded-full border border-bronze/60 bg-paper font-serif text-lg text-bronze">{{ $loop->iteration }}</span>
                    <div class="-ml-4">
                        <h2 class="flex items-center gap-2 font-serif text-2xl">{{ $title }}</h2>
                        <p class="mt-2 text-ink-soft">{{ $text }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
        <aside class="space-y-6 lg:col-span-4 lg:col-start-9">
            <div class="photo-frame aspect-[4/3] overflow-hidden rounded-lg">
                <x-photo name="students-discussing-laptop" alt="Two students discussing work on a laptop at a white table" sizes="(min-width: 1024px) 30vw, 100vw" />
            </div>
            <div class="card p-6">
                <h2 class="font-serif text-xl">What to include</h2>
                <ul class="mt-3 space-y-2 text-sm text-ink-soft">
                    <li class="flex gap-2"><x-icon name="check" class="mt-0.5 size-4 text-success" />The assignment brief and marking rubric</li>
                    <li class="flex gap-2"><x-icon name="check" class="mt-0.5 size-4 text-success" />Your module code and deadline</li>
                    <li class="flex gap-2"><x-icon name="check" class="mt-0.5 size-4 text-success" />Your draft, if you want feedback on it</li>
                    <li class="flex gap-2"><x-icon name="x" class="mt-0.5 size-4 text-danger" />Never your university password</li>
                </ul>
                <a href="{{ auth()->user()?->isStudent() ? route('student.submissions.create') : route('register') }}" class="btn btn-primary mt-6 w-full">Submit your brief</a>
            </div>
        </aside>
    </section>
</x-layouts.public>
