<x-layouts.admin title="Settings">
    <h1 class="font-serif text-3xl">Settings</h1>
    <p class="mt-1 max-w-2xl text-muted">Read-only. These values come from the server's environment file; change them there and redeploy. Secrets such as passwords are never shown.</p>

    @foreach ($warnings as $warning)
        <div class="alert-warning alert mt-4"><x-icon name="circle-alert" class="mt-0.5 size-5" /><p>{{ $warning }}</p></div>
    @endforeach

    <div class="card mt-6 max-w-3xl overflow-x-auto">
        <table class="data-table">
            <tbody>
                @foreach ($settings as $label => $value)
                    <tr><th scope="row" class="w-1/2 !text-sm !normal-case !tracking-normal !text-ink">{{ $label }}</th><td class="font-mono text-sm break-all">{{ $value }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layouts.admin>
