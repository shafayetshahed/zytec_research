@if ($errors->any())
    <div class="alert-error alert" role="alert" tabindex="-1" id="error-summary">
        <x-icon name="circle-alert" class="mt-0.5 size-5" />
        <div>
            <p class="font-semibold">Please correct the following:</p>
            <ul class="mt-1 list-disc pl-5">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
