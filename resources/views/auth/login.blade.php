<x-layouts.auth title="Sign in" heading="Sign in to your account" intro="Students and the Zytecs Research team sign in here.">
    <form method="POST" action="{{ route('login') }}" class="space-y-5" novalidate>
        @csrf
        <x-text-field name="email" label="Email address" type="email" autocomplete="email" autofocus />
        <x-text-field name="password" label="Password" type="password" autocomplete="current-password" />
        <div class="flex items-center justify-between gap-4">
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember" value="1" class="checkbox mt-0" @checked(old('remember'))> Keep me signed in
            </label>
            <a href="{{ route('password.request') }}" class="text-sm font-medium">Forgot password?</a>
        </div>
        <button type="submit" class="btn btn-primary w-full">Sign in</button>
    </form>
    <p class="mt-8 border-t border-line pt-6 text-sm text-ink-soft">
        New to Zytecs Research? <a href="{{ route('register') }}" class="font-semibold">Create a student account</a> to submit a brief.
    </p>
</x-layouts.auth>
