<table class="items">
    <thead><tr><th>Description</th><th class="num" style="width: 28%;">Amount ({{ $quotation->currency->value }})</th></tr></thead>
    <tbody>
        <tr>
            <td>
                <strong>Academic support: {{ $submission->assignment_name }}</strong><br>
                <span class="muted">Module {{ $submission->module_code }}@if ($quotation->work_due_date) · Delivery by {{ $quotation->work_due_date->format('j F Y') }}@endif</span>
            </td>
            <td class="num">{{ number_format((float) $quotation->total_amount, 2) }}</td>
        </tr>
    </tbody>
</table>
