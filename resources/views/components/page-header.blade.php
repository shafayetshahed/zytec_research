@props(['eyebrow' => null, 'title', 'lede' => null, 'breadcrumbs' => []])
<header class="border-b border-line bg-paper-deep/50">
    <div class="container-page py-12 sm:py-16">
        @if (count($breadcrumbs))
            <x-breadcrumbs :items="$breadcrumbs" />
        @endif
        <div class="mt-6 max-w-3xl">
            @if ($eyebrow)
                <p class="eyebrow">{{ $eyebrow }}</p>
            @endif
            <h1 class="display mt-4">{{ $title }}</h1>
            @if ($lede)
                <p class="lede mt-5">{{ $lede }}</p>
            @endif
            {{ $slot }}
        </div>
    </div>
</header>
