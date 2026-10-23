@props(['title'])
@php
    $links = [
        ['Overview', route('admin.dashboard'), 'admin.dashboard', 'layout-dashboard'],
        ['Submissions', route('admin.submissions.index'), 'admin.submissions.*', 'inbox'],
        ['Students & clients', route('admin.students.index'), 'admin.students.*|admin.clients.*', 'users'],
        ['Clients & dues', route('admin.dues'), 'admin.dues*', 'circle-alert'],
        ['Accounting', route('admin.accounting'), 'admin.accounting*|admin.orders.*', 'calculator'],
        ['Enquiries', route('admin.enquiries.index'), 'admin.enquiries.*', 'message-circle'],
        ['Notifications', route('admin.notifications.index'), 'admin.notifications.*', 'bell'],
        ['Audit log', route('admin.audit'), 'admin.audit', 'history'],
        ['Settings', route('admin.settings'), 'admin.settings', 'settings'],
    ];
@endphp
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <x-head-common />
    <title>{{ $title }} | Admin | Zytecs Research</title>
    <meta name="robots" content="noindex, nofollow">
</head>
<body class="min-h-screen bg-[#f3f1ec]">
    <a href="#main" class="skip-link">Skip to main content</a>
    <div class="lg:grid lg:min-h-screen lg:grid-cols-[15rem_1fr]">
        <aside class="border-b border-white/10 bg-ink text-white lg:border-r lg:border-b-0">
            <div class="flex items-center justify-between gap-3 px-5 py-4 lg:block">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 text-white no-underline">
                    <img src="{{ asset('images/brand/mark-192.png') }}" alt="" width="36" height="36" class="size-9 rounded-md bg-white p-1">
                    <span class="leading-tight"><span class="block font-serif text-lg">Zytecs Research</span><span class="block text-xs tracking-wider text-white/60 uppercase">Administration</span></span>
                </a>
            </div>
            <nav aria-label="Administration" class="overflow-x-auto px-3 pb-3 lg:pb-6">
                <ul class="flex gap-1 lg:flex-col">
                    @foreach ($links as [$label, $url, $pattern, $icon])
                        <li>
                            <a href="{{ $url }}" @if (request()->routeIs(...explode("|", $pattern))) aria-current="page" @endif
                               class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium whitespace-nowrap text-white/80 no-underline hover:bg-white/10 hover:text-white aria-[current=page]:bg-white/15 aria-[current=page]:text-white">
                                <x-icon :name="$icon" class="size-4" />{{ $label }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </aside>
        <div class="flex min-w-0 flex-col">
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-line bg-card px-4 py-3 sm:px-8">
                <p class="text-sm text-muted">Signed in as <span class="font-semibold text-ink">{{ auth()->user()->name }}</span></p>
                <div class="flex items-center gap-2">
                    <a href="{{ route('home') }}" class="btn btn-outline btn-sm">View website</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm"><x-icon name="log-out" class="size-4" />Sign out</button>
                    </form>
                </div>
            </header>
            <main id="main" tabindex="-1" class="flex-1 px-4 py-8 sm:px-8">
                @if (session('status') || session('error'))
            <div class="mb-6 space-y-3"><x-flash /></div>
        @endif
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
