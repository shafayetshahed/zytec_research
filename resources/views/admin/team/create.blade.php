<x-layouts.admin title="Add team member">
    <nav aria-label="Breadcrumb" class="text-sm"><a href="{{ route('admin.team.index') }}">Team</a> <span class="text-muted">/</span> <span aria-current="page">Add team member</span></nav>
    <h1 class="mt-3 font-serif text-3xl">Add team member</h1>
    <p class="mt-1 max-w-2xl text-muted">They will receive an email with a link to set their password and sign in to the Team portal.</p>
    <div class="mt-6 max-w-2xl"><x-error-summary /></div>
    <form method="POST" action="{{ route('admin.team.store') }}" class="card mt-6 grid max-w-2xl gap-5 p-6" novalidate data-submit-once>
        @csrf
        <x-text-field name="name" label="Full name" autocomplete="off" />
        <x-text-field name="email" label="Email" type="email" autocomplete="off" hint="Used to sign in. They receive the invitation here." />
        <x-text-field name="whatsapp_number" label="WhatsApp number" type="tel" inputmode="tel" optional placeholder="+880 1711 000000" hint="For your reference only; never shown to students." />
        <div class="flex flex-wrap gap-3">
            <button type="submit" class="btn btn-primary"><span data-busy-label="Adding…">Add and send invitation</span></button>
            <a href="{{ route('admin.team.index') }}" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</x-layouts.admin>
