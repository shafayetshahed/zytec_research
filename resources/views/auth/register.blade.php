<x-layouts.auth title="Create an account" heading="Create your student account" intro="You will need to verify your email address before submitting a brief." photo="students-discussing-laptop" photo-alt="">
    <x-error-summary />
    <form method="POST" action="{{ route('register') }}" class="mt-4 space-y-5" novalidate data-submit-once>
        @csrf
        <x-text-field name="name" label="Full name" autocomplete="name" autofocus />
        <x-text-field name="email" label="Email address" type="email" autocomplete="email" hint="We will send a six-digit verification code to this address." />
        <x-text-field name="whatsapp_number" label="WhatsApp number" type="tel" autocomplete="tel" inputmode="tel" placeholder="+44 7700 900123" hint="Include your country code. We use WhatsApp to discuss your requests." />
        <x-text-field name="password" label="Password" type="password" autocomplete="new-password" hint="At least 10 characters, including letters and numbers." />
        <x-text-field name="password_confirmation" label="Confirm password" type="password" autocomplete="new-password" />
        <div class="field">
            <label class="flex items-start gap-3 text-sm text-ink-soft">
                <input type="checkbox" name="terms" value="1" class="checkbox" required aria-required="true" @checked(old('terms')) @error('terms') aria-invalid="true" aria-describedby="terms-error" @enderror>
                <span>I have read and accept the <a href="{{ route('terms') }}" target="_blank" rel="noopener">Terms of Service</a> and <a href="{{ route('privacy') }}" target="_blank" rel="noopener">Privacy Policy</a>.</span>
            </label>
            <x-field-error name="terms" />
        </div>
        <button type="submit" class="btn btn-primary w-full"><span data-busy-label="Creating account…">Create account</span></button>
    </form>
    <p class="mt-8 border-t border-line pt-6 text-sm text-ink-soft">
        Already registered? <a href="{{ route('login') }}" class="font-semibold">Sign in</a>
    </p>
</x-layouts.auth>
