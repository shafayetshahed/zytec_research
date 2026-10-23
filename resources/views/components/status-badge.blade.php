@props(['status'])
@php
    $classes = match ($status) {
        \App\Enums\SubmissionStatus::New => 'bg-[#e8f0fb] text-royal-dark',
        \App\Enums\SubmissionStatus::UnderReview, \App\Enums\SubmissionStatus::QuotationSent => 'bg-paper-deep text-ink',
        \App\Enums\SubmissionStatus::AwaitingStudentResponse => 'bg-warning-bg text-warning',
        \App\Enums\SubmissionStatus::Accepted, \App\Enums\SubmissionStatus::InProgress => 'bg-[#e4f3fb] text-[#0b5a86]',
        \App\Enums\SubmissionStatus::Completed => 'bg-success-bg text-success',
        \App\Enums\SubmissionStatus::Closed => 'bg-line-soft text-muted',
        \App\Enums\SubmissionStatus::Declined => 'bg-danger-bg text-danger',
        \App\Enums\NotificationStatus::Sent => 'bg-success-bg text-success',
        \App\Enums\NotificationStatus::Pending => 'bg-warning-bg text-warning',
        \App\Enums\NotificationStatus::Failed => 'bg-danger-bg text-danger',
        default => 'bg-line-soft text-ink',
    };
@endphp
<span {{ $attributes->merge(['class' => 'badge '.$classes]) }}>{{ $status->label() }}</span>
