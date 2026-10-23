<table class="parties">
    <tr>
        <td>
            <div class="label">From</div>
            <strong>{{ $business['name'] }}</strong><br>
            @foreach (array_filter(explode('|', (string) $business['invoice_address'])) as $line)
                {{ trim($line) }}<br>
            @endforeach
            {{ $business['email'] }}<br>
            {{ preg_replace('#^https?://#', '', $business['url']) }}
        </td>
        <td>
            <div class="label">Bill to</div>
            <strong>{{ $submission->student->name }}</strong><br>
            {{ $submission->contact_email }}<br>
            @if ($quotation->country)<span class="muted">{{ $quotation->country }}</span><br>@endif
            <span class="muted">Order reference: <strong>{{ $submission->reference }}</strong></span>
        </td>
    </tr>
</table>
