@props(['title'])
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <x-head-common />
    <title>{{ $title }} | Team portal | Zytecs Research</title>
    <meta name="robots" content="noindex, nofollow">
</head>
<body class="flex min-h-screen flex-col bg-paper">
    <a href="#main" class="skip-link">Skip to main content</a>
    <header class="border-b border-line bg-card">
        <div class="container-page flex flex-wrap items-center justify-between gap-4 py-3">
            <div class="flex items-center gap-4">
                <a href="{{ route('team.dashboard') }}" aria-label="Team portal home"><x-logo :width="128" class="h-auto w-[128px]" /></a>
                <span class="hidden border-l border-line pl-4 text-sm font-semibold tracking-wide text-muted uppercase sm:inline">Team portal</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="hidden text-sm text-muted sm:inline">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm"><x-icon name="log-out" class="size-4" />Sign out</button>
                </form>
            </div>
        </div>
    </header>
    <main id="main" tabindex="-1" class="container-page flex-1 py-8 sm:py-10">
        @if (session('status') || session('error'))
            <div class="mb-6 space-y-3"><x-flash /></div>
        @endif
        {{ $slot }}
    </main>
    <footer class="border-t border-line py-6 text-sm text-muted">
        <div class="container-page">Keep all client work confidential. Never contact students directly; use the messages on each order.</div>
    </footer>
</body>
</html>
