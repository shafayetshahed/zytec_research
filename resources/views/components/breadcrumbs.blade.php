@props(['items' => []])
<nav aria-label="Breadcrumb" class="text-sm">
    <ol class="flex flex-wrap items-center gap-x-2 gap-y-1 text-muted">
        <li><a href="{{ route('home') }}" class="text-muted hover:text-ink">Home</a></li>
        @foreach ($items as $item)
            <li class="flex items-center gap-2">
                <span aria-hidden="true" class="text-line">/</span>
                @if (! empty($item[1]) && ! $loop->last)
                    <a href="{{ $item[1] }}" class="text-muted hover:text-ink">{{ $item[0] }}</a>
                @else
                    <span aria-current="page" class="text-ink">{{ $item[0] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
