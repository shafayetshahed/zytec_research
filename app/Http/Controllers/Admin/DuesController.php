<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SubmissionStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Submission;
use App\Services\ClientBalances;
use App\Support\SiteContent;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * "Who owes what right now": all-time balances per client, for chasing
 * payments. Accounting covers income by period; this page covers dues.
 *
 * @phpstan-import-type ClientBalance from ClientBalances
 */
class DuesController extends Controller
{
    public function index(Request $request, ClientBalances $balances): View
    {
        $filters = $this->filters($request);
        $all = $balances->all();
        $rows = $this->apply($all, $filters);

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 30;

        $finalStatuses = array_filter(SubmissionStatus::cases(), fn (SubmissionStatus $s) => $s->isFinal());
        $dueByCurrency = $all->flatMap(fn (array $b) => collect($b['due'])->map(fn (float $amount, string $code) => ['code' => $code, 'amount' => $amount])->values())
            ->groupBy('code')
            ->map(fn (Collection $group) => round($group->sum('amount'), 2))
            ->sortKeys()
            ->all();

        return view('admin.dues.index', [
            'filters' => $filters,
            'countries' => SiteContent::countriesOfStudy(),
            'balances' => $balances,
            'rows' => new LengthAwarePaginator($rows->forPage($page, $perPage)->values(), $rows->count(), $perPage, $page, ['path' => $request->url(), 'query' => $request->query()]),
            'stats' => [
                'clients' => $all->count(),
                'clients_with_dues' => $all->where('has_dues', true)->count(),
                'orders' => Submission::count(),
                'ongoing' => Submission::whereNotIn('status', $finalStatuses)->count(),
                'completed' => Submission::whereIn('status', $finalStatuses)->count(),
                'paid_bdt' => round(Payment::all()->sum(fn (Payment $p) => $p->signedBdt()), 2),
                'due_bdt' => $balances->toBdt($dueByCurrency),
                'due_by_currency' => $dueByCurrency,
            ],
        ]);
    }

    public function export(Request $request, ClientBalances $balances): StreamedResponse
    {
        $rows = $this->apply($balances->all(), $this->filters($request));
        $format = fn (array $amounts): string => collect($amounts)->map(fn (float $v, string $c) => $c.' '.number_format($v, 2, '.', ''))->implode('; ');

        return response()->streamDownload(function () use ($rows, $format) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Client', 'Country', 'WhatsApp', 'Email', 'Orders', 'Ongoing', 'Billed', 'Paid', 'Paid BDT', 'Due', 'Due BDT (today)', 'Last payment', 'Orders with dues']);

            foreach ($rows as $row) {
                $client = $row['client'];
                fputcsv($out, array_map(fn ($v) => $this->csvSafe((string) $v), [
                    $client->name, $row['country'] ?? '', $client->whatsapp_number ?? '', $client->email ?? '',
                    $row['orders'], $row['ongoing'], $format($row['billed']), $format($row['paid']), number_format($row['paid_bdt'], 2, '.', ''),
                    $format($row['due']), $row['due_bdt'] === null ? '' : number_format($row['due_bdt'], 2, '.', ''),
                    $row['last_payment']?->toDateString() ?? '',
                    $row['due_orders']->pluck('reference')->implode(' '),
                ]));
            }

            fclose($out);
        }, 'zytecs-client-dues-'.now()->toDateString().'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array{show: string, country: ?string, q: ?string, sort: string}
     */
    private function filters(Request $request): array
    {
        $filters = $request->validate([
            'show' => ['nullable', 'in:all,due,paid,none'],
            'country' => ['nullable', 'string', Rule::in([...SiteContent::countriesOfStudy(), 'Not set'])],
            'q' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'in:due,paid,name,recent'],
        ]);

        return [
            'show' => $filters['show'] ?? 'all',
            'country' => $filters['country'] ?? null,
            'q' => $filters['q'] ?? null,
            'sort' => $filters['sort'] ?? 'due',
        ];
    }

    /**
     * @param  Collection<int, ClientBalance>  $all
     * @param  array{show: string, country: ?string, q: ?string, sort: string}  $filters
     * @return Collection<int, ClientBalance>
     */
    private function apply(Collection $all, array $filters): Collection
    {
        $term = Str::lower(trim((string) $filters['q']));

        $rows = $all
            ->filter(fn (array $b) => match ($filters['show']) {
                'due' => $b['has_dues'],
                'paid' => ! $b['has_dues'] && $b['paid'] !== [],
                'none' => $b['orders'] === 0,
                default => true,
            })
            ->filter(fn (array $b) => $filters['country'] === null
                || ($filters['country'] === 'Not set' ? $b['country'] === null : $b['country'] === $filters['country']))
            ->filter(fn (array $b) => $term === '' || Str::contains(Str::lower(implode(' ', array_filter([
                $b['client']->name, $b['client']->email, $b['client']->whatsapp_number,
                $b['client']->submissions->pluck('reference')->implode(' '),
            ]))), $term));

        return match ($filters['sort']) {
            'paid' => $rows->sortByDesc('paid_bdt'),
            'name' => $rows->sortBy(fn (array $b) => Str::lower($b['client']->name)),
            'recent' => $rows->sortByDesc(fn (array $b) => $b['last_payment']->timestamp ?? 0),
            default => $rows->sortBy([
                fn (array $a, array $b) => $b['has_dues'] <=> $a['has_dues'],
                fn (array $a, array $b) => ($b['due_bdt'] ?? 0) <=> ($a['due_bdt'] ?? 0),
                fn (array $a, array $b) => strcmp($a['client']->name, $b['client']->name),
            ]),
        };
    }

    private function csvSafe(string $value): string
    {
        return preg_match('/^[=+\-@\t\r]/', $value) === 1 && ! is_numeric($value) ? "'".$value : $value;
    }
}
