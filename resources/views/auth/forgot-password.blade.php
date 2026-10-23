<x-layouts.auth title="Reset your password" heading="Reset your password" intro="Enter your account email and we will send you a link to choose a new password.">
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5" novalidate>
        @csrf
        <x-text-field name="email" label="Email address" type="email" autocomplete="email" autofocus />
        <button type="submit" class="btn btn-primary w-full">Email me a reset link</button>
    </form>
    <p class="mt-8 border-t border-line pt-6 text-sm"><a href="{{ route('login') }}">Back to sign in</a></p>
</x-layouts.auth>
