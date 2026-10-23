<x-layouts.auth title="Verify your email" heading="Check your email" intro="Enter the six-digit code we sent to {{ $maskedEmail }}. The code expires {{ $expiresMinutes }} minutes after it is sent." photo="desk-books-notes">
    <form method="POST" action="{{ route('verification.verify') }}" class="space-y-5" novalidate>
        @csrf
        <div class="field">
            <label for="code" class="label">Verification code</label>
            <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]*" maxlength="6" required aria-required="true"
                   class="input max-w-[12rem] text-center font-mono text-2xl tracking-[0.5em]" autofocus
                   @error('code') aria-invalid="true" aria-describedby="code-error" @enderror>
            <x-field-error name="code" />
        </div>
        <button type="submit" class="btn btn-primary w-full">Verify email</button>
    </form>

    <div class="mt-8 space-y-4 border-t border-line pt-6 text-sm text-ink-soft">
        <p>Did not receive it? Check your spam or junk folder. You can request a new code; any earlier code will stop working.</p>
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-outline btn-sm" data-countdown="{{ $resendIn }}" data-label="Send a new code">
                <x-icon name="refresh-cw" class="size-4" /><span data-countdown-label>Send a new code</span>
            </button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm font-medium text-royal underline underline-offset-4">Sign out and use a different email</button>
        </form>
    </div>
</x-layouts.auth>
