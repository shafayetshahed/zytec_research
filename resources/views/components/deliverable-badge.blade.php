@props(['status'])
@php
    $classes = match ($status) {
        \App\Enums\DeliverableStatus::Approved => 'bg-success-bg text-success',
        \App\Enums\DeliverableStatus::Returned => 'bg-warning-bg text-warning',
        default => 'bg-[#e8f0fb] text-royal-dark',
    };
@endphp
<span {{ $attributes->merge(['class' => 'badge '.$classes]) }}>{{ $status->label() }}</span>
