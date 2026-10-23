<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps account and admin pages out of search indexes and shared caches.
 * This complements, and never replaces, authentication.
 */
class PrivatePage
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        if (! $response->headers->has('Content-Disposition')) {
            $response->headers->set('Cache-Control', 'no-store, private');
        }

        return $response;
    }
}
