@props(['title', 'heading', 'intro' => null, 'photo' => 'notebook-and-pen', 'photoAlt' => 'An open notebook and pen on a wooden desk'])
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <x-head-common />
    <title>{{ $title }} | Zytecs Research</title>
    <meta name="robots" content="noindex, nofollow">
</head>
<body class="min-h-screen bg-paper">
    <a href="#main" class="skip-link">Skip to main content</a>
    <div class="grid min-h-screen lg:grid-cols-[1fr_minmax(0,44%)]">
        <div class="flex flex-col">
            <header class="container-page flex items-center justify-between py-6">
                <a href="{{ route('home') }}" aria-label="Zytecs Research home"><x-logo :width="140" class="h-auto w-[140px]" /></a>
                <a href="{{ route('home') }}" class="text-sm font-medium text-ink">Back to website</a>
            </header>
            <main id="main" tabindex="-1" class="flex flex-1 items-start justify-center px-4 pt-6 pb-16 sm:items-center sm:pt-0">
                <div class="w-full max-w-md">
                    <h1 class="font-serif text-3xl sm:text-4xl">{{ $heading }}</h1>
                    @if ($intro)
                        <p class="mt-3 text-ink-soft">{{ $intro }}</p>
                    @endif
                    <div class="mt-6 space-y-3">
                        <x-flash />
                    </div>
                    <div class="mt-6">
                        {{ $slot }}
                    </div>
                </div>
            </main>
        </div>
        <aside class="relative hidden overflow-hidden bg-ink lg:block" aria-hidden="true">
            <x-photo :name="$photo" alt="" sizes="44vw" class="h-full w-full object-cover opacity-80" />
            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-ink via-ink/80 to-transparent p-10 pt-32 text-white">
                <p class="font-serif text-2xl leading-snug">Your documents stay private: stored securely and visible only to you and our team.</p>
                <p class="mt-3 text-sm text-white/75">Our document-handling commitment</p>
            </div>
        </aside>
    </div>
</body>
</html>
