<?php

namespace App\Http\Controllers;

use App\Support\SiteContent;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * @phpstan-import-type PortfolioEntry from SiteContent
 */
class PageController extends Controller
{
    public function home(): View
    {
        $entries = SiteContent::portfolioEntries();

        return view('pages.home', [
            'services' => SiteContent::services(),
            'areas' => $this->areaSummaries($entries),
            'highlights' => $entries->filter(fn (array $entry) => $entry['code'] !== null && $entry['title_status'] === 'recorded')
                ->whereIn('code', ['MGMT402', 'MBIS406', 'EGR9045', 'MAR036-6', 'SHR080-6', 'CPU3003-20'])
                ->values(),
            'faqs' => collect(SiteContent::faqs())->flatten(1)->take(5),
        ]);
    }

    public function about(): View
    {
        return view('pages.about', ['areas' => SiteContent::portfolioAreas()]);
    }

    public function services(): View
    {
        $services = SiteContent::services();

        return view('pages.services.index', [
            'researchServices' => $services->where('group', 'research'),
            'disciplineServices' => $services->where('group', 'discipline'),
        ]);
    }

    public function service(string $slug): View
    {
        $service = SiteContent::service($slug) ?? abort(404);
        $area = array_search($slug, SiteContent::areaServices(), true);

        return view('pages.services.show', [
            'slug' => $slug,
            'service' => $service,
            'related' => SiteContent::services()->except($slug)->where('group', $service['group'])->take(3),
            'recordedModules' => $area !== false && $slug !== 'assignment-planning' && $slug !== 'research-methodology'
                ? SiteContent::portfolioEntries()->where('area', $area)->values()
                : collect(),
            'areaName' => $area !== false ? SiteContent::portfolioAreas()[$area] : null,
            'areaKey' => $area !== false ? $area : null,
        ]);
    }

    public function subjects(): View
    {
        $entries = SiteContent::portfolioEntries();

        return view('pages.subjects', [
            'areas' => SiteContent::portfolioAreas(),
            'grouped' => $entries->groupBy('area'),
            'areaServices' => SiteContent::areaServices(),
            'services' => SiteContent::services(),
        ]);
    }

    public function portfolio(Request $request): View
    {
        $areas = SiteContent::portfolioAreas();
        $entries = SiteContent::portfolioEntries();
        $countries = $entries->pluck('countries')->flatten()->unique()->sort()->values();
        $tasks = $entries->pluck('tasks')->flatten()->unique()->sort()->values();

        $filters = $request->validate([
            'area' => ['nullable', 'string', 'in:'.implode(',', array_keys($areas))],
            'country' => ['nullable', 'string', 'in:'.$countries->implode(',')],
            'task' => ['nullable', 'string', 'max:60'],
        ]);

        $area = $filters['area'] ?? null;
        $country = $filters['country'] ?? null;
        $task = $filters['task'] ?? null;

        $filtered = $entries->filter(fn (array $entry) => ($area === null || $entry['area'] === $area)
            && ($country === null || in_array($country, $entry['countries'], true))
            && ($task === null || in_array($task, $entry['tasks'], true)))->values();

        return view('pages.portfolio', [
            'areas' => $areas,
            'countries' => $countries,
            'tasks' => $tasks,
            'filters' => $filters,
            'entries' => $filtered,
            'total' => $entries->count(),
            'yearRange' => $entries->pluck('years')->flatten()->min().'–'.$entries->pluck('years')->flatten()->max(),
        ]);
    }

    public function howItWorks(): View
    {
        return view('pages.how-it-works');
    }

    public function whyChooseUs(): View
    {
        return view('pages.why-choose-us', ['areas' => SiteContent::portfolioAreas()]);
    }

    public function faq(): View
    {
        return view('pages.faq', ['groups' => SiteContent::faqs()]);
    }

    public function academicIntegrity(): View
    {
        return view('pages.academic-integrity');
    }

    public function privacy(): View
    {
        return view('pages.privacy');
    }

    public function terms(): View
    {
        return view('pages.terms');
    }

    /**
     * @param  Collection<int, PortfolioEntry>  $entries
     * @return Collection<string, array{name: string, count: int, sample: string}>
     */
    private function areaSummaries(Collection $entries): Collection
    {
        return collect(SiteContent::portfolioAreas())->map(fn (string $name, string $key) => [
            'name' => $name,
            'count' => $entries->where('area', $key)->count(),
            'sample' => $entries->where('area', $key)->whereNotNull('title')->pluck('title')->unique()->take(3)->implode(' · '),
        ]);
    }
}
