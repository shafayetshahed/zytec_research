@if (session('status'))
    <div class="alert-success alert" role="status">
        <x-icon name="circle-check" class="mt-0.5 size-5" />
        <p>{{ session('status') }}</p>
    </div>
@endif
@if (session('error'))
    <div class="alert-error alert" role="alert">
        <x-icon name="circle-alert" class="mt-0.5 size-5" />
        <p>{{ session('error') }}</p>
    </div>
@endif
