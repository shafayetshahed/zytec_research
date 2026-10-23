<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * @phpstan-type PortfolioEntry array{area: string, code: ?string, title: ?string, title_status: string, tasks: list<string>, years: list<int>, countries: list<string>}
 *
 * Read-only access to the public site's structured content in resources/content.
 */
class SiteContent
{
    /** @var array<string, mixed> */
    private static array $cache = [];

    /**
     * @return Collection<string, array<string, mixed>>
     */
    public static function services(): Collection
    {
        return collect(self::load('services'));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function service(string $slug): ?array
    {
        return self::services()->get($slug);
    }

    /**
     * @return array<string, list<array{q: string, a: string}>>
     */
    public static function faqs(): array
    {
        return self::load('faqs');
    }

    /**
     * @return array<string, string>
     */
    public static function portfolioAreas(): array
    {
        return self::portfolio()['areas'];
    }

    /**
     * @return Collection<int, PortfolioEntry>
     */
    public static function portfolioEntries(): Collection
    {
        return collect(self::portfolio()['entries']);
    }

    /**
     * @return array{areas: array<string, string>, entries: list<PortfolioEntry>, case_studies: list<array<string, mixed>>}
     */
    private static function portfolio(): array
    {
        return self::load('portfolio');
    }

    /**
     * Service most closely matching each portfolio subject area.
     *
     * @return array<string, string>
     */
    public static function areaServices(): array
    {
        return [
            'business' => 'business-management',
            'marketing' => 'marketing-consumer-behaviour',
            'hrm' => 'human-resource-management',
            'finance' => 'accounting-finance',
            'it' => 'it-computing',
            'engineering' => 'engineering-technical',
            'hospitality' => 'hospitality-tourism-events',
            'law' => 'law-legal-research',
            'education' => 'assignment-planning',
            'research' => 'research-methodology',
        ];
    }

    /**
     * @return list<string>
     */
    public static function countriesOfStudy(): array
    {
        return ['United Kingdom', 'Australia', 'Canada', 'United States', 'Other'];
    }

    /**
     * @return list<string>
     */
    public static function assessmentTypes(): array
    {
        return ['Essay', 'Report', 'Case study', 'Research proposal', 'Literature review', 'Dissertation or thesis', 'Presentation', 'Reflective journal', 'Group project', 'Data analysis', 'Portfolio', 'Proofreading', 'Other'];
    }

    /**
     * @return array<string, mixed>
     */
    private static function load(string $name): array
    {
        return self::$cache[$name] ??= require resource_path("content/{$name}.php");
    }
}
