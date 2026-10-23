@props(['name', 'alt', 'sizes' => '(min-width: 1024px) 50vw, 100vw', 'width' => 1400, 'height' => 933, 'eager' => false])
<img src="{{ asset("images/photos/{$name}-1400.webp") }}"
     srcset="{{ asset("images/photos/{$name}-800.webp") }} 800w, {{ asset("images/photos/{$name}-1400.webp") }} 1400w"
     sizes="{{ $sizes }}" width="{{ $width }}" height="{{ $height }}"
     alt="{{ $alt }}"
     @if ($eager) fetchpriority="high" @else loading="lazy" @endif
     decoding="async"
     {{ $attributes->merge(['class' => 'h-full w-full object-cover']) }}>
