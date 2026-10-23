@props(['status'])
@php
    $classes = match ($status) {
        \App\Enums\PaymentStatus::PaidInFull => 'bg-success-bg text-success',
        \App\Enums\PaymentStatus::DepositPaid => 'bg-[#e4f3fb] text-[#0b5a86]',
        \App\Enums\PaymentStatus::PartPaid, \App\Enums\PaymentStatus::AwaitingPayment => 'bg-warning-bg text-warning',
        default => 'bg-line-soft text-muted',
    };
@endphp
<span {{ $attributes->merge(['class' => 'badge '.$classes]) }}>{{ $status->label() }}</span>
