@props([
    'title',
    'description',
    'canonical' => null,
    'image' => null,
    'type' => 'website',
    'noindex' => false,
    'breadcrumbs' => [],
    'schema' => [],
])
@php
    $siteName = config('zytecs.business.name');
    $fullTitle = $title === $siteName ? $title : $title.' | '.$siteName;
    $canonical ??= url()->current();
    $image ??= asset('images/brand/og-default.jpg');

    $graph = [
        [
            '@type' => 'Organization',
            '@id' => url('/').'/#organization',
            'name' => $siteName,
            'url' => url('/'),
            'logo' => asset('images/brand/mark-512.png'),
            'email' => config('zytecs.business.email'),
            'contactPoint' => [[
                '@type' => 'ContactPoint',
                'contactType' => 'customer support',
                'email' => config('zytecs.business.email'),
                'availableLanguage' => ['English'],
            ]],
        ],
        [
            '@type' => 'WebSite',
            '@id' => url('/').'/#website',
            'url' => url('/'),
            'name' => $siteName,
            'publisher' => ['@id' => url('/').'/#organization'],
        ],
    ];

    if (count($breadcrumbs) > 0) {
        $graph[] = [
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect([['Home', route('home')], ...$breadcrumbs])->values()->map(fn ($crumb, $i) => array_filter([
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $crumb[0],
                'item' => $crumb[1] ?? null,
            ]))->all(),
        ];
    }

    $graph = array_merge($graph, $schema);
@endphp
<title>{{ $fullTitle }}</title>
<meta name="description" content="{{ $description }}">
@if ($noindex)
    <meta name="robots" content="noindex, nofollow">
@else
    <link rel="canonical" href="{{ $canonical }}">
@endif
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:type" content="{{ $type }}">
<meta property="og:title" content="{{ $fullTitle }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $image }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:locale" content="en_GB">
<meta name="twitter:card" content="summary_large_image">
@unless ($noindex)
    <script type="application/ld+json">{!! json_encode(['@'.'context' => 'https://schema.org', '@'.'graph' => $graph], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endunless
