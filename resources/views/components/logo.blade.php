@props(['width' => 168])
<img src="{{ asset('images/brand/logo-480.webp') }}"
     srcset="{{ asset('images/brand/logo-480.webp') }} 480w, {{ asset('images/brand/logo-960.webp') }} 960w"
     sizes="{{ $width }}px"
     width="{{ $width }}" height="{{ round($width * 544 / 1367) }}"
     alt="Zytecs Research"
     {{ $attributes }}>
