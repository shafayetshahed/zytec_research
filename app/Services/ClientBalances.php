<?php

namespace App\Services;

use App\Enums\Currency;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Per-client billing position across all of a client's orders: what was
 * billed, paid and is still due, by currency, plus BDT equivalents.
 *
 * @phpstan-type ClientBalance array{
 *     client: User,
 *     country: ?string,
 *     orders: int,
 *     ongoing: int,
 *     completed: int,
 *     billed: array<string, float>,
 *     paid: array<string, float>,
 *     due: array<string, float>,
 *     paid_bdt: float,
 *     due_bdt: ?float,
 *     last_payment: ?\Illuminate\Support\Carbon,
 *     due_orders: Collection<int, Submission>,
 *     has_dues: bool,
 * }
 */
class ClientBalances
{
    public function __construct(private ExchangeRateService $rates) {}

    /**
     * @return Collection<int, ClientBalance>
     */
    public function all(): Collection
    {
        $clients = User::students()
            ->with(['submissions' => fn ($query) => $query->latest('submitted_at'), 'submissions.quotation.payments'])
            ->orderBy('name')
            ->get();

        return $clients->map(fn (User $client) => $this->forClient($client));
    }

    /**
     * @return ClientBalance
     */
    public function forClient(User $client): array
    {
        $quotations = $client->submissions
            ->map(fn (Submission $s) => $s->quotation)
            ->filter(fn (?Quotation $q) => $q !== null && $q->sent_at !== null)
            ->values();

        $byCurrency = fn (callable $amount): array => $quotations
            ->groupBy(fn (Quotation $q) => $q->currency->value)
            ->map(fn (Collection $group) => round($group->sum($amount), 2))
            ->filter(fn (float $value) => $value != 0.0)
            ->sortKeys()
            ->all();

        $payments = $quotations->flatMap(fn (Quotation $q) => $q->payments);
        $due = $byCurrency(fn (Quotation $q) => $q->balance());

        return [
            'client' => $client,
            // Portal students give a country per order rather than on their profile.
            'country' => $client->country
                ?? $client->submissions->map(fn (Submission $s) => $s->quotation->country ?? $s->country)->filter()->first(),
            'orders' => $client->submissions->count(),
            'ongoing' => $client->submissions->filter(fn (Submission $s) => ! $s->status->isFinal())->count(),
            'completed' => $client->submissions->filter(fn (Submission $s) => $s->status->isFinal())->count(),
            'billed' => $byCurrency(fn (Quotation $q) => (float) $q->total_amount),
            'paid' => $byCurrency(fn (Quotation $q) => $q->amountPaid()),
            'due' => $due,
            'paid_bdt' => round($payments->sum(fn (Payment $p) => $p->signedBdt()), 2),
            'due_bdt' => $this->toBdt($due),
            'last_payment' => $payments->max('paid_on'),
            'due_orders' => $client->submissions->filter(fn (Submission $s) => $s->quotation?->sent_at !== null && $s->quotation->balance() > 0)->values(),
            'has_dues' => $due !== [],
        ];
    }

    /**
     * Converts per-currency amounts to BDT at today's rate; null if any rate is unavailable.
     *
     * @param  array<string, float>  $amounts
     */
    public function toBdt(array $amounts): ?float
    {
        $total = 0.0;

        foreach ($amounts as $code => $amount) {
            $rate = $this->rates->rateToBdt(Currency::from($code), now());

            if ($rate === null) {
                return null;
            }

            $total += $amount * $rate;
        }

        return round($total, 2);
    }

    /**
     * WhatsApp link with an editable, polite reminder listing what is due.
     *
     * @param  ClientBalance  $balance
     */
    public function reminderLink(array $balance): ?string
    {
        $digits = ltrim((string) $balance['client']->whatsapp_number, '+');

        if ($digits === '' || ! $balance['has_dues']) {
            return null;
        }

        $lines = $balance['due_orders']->map(fn (Submission $s) => '• '.$s->reference.' ('.str($s->assignment_name)->limit(40).'): '.$s->quotation?->currency->format($s->quotation->balance()))->implode("\n");

        $message = "Hello {$balance['client']->name}, this is a friendly reminder from Zytecs Research.\n\n"
            ."The following balance is due:\n{$lines}\n\n"
            .'Please write the order reference in your payment note and mention it when you send the payment screenshot. Thank you!';

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($message);
    }
}
