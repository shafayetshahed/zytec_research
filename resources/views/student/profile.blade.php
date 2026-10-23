<x-layouts.student title="Profile and security">
    <p class="eyebrow">Account</p>
    <h1 class="mt-3 font-serif text-3xl sm:text-4xl">Profile and security</h1>

    <div class="mt-8 grid max-w-4xl gap-8">
        <section class="card p-6 sm:p-8" aria-labelledby="profile-heading">
            <h2 id="profile-heading" class="font-serif text-xl">Your details</h2>
            <form method="POST" action="{{ route('student.profile.update') }}" class="mt-5 grid gap-5 sm:max-w-md" novalidate>
                @csrf
                @method('PUT')
                <x-text-field name="name" label="Full name" autocomplete="name" :value="$user->name" />
                <div class="field">
                    <span class="label">Email address</span>
                    <p class="flex flex-wrap items-center gap-2">{{ $user->email }} <span class="badge bg-success-bg text-success">Verified</span></p>
                    <p class="hint">To change your email address, please contact us so we can verify the change.</p>
                </div>
                <x-text-field name="whatsapp_number" label="WhatsApp number" type="tel" inputmode="tel" autocomplete="tel" :value="$user->whatsapp_number" class="font-mono" hint="Include your country code." />
                <div><button type="submit" class="btn btn-primary">Save details</button></div>
            </form>
        </section>

        <section class="card p-6 sm:p-8" aria-labelledby="password-heading">
            <h2 id="password-heading" class="font-serif text-xl">Change password</h2>
            <form method="POST" action="{{ route('student.profile.password') }}" class="mt-5 grid gap-5 sm:max-w-md" novalidate>
                @csrf
                @method('PUT')
                <x-text-field name="current_password" label="Current password" type="password" autocomplete="current-password" />
                <x-text-field name="password" label="New password" type="password" autocomplete="new-password" hint="At least 10 characters, including letters and numbers." />
                <x-text-field name="password_confirmation" label="Confirm new password" type="password" autocomplete="new-password" />
                <div><button type="submit" class="btn btn-ink">Change password</button></div>
            </form>
        </section>

        <section class="card border-danger/30 p-6 sm:p-8" aria-labelledby="delete-heading">
            <h2 id="delete-heading" class="font-serif text-xl">Delete your account and data</h2>
            @if ($user->deletion_requested_at)
                <p class="mt-3 text-ink-soft">You requested deletion on {{ $user->deletion_requested_at->format('j F Y') }}. We will confirm by email once it has been processed.</p>
            @else
                <p class="mt-3 max-w-2xl text-ink-soft">We will permanently delete your account, submissions and uploaded files. Some limited records may be kept where the law requires it, as described in the Privacy Policy.</p>
                <form method="POST" action="{{ route('student.profile.deletion') }}" class="mt-5 space-y-4" data-confirm="Request permanent deletion of your account and files?">
                    @csrf
                    <label class="flex items-start gap-3 text-sm text-ink-soft">
                        <input type="checkbox" name="confirm_deletion" value="1" class="checkbox" required>
                        <span>I understand this cannot be undone once processed.</span>
                    </label>
                    <x-field-error name="confirm_deletion" />
                    <button type="submit" class="btn btn-danger btn-sm"><x-icon name="trash-2" class="size-4" />Request deletion</button>
                </form>
            @endif
        </section>
    </div>
</x-layouts.student>
