<?php

namespace App\Services;

use App\Enums\Currency;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Looks up the BDT value of one unit of a currency on a given date, using a
 * free rate service. Returns null when no rate is available so the caller
 * can ask the administrator to enter one.
 */
class ExchangeRateService
{
    public function rateToBdt(Currency $currency, CarbonInterface $date): ?float
    {
        if ($currency === Currency::BDT) {
            return 1.0;
        }

        $dateKey = $date->isToday() || $date->isFuture() ? 'latest' : $date->toDateString();

        return Cache::remember("fx:{$currency->value}:{$dateKey}", now()->addHours(12), function () use ($currency, $dateKey) {
            $base = strtolower($currency->value);

            foreach ((array) config('zytecs.accounting.rate_urls') as $template) {
                $url = str_replace(['{date}', '{base}'], [$dateKey, $base], (string) $template);

                try {
                    $response = Http::timeout(5)->acceptJson()->get($url);
                    $rate = $response->successful() ? $response->json("{$base}.bdt") : null;

                    if (is_numeric($rate) && $rate > 0) {
                        return round((float) $rate, 6);
                    }
                } catch (Throwable $exception) {
                    Log::info('Exchange rate lookup failed.', ['currency' => $currency->value, 'date' => $dateKey, 'exception' => class_basename($exception)]);
                }
            }

            return null;
        });
    }
}
