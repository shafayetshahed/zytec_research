<?php

namespace Tests\Feature\Site;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_security_headers_are_sent(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $csp = (string) $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("script-src 'self'", $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
        $this->assertStringNotContainsString('unsafe-eval', $csp);
    }

    public function test_hsts_is_sent_over_https(): void
    {
        $this->get('https://localhost/')->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_pages_contain_no_inline_executable_scripts(): void
    {
        $html = $this->get('/')->getContent();

        preg_match_all('/<script\b([^>]*)>/', $html, $matches);
        foreach ($matches[1] as $attributes) {
            $this->assertTrue(str_contains($attributes, 'src=') || str_contains($attributes, 'application/ld+json'), "Inline script found: {$attributes}");
        }
    }

    public function test_session_cookie_is_http_only_and_encrypted(): void
    {
        $this->assertTrue(config('session.http_only'));
        $this->assertTrue(config('session.encrypt'));
        $this->assertSame('lax', config('session.same_site'));
    }
}
