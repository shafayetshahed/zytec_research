<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Currency;
use App\Enums\PaymentType;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Quotation;
use App\Services\ExchangeRateService;
use App\Support\SiteContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Income reporting. Income is counted on the date each payment was received
 * (cash basis). BDT totals use the rate stored with each payment, so past
 * figures never change when exchange rates move.
 */
class AccountingController extends Controller
{
    public function index(Request $request, ExchangeRateService $rates): View
    {
        [$filters, $from, $to] = $this->filters($request);
        $payments = $this->payments($filters, $from, $to)->get();
        $currencies = Currency::cases();

        $outstanding = Quotation::query()
            ->whereNotNull('sent_at')
            ->with(['submission', 'payments'])
            ->when($filters['country'] ?? null, fn ($query, $country) => $query->where('country', $country))
            ->when($filters['currency'] ?? null, fn ($query, $currency) => $query->where('currency', $currency))
            ->get()
            ->filter(fn (Quotation $quotation) => $quotation->balance() > 0)
            ->sortBy(fn (Quotation $quotation) => $quotation->work_due_date->timestamp ?? PHP_INT_MAX)
            ->values();

        $outstandingByCurrency = $outstanding->groupBy(fn (Quotation $q) => $q->currency->value)
            ->map(fn (Collection $group, string $code) => [
                'amount' => round($group->sum(fn (Quotation $q) => $q->balance()), 2),
                'rate' => $rates->rateToBdt(Currency::from($code), now()),
            ]);

        $quoted = Quotation::query()
            ->whereNotNull('sent_at')
            ->whereBetween('sent_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->when($filters['country'] ?? null, fn ($query, $country) => $query->where('country', $country))
            ->when($filters['currency'] ?? null, fn ($query, $currency) => $query->where('currency', $currency))
            ->get()
            ->groupBy(fn (Quotation $q) => $q->currency->value)
            ->map(fn (Collection $group) => ['count' => $group->count(), 'amount' => round($group->sum(fn (Quotation $q) => (float) $q->total_amount), 2)]);

        return view('admin.accounting.index', [
            'filters' => $filters,
            'from' => $from,
            'to' => $to,
            'currencies' => $currencies,
            'countries' => SiteContent::countriesOfStudy(),
            'summary' => [
                'received_bdt' => round($payments->where('type', PaymentType::Payment)->sum(fn (Payment $p) => (float) $p->amount_bdt), 2),
                'refunded_bdt' => round($payments->where('type', PaymentType::Refund)->sum(fn (Payment $p) => (float) $p->amount_bdt), 2),
                'net_bdt' => round($payments->sum(fn (Payment $p) => $p->signedBdt()), 2),
                'count' => $payments->count(),
                'outstanding_bdt' => $outstandingByCurrency->every(fn (array $row) => $row['rate'] !== null)
                    ? round($outstandingByCurrency->sum(fn (array $row) => $row['amount'] * $row['rate']), 2)
                    : null,
            ],
            'byMonth' => $this->breakdown($payments, fn (Payment $p) => $p->paid_on->format('Y-m'))->sortKeysDesc(),
            'byYear' => $this->breakdown($payments, fn (Payment $p) => $p->paid_on->format('Y'))->sortKeysDesc(),
            'byCountry' => $this->breakdown($payments, fn (Payment $p) => $p->country ?? 'Not specified')->sortByDesc('bdt'),
            'byCurrency' => $this->breakdown($payments, fn (Payment $p) => $p->currency->value)->sortByDesc('bdt'),
            'quoted' => $quoted,
            'outstanding' => $outstanding,
            'outstandingByCurrency' => $outstandingByCurrency,
            'ledger' => $payments->sortByDesc(fn (Payment $p) => $p->paid_on->format('Ymd').str_pad((string) $p->id, 10, '0', STR_PAD_LEFT))->values(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        [$filters, $from, $to] = $this->filters($request);
        $query = $this->payments($filters, $from, $to)->orderBy('paid_on')->orderBy('id');

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Submission', 'Country', 'Type', 'Currency', 'Amount', 'Rate to BDT', 'Amount BDT', 'Rate source', 'Method', 'Reference', 'Recorded by']);

            foreach ($query->lazy() as $payment) {
                fputcsv($out, array_map([$this, 'csvSafe'], [
                    $payment->paid_on->toDateString(),
                    $payment->submission_reference,
                    $payment->country ?? '',
                    $payment->type->label(),
                    $payment->currency->value,
                    $payment->amount,
                    $payment->rate_to_bdt,
                    ($payment->type === PaymentType::Refund ? '-' : '').$payment->amount_bdt,
                    $payment->rate_source,
                    $payment->method->label(),
                    $payment->reference ?? '',
                    $payment->recorder->name ?? '',
                ]));
            }

            fclose($out);
        }, 'zytecs-payments-'.$from->toDateString().'-to-'.$to->toDateString().'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Prevents spreadsheet formula injection from free-text fields.
     */
    private function csvSafe(string $value): string
    {
        return preg_match('/^[=+\-@\t\r]/', $value) === 1 && ! is_numeric($value) ? "'".$value : $value;
    }

    /**
     * @return array{0: array<string, mixed>, 1: Carbon, 2: Carbon}
     */
    private function filters(Request $request): array
    {
        $filters = $request->validate([
            'period' => ['nullable', 'in:this_month,last_month,this_year,last_year,all,custom'],
            'from' => ['nullable', 'date', 'required_if:period,custom'],
            'to' => ['nullable', 'date', 'after_or_equal:from', 'required_if:period,custom'],
            'country' => ['nullable', 'string', Rule::in([...SiteContent::countriesOfStudy(), 'Not specified'])],
            'currency' => ['nullable', Rule::enum(Currency::class)],
        ]);

        $filters['period'] ??= 'this_year';

        [$from, $to] = match ($filters['period']) {
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            'last_year' => [now()->subYear()->startOfYear(), now()->subYear()->endOfYear()],
            'all' => [Carbon::create(2000), now()->endOfYear()],
            'custom' => [Carbon::parse($filters['from']), Carbon::parse($filters['to'])],
            default => [now()->startOfYear(), now()->endOfYear()],
        };

        return [$filters, $from->startOfDay(), $to->startOfDay()];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<Payment>
     */
    private function payments(array $filters, Carbon $from, Carbon $to): Builder
    {
        $country = $filters['country'] ?? null;

        return Payment::query()
            ->with('recorder')
            ->whereBetween('paid_on', [$from->toDateString(), $to->toDateString()])
            ->when($country === 'Not specified', fn ($query) => $query->whereNull('country'))
            ->when($country !== null && $country !== 'Not specified', fn ($query) => $query->where('country', $country))
            ->when($filters['currency'] ?? null, fn ($query, $currency) => $query->where('currency', $currency));
    }

    /**
     * Net totals per group: BDT plus the native amount in each currency.
     * Keys are array keys, so numeric labels such as years become integers.
     *
     * @param  Collection<int, Payment>  $payments
     * @param  callable(Payment): string  $key
     * @return Collection<array-key, array{bdt: float, count: int, native: array<array-key, float>}>
     */
    private function breakdown(Collection $payments, callable $key): Collection
    {
        return $payments->groupBy($key)->map(fn (Collection $group) => [
            'bdt' => round($group->sum(fn (Payment $p) => $p->signedBdt()), 2),
            'count' => $group->count(),
            'native' => $group->groupBy(fn (Payment $p) => $p->currency->value)
                ->map(fn (Collection $byCurrency) => round($byCurrency->sum(fn (Payment $p) => $p->signedAmount()), 2))
                ->all(),
        ]);
    }
}
