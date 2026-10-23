<x-layouts.admin :title="$client ? 'Edit client' : 'Add client'">
    <nav aria-label="Breadcrumb" class="text-sm"><a href="{{ route('admin.students.index') }}">Students and clients</a> <span class="text-muted">/</span> <span aria-current="page">{{ $client ? $client->name : 'Add client' }}</span></nav>
    <h1 class="mt-3 font-serif text-3xl">{{ $client ? 'Edit client' : 'Add client' }}</h1>
    <p class="mt-1 max-w-2xl text-muted">For people who order on WhatsApp, by phone or by email. Only the name is required. If you add an email address, the client can later use "Forgot password" to sign in to the portal and see their orders.</p>

    <div class="mt-6 max-w-2xl"><x-error-summary /></div>

    <form method="POST" action="{{ $client ? route('admin.clients.update', $client) : route('admin.clients.store') }}" class="card mt-6 grid max-w-2xl gap-5 p-6" novalidate>
        @csrf
        @if ($client) @method('PUT') @endif
        <x-text-field name="name" label="Client name" :value="$client?->name" autocomplete="off" />
        <x-text-field name="whatsapp_number" label="WhatsApp number" type="tel" inputmode="tel" optional :value="$client?->whatsapp_number" placeholder="+61 412 345 678" hint="With country code." />
        @if ($client && $client->email_verified_at)
            <div class="field">
                <span class="label">Email</span>
                <p>{{ $client->email }} <span class="badge bg-success-bg text-success">Verified by the student</span></p>
            </div>
        @else
            <x-text-field name="email" label="Email" type="email" optional :value="$client?->email" autocomplete="off" />
        @endif
        <x-select-field name="country" label="Country of study" :options="$countries" :value="$client?->country" optional />
        <div class="flex flex-wrap gap-3">
            <button type="submit" class="btn btn-primary">{{ $client ? 'Save client' : 'Add client' }}</button>
            <a href="{{ $client ? route('admin.students.show', $client) : route('admin.students.index') }}" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</x-layouts.admin>
