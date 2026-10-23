@props(['title', 'number', 'date', 'stamp' => null, 'business', 'logoPath'])
<!DOCTYPE html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<title>{{ $title }} {{ $number }}</title>
<style>
    @page { margin: 34px 40px 60px; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10.5px; color: #0a1f3c; line-height: 1.45; }
    h1 { font-size: 22px; letter-spacing: 3px; margin: 0; color: #0a1f3c; font-weight: bold; }
    .muted { color: #4e5965; }
    .bronze { color: #8a5d28; }
    table { width: 100%; border-collapse: collapse; }
    .head td { vertical-align: top; }
    .rule { border-top: 2px solid #0a1f3c; margin: 16px 0 14px; }
    .label { font-size: 8.5px; text-transform: uppercase; letter-spacing: 1.5px; color: #8a5d28; font-weight: bold; margin-bottom: 4px; }
    .parties td { vertical-align: top; width: 50%; padding-right: 20px; }
    .items { margin-top: 20px; }
    .items th { background: #0a1f3c; color: #fff; text-align: left; padding: 7px 9px; font-size: 9px; text-transform: uppercase; letter-spacing: 1px; }
    .items td { padding: 9px; border-bottom: 1px solid #ddd5c6; vertical-align: top; }
    .num, .items th.num { text-align: right; white-space: nowrap; }
    .totals { margin-top: 14px; width: 48%; margin-left: 52%; }
    .totals td { padding: 5px 9px; }
    .totals .grand td { border-top: 2px solid #0a1f3c; font-weight: bold; font-size: 12.5px; padding-top: 8px; }
    .highlight { background: #e8f0fb; }
    .stamp { display: inline-block; border: 3px solid #1e6b45; color: #1e6b45; font-weight: bold; font-size: 18px; letter-spacing: 4px; padding: 4px 14px; transform: rotate(-6deg); }
    .note { margin-top: 26px; padding: 10px 12px; background: #f7f4ed; border-left: 3px solid #8a5d28; }
    .footer { position: fixed; bottom: -40px; left: 0; right: 0; font-size: 8.5px; color: #4e5965; border-top: 1px solid #ddd5c6; padding-top: 6px; }
</style>
</head>
<body>
    <table class="head">
        <tr>
            <td style="width: 55%;">
                @if (is_file($logoPath))
                    <img src="{{ $logoPath }}" alt="Zytecs Research" style="width: 170px;">
                @else
                    <strong style="font-size: 18px;">Zytecs Research</strong>
                @endif
            </td>
            <td style="text-align: right;">
                <h1>{{ strtoupper($title) }}</h1>
                <div style="margin-top: 6px;"><strong>No.</strong> {{ $number }}</div>
                <div class="muted">Date: {{ $date->format('j F Y') }}</div>
                @if ($stamp)
                    <div style="margin-top: 10px;"><span class="stamp">{{ $stamp }}</span></div>
                @endif
            </td>
        </tr>
    </table>

    <div class="rule"></div>

    {{ $slot }}

    <div class="footer">
        <table><tr>
            <td>{{ $business['name'] }} · {{ preg_replace('#^https?://#', '', $business['url']) }} · {{ $business['email'] }}</td>
            <td style="text-align: right;">{{ $number }}</td>
        </tr></table>
    </div>
</body>
</html>
