<x-layouts.auth title="Choose a new password" heading="Choose a new password">
    <form method="POST" action="{{ route('password.update') }}" class="space-y-5" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <x-text-field name="email" label="Email address" type="email" autocomplete="email" :value="$email" />
        <x-text-field name="password" label="New password" type="password" autocomplete="new-password" hint="At least 10 characters, including letters and numbers." />
        <x-text-field name="password_confirmation" label="Confirm new password" type="password" autocomplete="new-password" />
        <button type="submit" class="btn btn-primary w-full">Reset password</button>
    </form>
</x-layouts.auth>
