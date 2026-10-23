<?php

namespace Tests\Feature\Site;

use App\Support\SiteContent;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function pages(): array
    {
        return [
            'home' => ['/', 'Understand the brief.'],
            'about' => ['/about', 'Academic support built on careful reading'],
            'services' => ['/services', 'Guidance, feedback and research support'],
            'subjects' => ['/subjects', 'Subjects and modules'],
            'portfolio' => ['/portfolio', 'An anonymised record of our work'],
            'how it works' => ['/how-it-works', 'A clear process'],
            'why choose us' => ['/why-choose-us', 'What you can expect from us'],
            'faq' => ['/faq', 'Frequently asked questions'],
            'contact' => ['/contact', 'Talk to us'],
            'integrity' => ['/academic-integrity', 'Academic integrity'],
            'privacy' => ['/privacy-policy', 'Privacy policy'],
            'terms' => ['/terms', 'Terms of service'],
        ];
    }

    #[DataProvider('pages')]
    public function test_page_renders_with_unique_seo_metadata(string $path, string $heading): void
    {
        $response = $this->get($path)->assertOk()->assertSee($heading, false);
        $html = $response->getContent();

        $this->assertSame(1, preg_match_all('/<h1\b/', $html), 'Exactly one h1');
        $this->assertMatchesRegularExpression('/<title>[^<]{5,}<\/title>/', $html);
        $this->assertMatchesRegularExpression('/<meta name="description" content="[^"]{50,}">/', $html);
        $this->assertStringContainsString('<link rel="canonical"', $html);
        $this->assertStringContainsString('property="og:title"', $html);
        $this->assertStringContainsString('"@context":"https://schema.org"', $html);
        $this->assertStringNotContainsString('<?php', $html);
        $this->assertStringContainsString(config('zytecs.business.whatsapp_url'), $html);
        $this->assertStringContainsString('mailto:research@zytecs.com', $html);
        $this->assertStringContainsString('Emergency contact only', $html);
    }

    public function test_titles_and_descriptions_are_unique_across_pages(): void
    {
        $titles = [];
        $descriptions = [];

        foreach (array_merge(array_column(self::pages(), 0), SiteContent::services()->keys()->map(fn ($s) => "/services/{$s}")->all()) as $path) {
            $html = $this->get($path)->getContent();
            preg_match('/<title>([^<]+)<\/title>/', $html, $t);
            preg_match('/<meta name="description" content="([^"]+)">/', $html, $d);
            $titles[] = $t[1];
            $descriptions[] = $d[1];
        }

        $this->assertSame(count($titles), count(array_unique($titles)));
        $this->assertSame(count($descriptions), count(array_unique($descriptions)));
    }

    public function test_every_service_page_renders_and_unknown_slug_is_404(): void
    {
        foreach (SiteContent::services() as $slug => $service) {
            $this->get("/services/{$slug}")->assertOk()->assertSee($service['title'], false)->assertSee('"@type":"Service"', false);
        }

        $this->get('/services/ghost-writing')->assertNotFound()->assertSee('We could not find that page.');
    }

    public function test_structured_data_is_valid_json(): void
    {
        $html = $this->get('/services/data-analysis')->getContent();
        preg_match('/<script type="application\/ld\+json">(.+?)<\/script>/s', $html, $match);

        $data = json_decode($match[1], true, flags: JSON_THROW_ON_ERROR);
        $types = array_column($data['@graph'], '@type');
        $this->assertContains('Organization', $types);
        $this->assertContains('WebSite', $types);
        $this->assertContains('BreadcrumbList', $types);
        $this->assertContains('Service', $types);
        $this->assertNotContains('Review', $types);
        $this->assertNotContains('AggregateRating', $types);
    }

    public function test_sitemap_lists_public_pages_only(): void
    {
        $response = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $xml = simplexml_load_string($response->getContent());

        $this->assertNotFalse($xml);
        $locs = array_map('strval', iterator_to_array($xml->xpath('//*[local-name()="loc"]'), false));
        $this->assertContains(route('home'), $locs);
        $this->assertContains(route('services.show', 'research-proposals'), $locs);
        $this->assertCount(12 + SiteContent::services()->count(), $locs);
        foreach ($locs as $loc) {
            $this->assertStringNotContainsString('/admin', $loc);
            $this->assertStringNotContainsString('/student', $loc);
        }
    }

    public function test_robots_txt_disallows_private_areas_and_links_sitemap(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));

        $this->assertStringContainsString('Disallow: /admin', $robots);
        $this->assertStringContainsString('Disallow: /student', $robots);
        $this->assertStringContainsString('Sitemap: https://research.zytecs.com/sitemap.xml', $robots);
    }

    public function test_legacy_paths_redirect_permanently(): void
    {
        $this->get('/faqs')->assertStatus(301)->assertRedirect('/faq');
        $this->get('/privacy')->assertStatus(301)->assertRedirect('/privacy-policy');
    }

    public function test_portfolio_filters(): void
    {
        $this->get('/portfolio?area=engineering')->assertOk()->assertSee('EGR9019')->assertDontSee('MBIS406');
        $this->get('/portfolio?country=United+Kingdom')->assertOk()->assertSee('CPU3003-20')->assertDontSee('EGR9019');
        $this->get('/portfolio?area=not-an-area')->assertRedirect();
    }

    public function test_portfolio_never_exposes_private_record_fields(): void
    {
        $html = $this->get('/portfolio')->getContent().$this->get('/subjects')->getContent().$this->get('/')->getContent();

        // Writer names, fee columns and spreadsheet labels from the private source files must never appear.
        foreach (['Ornob', 'Deehan', 'Prem', 'Jahir', 'Turna', 'Total Payment', 'Paid', 'Dew', 'Writer', 'Copy 1', 'GBP', 'BDT', 'Work and Bill', '.xlsx'] as $private) {
            $this->assertStringNotContainsString($private, $html, "Found private term: {$private}");
        }
    }

    public function test_portfolio_does_not_claim_unrecorded_countries(): void
    {
        $countries = SiteContent::portfolioEntries()->pluck('countries')->flatten()->unique()->values()->all();

        $this->assertEqualsCanonicalizing(['Australia', 'United Kingdom'], $countries);
    }

    public function test_spreadsheets_are_not_in_public_directory(): void
    {
        $found = array_filter(
            iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(public_path(), \FilesystemIterator::SKIP_DOTS))),
            fn (\SplFileInfo $f) => in_array(strtolower($f->getExtension()), ['xlsx', 'xls', 'csv', 'env', 'sql'], true),
        );

        $this->assertSame([], array_values($found));
    }

    public function test_no_fabricated_claims_on_home_page(): void
    {
        $html = strtolower($this->get('/')->getContent());

        foreach (['guaranteed grade', 'guarantee a', '100% pass', 'testimonial', 'partner university', 'trusted by', 'satisfied clients', '★'] as $claim) {
            $this->assertStringNotContainsString($claim, $html, "Unsupported claim: {$claim}");
        }
    }

    public function test_whatsapp_is_primary_and_phone_labelled_emergency(): void
    {
        $html = $this->get('/contact')->getContent();

        $this->assertLessThan(strpos($html, 'mailto:research@zytecs.com"'), strpos($html, 'https://wa.me/message/V3FR2DQG5IJ7J1'));
        $this->assertStringContainsString('tel:+8801871333382', $html);
        $this->assertMatchesRegularExpression('/Emergency contact only/', $html);
    }

    public function test_all_internal_links_on_public_pages_resolve(): void
    {
        $checked = [];

        foreach (array_column(self::pages(), 0) as $path) {
            preg_match_all('/href="('.preg_quote(url('/'), '/').'[^"#]*)"/', $this->get($path)->getContent(), $matches);

            foreach (array_unique($matches[1]) as $url) {
                $relative = (parse_url($url, PHP_URL_PATH) ?? '/').(parse_url($url, PHP_URL_QUERY) ? '?'.parse_url($url, PHP_URL_QUERY) : '');
                if (isset($checked[$relative]) || str_starts_with($relative, '/build/') || str_starts_with($relative, '/images/') || str_ends_with($relative, '.ico') || str_ends_with($relative, '.png')) {
                    continue;
                }
                $checked[$relative] = true;
                $status = $this->get($relative)->getStatusCode();
                $this->assertContains($status, [200, 302], "Broken link {$relative} on {$path} ({$status})");
            }
        }

        $this->assertGreaterThan(30, count($checked));
    }

    public function test_referenced_images_exist(): void
    {
        foreach (array_column(self::pages(), 0) as $path) {
            preg_match_all('/(?:src|srcset)="([^"]+)"/', $this->get($path)->getContent(), $matches);

            foreach ($matches[1] as $attr) {
                foreach (preg_split('/,\s*/', $attr) as $candidate) {
                    $url = strtok(trim($candidate), ' ');
                    if (str_contains($url, '/images/')) {
                        $this->assertFileExists(public_path(parse_url($url, PHP_URL_PATH)), "Missing image {$url} on {$path}");
                    }
                }
            }
        }
    }
}
