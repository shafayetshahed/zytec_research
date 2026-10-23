# Test results

Run date: **23 October 2026**, macOS (Apple Silicon), PHP 8.5.12, Laravel 13.38.0, Node 26.12.1, Playwright 1.64.1 (Chromium), MariaDB 13.0.2 (temporary local instance).

## Summary

| Check | Result |
| --- | --- |
| PHPUnit feature + unit tests (SQLite, in-memory) | **281 passed**, 0 failed, 0 skipped — 1,618 assertions (re-run after adding the Team portal, materials and messages) |
| Same suite against **MariaDB 13.0.2** | **206 passed**, 0 failed — 1,112 assertions (run before the billing feature; re-run on MariaDB before deploying). Migrations, rollback and re-migration also succeeded. Verified that the suite really used MariaDB by pointing it at a dead port (it then failed). |
| Line coverage (`app/`, Xdebug) | **94.7 %** overall |
| Playwright browser tests | **48 passed**, 0 failed (journeys now include sending a quotation, recording a deposit and checking accounting) |
| axe-core (WCAG 2.0/2.1/2.2 A + AA) | **0 serious/critical violations** on 18 public pages, OTP, student dashboard, submission form (clean and error state), profile and 6 admin pages including Accounting |
| Larastan (PHPStan level 6) | **0 errors** |
| Laravel Pint | **Pass** |
| `composer audit` | No advisories |
| `npm audit` | 0 vulnerabilities |
| Real SMTP delivery | **Not tested** — no credentials available (see below) |

## Coverage of critical code

| Class | Coverage | Untested lines |
| --- | --- | --- |
| `Http/Controllers/Student/SubmissionController` | 100 % | — |
| `Http/Controllers/Auth/LoginController`, `RegisterController` | 100 % | — |
| `Http/Middleware/*` | 100 % | — |
| `Http/Requests/*` | 100 % | — |
| `Services/EmailOtpService` | 97.6 % | one defensive branch (attempts already at max before increment) |
| `Http/Controllers/Admin/SubmissionController` | 97.4 % | "status unchanged" and "already sent" early returns |
| `Http/Controllers/Auth/EmailOtpController` | 84.4 % | already-verified shortcut on verify/resend; mail failure on resend |
| `Actions/SubmitBrief` | 82.4 % | concurrent-duplicate (unique-constraint race) path; orphan-delete warning; queue-unavailable fallback |
| `Rules/SafeDocument` | 79.3 % | defensive branches: invalid upload object, unreadable temp file, ZIP open failure |

Line coverage is not functional completeness; the traceability matrix in `TEST-PLAN.md` maps requirements to tests.

## Defects found and fixed during testing

| # | Found by | Defect | Fix | Regression test |
| --- | --- | --- | --- | --- |
| 1 | Browser smoke test | The JSON-LD `@context` key was compiled by Blade as the `@context` directive, emitting PHP source into structured data | Key built as `'@'.'context'` | `PublicPagesTest::test_structured_data_is_valid_json`, `…unique_seo_metadata` |
| 2 | Visual review | Icon component stripped `width`/`height` from inner SVG shapes (mail icon lost its rectangle) | Only strip on root `<svg>` | Visual; covered by screenshots |
| 3 | Visual review | Desktop navigation wrapped at 1366 px | Wider header container, `nowrap` | `responsive.spec.js` |
| 4 | Unit test | Sanitised filenames could end with `-` | Trim stem after removing extension | `SafeFilenameTest` |
| 5 | Feature test | `noindex` header missing on redirects from private routes (auth middleware runs first) | `PrivatePage` placed ahead of authentication in middleware priority | `AccessControlTest::test_private_pages_are_not_indexable` |
| 6 | axe-core | Muted text below 4.5:1 contrast on the footer background | Darker `--color-muted` | `accessibility.spec.js` |
| 7 | axe-core | Inline links distinguished by colour only | Links underlined by default | `accessibility.spec.js` |
| 8 | Lighthouse | 11 font files preloaded, delaying first paint | Unused weights removed; only two faces preloaded | Lighthouse re-run (below) |
| 9–15 | Independent security review | Host-header reset links; remember-me not revoked on password change; Markdown link injection in emails; no account-wide login limit; bidi characters in filenames; string `in:` rule; macro-enabled DOCX accepted | See `SECURITY.md` | `SecurityRegressionTest` (8 tests) |

| 16 | axe-core | Empty-state text placed directly inside a `<dl>` on the accounting page | Empty state moved outside the list | `accessibility.spec.js` |
| 18 | Compiled-view lint | `@endif@if` written without a space on the student order page, so Blade printed the second `@if` as text and the page failed to render | Space added; all 138 compiled views now lint-checked | `ManualOrdersTest`, `BillingTest` page renders |
| 17 | Responsive check | Admin submission page overflowed by 313 px on phones: screen-reader-only text in the wide payments table was absolutely positioned outside the table's scroll box | Tables made a positioning context; admin grids use explicit single columns on mobile | `journeys.spec.js` (`snapAllSizes` now names overflowing elements) |

Test-side corrections (not application defects) were also made: timestamp precision in one assertion, ambiguous Playwright locators, a duplicate fixture email, Laravel 13's renamed CSRF middleware class, the test host in a link-checking regex, and a static trusted-hosts setting that leaked between tests. One placeholder test that asserted nothing was removed.

## Lighthouse (local PHP development server, no gzip)

| Page | Device | Performance | Accessibility | Best practices | SEO | LCP | CLS |
| --- | --- | --- | --- | --- | --- | --- | --- |
| `/` | Mobile | 86 | 100 | 100 | 100 | 3.5 s | 0.003 |
| `/` | Desktop | 99 | 100 | 100 | 100 | 1.0 s | 0 |
| `/portfolio` | Mobile | 89 | 100 | 100 | 100 | 3.2 s | 0 |
| `/portfolio` | Desktop | 99 | 100 | 100 | 100 | 0.9 s | 0 |
| `/services/data-analysis` | Mobile | 94 | 100 | 100 | 100 | 2.6 s | 0 |
| `/services/data-analysis` | Desktop | 100 | 100 | 100 | 100 | 0.8 s | 0 |
| `/register` | Mobile | 94 | 100 | 100 | 58* | 2.7 s | 0 |

\*The register page is deliberately `noindex`. Desktop figures were measured before the font change; mobile figures after. The PHP built-in server does not compress responses; Lighthouse estimates 70–165 KiB savings from gzip/brotli, which a production web server provides. Re-measure on the live site.

## Screenshots

42 full-page screenshots (desktop 1440 px, tablet 820 px, mobile 390 px) of the home, services, service detail, portfolio, subjects, contact, register, login, OTP, student dashboard, submission form, submission confirmation, admin dashboard and admin submission pages are written to `storage/framework/testing/screenshots/` by `npx playwright test`. No horizontal overflow or broken images were detected at any size.

## Blocked / not run

- **Real email delivery**: not verified. Needs SMTP credentials; follow `EMAIL-CONFIGURATION.md` §4 and record the result here.
- **Malware scanning**: not implemented, so not tested.
- **Production deployment, HTTPS, cron and backups**: not performed from the development environment.
- **Manual screen-reader testing**: not performed; automated axe checks and keyboard tests only.

## How to reproduce

```bash
php artisan test
```

```bash
XDEBUG_MODE=coverage php artisan test --coverage
```

```bash
npx playwright test
```

```bash
vendor/bin/phpstan analyse && vendor/bin/pint --test && composer audit && npm audit
```
