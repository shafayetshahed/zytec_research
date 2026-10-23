<?php

namespace App\Http\Controllers;

use App\Support\SiteContent;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = collect([
            route('home'), route('about'), route('services.index'), route('subjects'),
            route('portfolio'), route('how-it-works'), route('why-choose-us'), route('faq'),
            route('contact'), route('confidentiality'), route('academic-integrity'), route('privacy'), route('terms'),
        ])->merge(SiteContent::services()->keys()->map(fn (string $slug) => route('services.show', $slug)));

        return response()
            ->view('sitemap', ['urls' => $urls, 'lastModified' => now()->startOfMonth()->toAtomString()])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
