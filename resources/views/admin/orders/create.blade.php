<x-layouts.admin title="New manual order">
    <nav aria-label="Breadcrumb" class="text-sm"><a href="{{ route('admin.accounting') }}">Accounting</a> <span class="text-muted">/</span> <span aria-current="page">New manual order</span></nav>
    <h1 class="mt-3 font-serif text-3xl">New manual order</h1>
    <p class="mt-1 max-w-2xl text-muted">Record an order taken on WhatsApp, by phone, email or in person. It gets a reference and quotation number, and you record payments on the next page.</p>

    <div class="mt-6 max-w-3xl"><x-error-summary /></div>

    @if ($clients->isEmpty())
        <div class="alert-info alert mt-6 max-w-3xl"><x-icon name="info" class="mt-0.5 size-5" /><p>There are no clients yet. <a href="{{ route('admin.clients.create') }}">Add a client</a> first.</p></div>
    @else
        <form method="POST" action="{{ route('admin.orders.store') }}" class="card mt-6 grid max-w-3xl gap-5 p-6 sm:grid-cols-2" novalidate data-submit-once>
            @csrf
            <div class="field sm:col-span-2">
                <label for="client_id" class="label">Client</label>
                <p class="hint">Not listed? <a href="{{ route('admin.clients.create') }}">Add a client</a>.</p>
                <select id="client_id" name="client_id" class="input" required @error('client_id') aria-invalid="true" @enderror>
                    <option value="">Choose…</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" @selected((int) old('client_id', $selectedClient) === $client->id)>{{ $client->name }} — {{ $client->contactSummary() }}</option>
                    @endforeach
                </select>
                <x-field-error name="client_id" />
            </div>
            <x-select-field name="source" label="Order came from" :options="collect($sources)->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all()" value="whatsapp" />
            <x-text-field name="order_date" label="Order date" type="date" optional :value="now()->toDateString()" :max="now()->toDateString()" />
            <x-text-field name="assignment_name" label="Work name" maxlength="200" class="sm:col-span-2" placeholder="e.g. Marketing plan report" />
            <x-text-field name="module_code" label="Module code" optional maxlength="40" />
            <x-select-field name="country" label="Country" :options="$countries" optional hint="Leave blank to use the client's country." />
            <x-select-field name="currency" label="Currency" :options="collect($currencies)->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all()" />
            <x-text-field name="total_amount" label="Price" type="number" step="0.01" min="0.01" inputmode="decimal" />
            <x-text-field name="deposit_amount" label="Deposit / advance needed" type="number" step="0.01" min="0" inputmode="decimal" value="0" />
            <x-text-field name="work_due_date" label="Delivery date" type="date" optional />
            <div class="field sm:col-span-2">
                <label for="notes" class="label">Notes<span class="label-optional">(optional, internal)</span></label>
                <textarea id="notes" name="notes" rows="3" maxlength="3000" class="input">{{ old('notes') }}</textarea>
            </div>
            <label class="flex items-start gap-3 text-sm text-ink-soft sm:col-span-2">
                <input type="hidden" name="email_client" value="0">
                <input type="checkbox" name="email_client" value="1" class="checkbox" @checked(old('email_client'))>
                <span>Email the quotation PDF to the client (only if they have an email address)</span>
            </label>
            <div class="sm:col-span-2"><button type="submit" class="btn btn-primary"><span data-busy-label="Saving…">Add order</span></button></div>
        </form>
    @endif
</x-layouts.admin>
