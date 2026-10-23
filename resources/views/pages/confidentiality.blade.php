<x-layouts.public title="Confidentiality" description="How Zytecs Research keeps client information strictly confidential: we do not share your identity, documents or use of our services with universities or anyone else unless the law requires it." :breadcrumbs="[['Confidentiality']]">
    <x-page-header eyebrow="Our commitment" title="Strictly confidential, from first message to final file" :breadcrumbs="[['Confidentiality']]"
        lede="Students trust us with their names, contact details, briefs and drafts. Protecting that trust has been central to how we have worked for around {{ config('zytecs.business.experience_years') }} years." />

    <section class="container-page grid gap-12 py-16 lg:grid-cols-12">
        <div class="lg:col-span-8">
            @include('pages.partials.policy-review-notice')

            <div class="grid gap-px overflow-hidden rounded-lg border border-line bg-line sm:grid-cols-2">
                @foreach ([
                    ['shield-check', 'Your identity stays private', 'We do not tell your university, college, employer, family or anyone else that you use our services.'],
                    ['lock', 'Your documents stay private', 'Briefs and drafts are stored privately and seen only by the team members working on your request.'],
                    ['eye', 'Nothing is published', 'We never publish your name, institution, documents, grades or messages, and never use them in marketing without written permission.'],
                    ['user', 'Only what we need', 'We ask only for the details needed to help you, and never for university passwords or login details.'],
                ] as [$icon, $title, $text])
                    <div class="bg-card p-6">
                        <x-icon :name="$icon" class="size-6 text-royal" />
                        <h2 class="mt-3 font-serif text-xl">{{ $title }}</h2>
                        <p class="mt-2 text-[0.95rem] text-ink-soft">{{ $text }}</p>
                    </div>
                @endforeach
            </div>

            <div class="prose-zr mt-12">
                <h2>Our confidentiality promise</h2>
                <p>Everything you share with Zytecs Research is treated as strictly confidential. That includes your name, email address and WhatsApp number, your university or college and course, your assignment briefs, drafts and supporting files, our messages and calls with you, and the fact that you have contacted us at all.</p>
                <p>We do not share any of this with your university or college, your lecturers or supervisors, your employer, sponsors, family, or any other person or organisation. We do not sell or rent client information, and we do not pass it to advertisers or data brokers.</p>

                <h2>The only exceptions</h2>
                <p>We will only disclose client information in the following limited situations:</p>
                <ul>
                    <li><strong>When you ask us to</strong>, in writing, for a specific purpose.</li>
                    <li><strong>When the law requires it</strong>, for example a valid court order or a lawful request from a regulator or law-enforcement authority that we are legally obliged to follow. We will check that any such request is genuine and lawful, disclose only what is strictly required, and tell you about it unless the law prevents us from doing so.</li>
                    <li><strong>To protect someone's safety</strong>, where there is a serious and immediate risk of harm to a person.</li>
                </ul>
                <p>The service providers that run our website and email (hosting and email delivery) process data on our behalf only to provide those services. They do not use your information for their own purposes.</p>

                <h2>Who inside Zytecs Research can see your information</h2>
                <ul>
                    <li>Access is limited to the team members who need it to review or support your request.</li>
                    <li>Everyone who works with us is required to keep client information confidential, both while working with us and afterwards.</li>
                    <li>Team members working on subject support see your brief and requirements. They do not need, and are not given, more of your personal details than the task requires.</li>
                    <li>Administrative access to the student portal is restricted to named administrators, and every sign-in, file download and status change is recorded in an audit log.</li>
                </ul>

                <h2>How we protect your information</h2>
                <ul>
                    <li><strong>Encrypted connections:</strong> the website uses HTTPS, so information you send is encrypted in transit.</li>
                    <li><strong>Private file storage:</strong> uploaded files are stored outside the public part of the website under random file names, and can only be downloaded by you or authorised staff after signing in.</li>
                    <li><strong>Verified accounts:</strong> email addresses are verified with a one-time code, passwords are stored only in hashed form, and sign-in attempts are limited to block guessing.</li>
                    <li><strong>No public AI tools:</strong> we do not upload your documents to public AI services, and we never use them to train AI systems.</li>
                    <li><strong>Limited retention:</strong> completed and closed requests, and their files, are deleted automatically after {{ config('zytecs.retention.closed_submission_days') }} days. You can ask us to delete your account and data sooner.</li>
                </ul>

                <h2>Experience you can rely on</h2>
                <p>Over roughly {{ config('zytecs.business.experience_years') }} years we have worked with students studying in the United Kingdom and Australia, and we welcome students in Canada, the United States and elsewhere. Our records cover business, IT, engineering, hospitality, law, education and research methods. In all that time, discretion has been part of how we work. Our <a href="{{ route('portfolio') }}">portfolio</a> shows the breadth of that experience without revealing a single client.</p>

                <h2>Privacy laws in the countries our students study in</h2>
                <p>Zytecs Research operates from Bangladesh and works with students in several countries. Each has its own privacy law. We have shaped our practices around the principles these laws share: collect only what is needed, use it only for the purpose it was given, keep it secure, keep it no longer than necessary, be open about what we do, and respect people's rights over their own information.</p>
            </div>

            <div class="card mt-6 overflow-x-auto">
                <table class="data-table">
                    <caption class="sr-only">How our practices relate to privacy laws by country</caption>
                    <thead>
                        <tr><th scope="col">Country</th><th scope="col">Main privacy framework</th><th scope="col">How we reflect its principles</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row" class="!text-sm !normal-case !tracking-normal !text-ink">United Kingdom</th>
                            <td>UK GDPR and the Data Protection Act 2018</td>
                            <td>Data minimisation, purpose limitation, security of processing, defined retention, and requests for access or deletion.</td>
                        </tr>
                        <tr>
                            <th scope="row" class="!text-sm !normal-case !tracking-normal !text-ink">Australia</th>
                            <td>Privacy Act 1988 and the Australian Privacy Principles</td>
                            <td>Open handling of personal information, collection only for our services, no disclosure for unrelated purposes, security, and access and correction on request.</td>
                        </tr>
                        <tr>
                            <th scope="row" class="!text-sm !normal-case !tracking-normal !text-ink">Canada</th>
                            <td>Personal Information Protection and Electronic Documents Act (PIPEDA)</td>
                            <td>Consent for collection, limited use and disclosure, safeguards, accountability, and individual access.</td>
                        </tr>
                        <tr>
                            <th scope="row" class="!text-sm !normal-case !tracking-normal !text-ink">United States</th>
                            <td>No single federal law; state laws such as the California Consumer Privacy Act (as amended by the CPRA)</td>
                            <td>No sale or sharing of personal information, transparency about what we collect, and deletion on request.</td>
                        </tr>
                        <tr>
                            <th scope="row" class="!text-sm !normal-case !tracking-normal !text-ink">Bangladesh</th>
                            <td>Where we operate; applicable Bangladeshi law on data protection and cyber security</td>
                            <td>Secure storage, restricted access and lawful handling of client data.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="mt-3 text-sm text-muted">This table explains our approach in plain language. It is not legal advice, and it is not a claim of certification or of formal compliance with any particular law.</p>

            <div class="prose-zr mt-12">
                <h2>Your rights and choices</h2>
                <ul>
                    <li>Ask what information we hold about you and receive a copy.</li>
                    <li>Correct details that are wrong. You can update your name and WhatsApp number yourself in your profile.</li>
                    <li>Ask us to delete your account, requests and files. You can do this from your profile page.</li>
                    <li>Ask questions or raise a concern about how your information is handled.</li>
                </ul>
                <p>Email <a href="mailto:{{ config('zytecs.business.email') }}">{{ config('zytecs.business.email') }}</a> or message us on WhatsApp. We will respond as quickly as we can.</p>

                <h2>Confidentiality and academic integrity</h2>
                <p>Confidentiality protects your privacy. It sits alongside, and does not replace, your responsibility to follow your institution's rules. Our services are learning support, as described in our <a href="{{ route('academic-integrity') }}">academic integrity commitment</a>.</p>

                <h2>Related policies</h2>
                <p>For full details of what we collect, how long we keep it and how to exercise your rights, see our <a href="{{ route('privacy') }}">Privacy Policy</a> and <a href="{{ route('terms') }}">Terms of Service</a>.</p>
            </div>
        </div>

        <aside class="space-y-6 lg:col-span-4">
            <div class="card p-6 lg:sticky lg:top-28">
                <x-icon name="shield-check" class="size-8 text-royal" />
                <h2 class="mt-3 font-serif text-xl">In short</h2>
                <ul class="mt-3 space-y-2 text-sm text-ink-soft">
                    <li class="flex gap-2"><x-icon name="check" class="mt-0.5 size-4 text-success" />We never contact your university about you.</li>
                    <li class="flex gap-2"><x-icon name="check" class="mt-0.5 size-4 text-success" />We never publish or sell your information.</li>
                    <li class="flex gap-2"><x-icon name="check" class="mt-0.5 size-4 text-success" />Files are private and access is logged.</li>
                    <li class="flex gap-2"><x-icon name="check" class="mt-0.5 size-4 text-success" />Disclosure only when you ask or the law requires.</li>
                    <li class="flex gap-2"><x-icon name="x" class="mt-0.5 size-4 text-danger" />We never ask for your university password.</li>
                </ul>
                <a href="{{ config('zytecs.business.whatsapp_url') }}" class="btn btn-whatsapp mt-6 w-full" rel="noopener" target="_blank"><x-icon name="message-circle" class="size-5" />Ask us on WhatsApp<span class="sr-only"> (opens in a new tab)</span></a>
            </div>
        </aside>
    </section>

    @include('pages.partials.contact-band')
</x-layouts.public>
