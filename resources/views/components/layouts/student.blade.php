@props(['title'])
@php
    $links = [
        ['Dashboard', route('student.dashboard'), 'student.dashboard', 'layout-dashboard'],
        ['Submit a brief', route('student.submissions.create'), 'student.submissions.create', 'file-up'],
        ['Profile', route('student.profile'), 'student.profile', 'settings'],
    ];
@endphp
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <x-head-common />
    <title>{{ $title }} | Student portal | Zytecs Research</title>
    <meta name="robots" content="noindex, nofollow">
</head>
<body class="flex min-h-screen flex-col bg-paper">
    <a href="#main" class="skip-link">Skip to main content</a>
    <header class="border-b border-line bg-card">
        <div class="container-page flex flex-wrap items-center justify-between gap-4 py-3">
            <div class="flex items-center gap-4">
                <a href="{{ route('home') }}" aria-label="Zytecs Research home"><x-logo :width="128" class="h-auto w-[128px]" /></a>
                <span class="hidden border-l border-line pl-4 text-sm font-semibold tracking-wide text-muted uppercase sm:inline">Student portal</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm"><x-icon name="log-out" class="size-4" />Sign out</button>
            </form>
        </div>
        <nav aria-label="Student portal" class="container-page -mb-px overflow-x-auto">
            <ul class="flex gap-1">
                @foreach ($links as [$label, $url, $pattern, $icon])
                    <li>
                        <a href="{{ $url }}" @if (request()->routeIs($pattern)) aria-current="page" @endif
                           class="inline-flex items-center gap-2 border-b-2 border-transparent px-3 py-3 text-sm font-medium whitespace-nowrap text-ink-soft no-underline hover:text-ink aria-[current=page]:border-royal aria-[current=page]:text-royal">
                            <x-icon :name="$icon" class="size-4" />{{ $label }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </header>
    <main id="main" tabindex="-1" class="container-page flex-1 py-8 sm:py-10">
        @if (session('status') || session('error'))
            <div class="mb-6 space-y-3"><x-flash /></div>
        @endif
        {{ $slot }}
    </main>
    <footer class="border-t border-line py-6 text-sm text-muted">
        <div class="container-page flex flex-wrap items-center justify-between gap-3">
            <p>Need help? <a href="{{ config('zytecs.business.whatsapp_url') }}" rel="noopener" target="_blank">Message us on WhatsApp</a> or email <a href="mailto:{{ config('zytecs.business.email') }}">{{ config('zytecs.business.email') }}</a>.</p>
            <p><a class="text-muted" href="{{ route('privacy') }}">Privacy</a> · <a class="text-muted" href="{{ route('terms') }}">Terms</a></p>
        </div>
    </footer>
</body>
</html>
