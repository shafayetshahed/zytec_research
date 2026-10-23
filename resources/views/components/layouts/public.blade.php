@props([
    'title',
    'description',
    'canonical' => null,
    'breadcrumbs' => [],
    'schema' => [],
    'noindex' => false,
])
@php
    $nav = [
        ['Home', route('home'), 'home'],
        ['About', route('about'), 'about'],
        ['Services', route('services.index'), 'services.*'],
        ['Subjects', route('subjects'), 'subjects'],
        ['Portfolio', route('portfolio'), 'portfolio'],
        ['How it works', route('how-it-works'), 'how-it-works'],
        ['FAQs', route('faq'), 'faq'],
        ['Contact', route('contact'), 'contact'],
    ];
    $user = auth()->user();
    $portalUrl = $user ? ($user->isAdmin() ? route('admin.dashboard') : route('student.dashboard')) : route('login');
    $briefUrl = $user && ! $user->isAdmin() ? route('student.submissions.create') : route('register');
@endphp
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <x-head-common />
    <x-seo :title="$title" :description="$description" :canonical="$canonical" :breadcrumbs="$breadcrumbs" :schema="$schema" :noindex="$noindex" />
</head>
<body class="flex min-h-screen flex-col">
    <a href="#main" class="skip-link">Skip to main content</a>

    <div class="bg-ink text-[0.8125rem] text-white/85">
        <div class="container-page flex flex-wrap items-center justify-between gap-x-6 gap-y-1 py-2">
            <p class="hidden sm:block">Academic guidance and research support for students in the UK, Australia, Canada and the USA</p>
            <div class="flex items-center gap-5">
                <a href="{{ config('zytecs.business.whatsapp_url') }}" class="inline-flex items-center gap-1.5 text-white no-underline hover:underline" rel="noopener" target="_blank">
                    <x-icon name="message-circle" class="size-4" />WhatsApp<span class="sr-only"> (opens in a new tab)</span>
                </a>
                <a href="mailto:{{ config('zytecs.business.email') }}" class="inline-flex items-center gap-1.5 text-white no-underline hover:underline">
                    <x-icon name="mail" class="size-4" />{{ config('zytecs.business.email') }}
                </a>
            </div>
        </div>
    </div>

    <header class="sticky top-0 z-40 border-b border-line bg-paper/95 backdrop-blur supports-[backdrop-filter]:bg-paper/85">
        <div class="container-page flex max-w-7xl items-center justify-between gap-4 py-3">
            <a href="{{ route('home') }}" class="shrink-0" aria-label="Zytecs Research home">
                <x-logo :width="150" class="h-auto w-[132px] sm:w-[150px]" />
            </a>

            <nav aria-label="Main" class="hidden xl:block">
                <ul class="flex items-center">
                    @foreach ($nav as [$label, $url, $pattern])
                        <li>
                            <a href="{{ $url }}" @if (request()->routeIs($pattern)) aria-current="page" @endif
                               class="rounded-md px-2 py-2 text-[0.9rem] font-medium whitespace-nowrap text-ink no-underline hover:bg-ink/5 aria-[current=page]:text-royal aria-[current=page]:underline aria-[current=page]:decoration-2 aria-[current=page]:underline-offset-8">{{ $label }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <div class="flex items-center gap-2">
                <a href="{{ $portalUrl }}" class="btn btn-outline btn-sm hidden whitespace-nowrap sm:inline-flex">
                    <x-icon name="user" class="size-4" />{{ $user ? 'My account' : 'Student portal' }}
                </a>
                <a href="{{ $briefUrl }}" class="btn btn-primary btn-sm hidden whitespace-nowrap sm:inline-flex">Submit your brief</a>
                <button type="button" class="btn btn-outline btn-sm xl:hidden" data-nav-toggle aria-expanded="false" aria-controls="mobile-nav">
                    <span data-icon-open><x-icon name="menu" class="size-5" /></span>
                    <span data-icon-close hidden><x-icon name="x" class="size-5" /></span>
                    <span>Menu</span>
                </button>
            </div>
        </div>

        <nav id="mobile-nav" aria-label="Main" class="border-t border-line bg-paper xl:hidden" hidden>
            <ul class="container-page grid gap-1 py-4 sm:grid-cols-2">
                @foreach ($nav as [$label, $url, $pattern])
                    <li>
                        <a href="{{ $url }}" @if (request()->routeIs($pattern)) aria-current="page" @endif
                           class="block rounded-md px-3 py-2.5 font-medium text-ink no-underline hover:bg-ink/5 aria-[current=page]:bg-ink/5 aria-[current=page]:text-royal">{{ $label }}</a>
                    </li>
                @endforeach
                <li class="mt-2 flex flex-col gap-2 sm:col-span-2 sm:flex-row">
                    <a href="{{ $portalUrl }}" class="btn btn-outline">{{ $user ? 'My account' : 'Student portal' }}</a>
                    <a href="{{ $briefUrl }}" class="btn btn-primary">Submit your brief</a>
                </li>
            </ul>
        </nav>
    </header>

    <main id="main" class="flex-1" tabindex="-1">
        @if (session('status') || session('error'))
            <div class="container-page pt-6"><x-flash /></div>
        @endif
        {{ $slot }}
    </main>

    <footer class="mt-auto border-t border-line bg-paper-deep">
        <div class="container-page grid gap-10 py-14 md:grid-cols-12">
            <div class="md:col-span-4">
                <x-logo :width="170" class="h-auto w-[170px]" />
                <p class="mt-5 max-w-sm text-[0.95rem] text-ink-soft">
                    Academic guidance, research support, feedback and proofreading for international students. Around {{ config('zytecs.business.experience_years') }} years of experience across many disciplines.
                </p>
                <p class="mt-4 max-w-sm text-sm text-muted">
                    We support learning. We do not produce work for students to submit as their own. <a href="{{ route('academic-integrity') }}">Our integrity commitment</a>.
                </p>
            </div>

            <nav aria-label="Services" class="md:col-span-3">
                <h2 class="font-sans text-xs font-semibold tracking-[0.16em] text-bronze uppercase">Services</h2>
                <ul class="mt-4 space-y-2 text-[0.95rem]">
                    <li><a class="text-ink no-underline hover:underline" href="{{ route('services.show', 'research-proposals') }}">Research proposals</a></li>
                    <li><a class="text-ink no-underline hover:underline" href="{{ route('services.show', 'dissertation-guidance') }}">Dissertation guidance</a></li>
                    <li><a class="text-ink no-underline hover:underline" href="{{ route('services.show', 'data-analysis') }}">Data analysis guidance</a></li>
                    <li><a class="text-ink no-underline hover:underline" href="{{ route('services.show', 'proofreading-editing') }}">Proofreading and referencing</a></li>
                    <li><a class="text-ink no-underline hover:underline" href="{{ route('services.index') }}">All services</a></li>
                </ul>
            </nav>

            <nav aria-label="Company" class="md:col-span-2">
                <h2 class="font-sans text-xs font-semibold tracking-[0.16em] text-bronze uppercase">Company</h2>
                <ul class="mt-4 space-y-2 text-[0.95rem]">
                    <li><a class="text-ink no-underline hover:underline" href="{{ route('about') }}">About us</a></li>
                    <li><a class="text-ink no-underline hover:underline" href="{{ route('why-choose-us') }}">Why choose us</a></li>
                    <li><a class="text-ink no-underline hover:underline" href="{{ route('portfolio') }}">Portfolio</a></li>
                    <li><a class="text-ink no-underline hover:underline" href="{{ route('subjects') }}">Subjects</a></li>
                    <li><a class="text-ink no-underline hover:underline" href="{{ route('faq') }}">FAQs</a></li>
                </ul>
            </nav>

            <div class="md:col-span-3">
                <h2 class="font-sans text-xs font-semibold tracking-[0.16em] text-bronze uppercase">Contact</h2>
                <ul class="mt-4 space-y-3 text-[0.95rem]">
                    <li>
                        <a class="inline-flex items-center gap-2 font-semibold text-ink no-underline hover:underline" href="{{ config('zytecs.business.whatsapp_url') }}" rel="noopener" target="_blank">
                            <x-icon name="message-circle" class="size-4 text-[#1d6f42]" />WhatsApp (preferred)<span class="sr-only"> (opens in a new tab)</span>
                        </a>
                    </li>
                    <li>
                        <a class="inline-flex items-center gap-2 text-ink no-underline hover:underline" href="mailto:{{ config('zytecs.business.email') }}">
                            <x-icon name="mail" class="size-4" />{{ config('zytecs.business.email') }}
                        </a>
                    </li>
                    <li class="text-sm text-muted">
                        <span class="flex items-center gap-2"><x-icon name="phone" class="size-4" /><span><strong class="font-semibold text-ink">Emergency contact only:</strong> <a href="tel:{{ config('zytecs.business.emergency_phone') }}" class="whitespace-nowrap text-ink">{{ config('zytecs.business.emergency_phone_display') }}</a></span></span>
                        <span class="mt-1 block pl-6">For urgent issues with an active request. Please use WhatsApp for enquiries.</span>
                    </li>
                </ul>
            </div>
        </div>
        <div class="border-t border-line">
            <div class="container-page flex flex-col gap-3 py-5 text-sm text-muted sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ now()->year }} Zytecs Research. All rights reserved.</p>
                <ul class="flex flex-wrap gap-x-5 gap-y-1">
                    <li><a class="text-muted hover:text-ink" href="{{ route('privacy') }}">Privacy policy</a></li>
                    <li><a class="text-muted hover:text-ink" href="{{ route('terms') }}">Terms of service</a></li>
                    <li><a class="text-muted hover:text-ink" href="{{ route('academic-integrity') }}">Academic integrity</a></li>
                    <li><a class="text-muted hover:text-ink" href="{{ route('sitemap') }}">Sitemap</a></li>
                </ul>
            </div>
        </div>
    </footer>
</body>
</html>
