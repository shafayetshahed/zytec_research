# Zytecs Research — website and student portal

A Laravel 13 (PHP 8.3+) application for **Zytecs Research**: the public website, a student portal with email-OTP verification and private brief uploads, business email notifications, and an administrator panel.

| Area | What it does |
| --- | --- |
| Public site | Home, About, Services (17 detail pages), Subjects directory, anonymised Portfolio, How it works, Why choose us, FAQs, Contact form, Academic integrity, Privacy policy, Terms, XML sitemap, custom error pages |
| Students | Register (name, email, WhatsApp, password, terms) → six-digit email OTP → dashboard, brief submission with private file upload, own-file download, profile, password change, deletion request |
| Business | New-brief and enquiry emails to `research@zytecs.com`, delivery log with automatic and manual retry |
| Admin (`/admin`) | Live metrics, searchable/filterable submissions, secure downloads, status workflow with history, internal notes, student management (suspend, reactivate, delete), enquiries, notification monitor, audit log, read-only settings |

## Requirements

- PHP **8.3 or newer** (developed and tested on 8.5.12) with extensions: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `intl`, `mbstring`, `openssl`, `pdo_mysql` (production) or `pdo_sqlite` (local/tests), `tokenizer`, `xml`, `zip`
- Composer 2
- Node.js 20+ and npm (only to build CSS/JS; not needed on the server if you upload `public/build`)
- MySQL 8 / MariaDB 10.6+ in production (SQLite is used locally and for automated tests)

## Local development

```bash
composer install
```

```bash
cp .env.example .env && php artisan key:generate
```

```bash
touch database/database.sqlite && php artisan migrate
```

```bash
npm install && npm run build
```

```bash
php artisan serve
```

Open http://127.0.0.1:8000. Locally, emails are written to `storage/logs/mail.log` (`MAIL_MAILER=log`, `MAIL_LOG_CHANNEL=mail`), which is where you will find verification codes while developing. **Never use the `log` mailer in production** — it would write verification codes to disk.

Optional synthetic data (refuses to run in production; prints a one-time demo admin password):

```bash
php artisan db:seed --class=DemoSeeder
```

Create a real administrator (there is no public admin registration):

```bash
php artisan zytecs:create-admin owner@example.com --name="Owner Name"
```

Use `--generate-password` to have a strong random password generated and shown once.

## Tests and checks

```bash
php artisan test
```

```bash
vendor/bin/phpstan analyse
```

```bash
vendor/bin/pint --test
```

```bash
npx playwright install chromium && npx playwright test
```

```bash
composer audit && npm audit
```

Coverage (requires Xdebug or PCOV): `XDEBUG_MODE=coverage php artisan test --coverage`.

Browser tests start their own server on port 8001 against an isolated `database/e2e.sqlite`, create a throwaway admin, and save screenshots to `storage/framework/testing/screenshots`.

## Documentation

- [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) — server setup, cron, queue, HTTPS, launch checklist
- [docs/EMAIL-CONFIGURATION.md](docs/EMAIL-CONFIGURATION.md) — SMTP/provider setup and verification
- [docs/SECURITY.md](docs/SECURITY.md) — controls, threat review and remaining risks
- [docs/ADMIN-GUIDE.md](docs/ADMIN-GUIDE.md) — day-to-day administration and content review
- [docs/ASSET-LICENCES.md](docs/ASSET-LICENCES.md) — logo, photography, icons and fonts
- [docs/PORTFOLIO-DRAFTS.md](docs/PORTFOLIO-DRAFTS.md) — how the portfolio was built and items for owner review
- [docs/TEST-PLAN.md](docs/TEST-PLAN.md) and [docs/TEST-RESULTS.md](docs/TEST-RESULTS.md)

## Project layout (non-standard parts)

- `config/zytecs.php` — business details, OTP, upload, notification and retention settings
- `resources/content/` — services, FAQs and the anonymised portfolio data
- `resources/icons/` — Lucide SVG icons used by `<x-icon>`
- `resources/brand-source/` — the supplied logo (original) and its transparent derivative
- `app/Actions/SubmitBrief.php` — atomic submission with file cleanup and notification hand-off
- `app/Services/EmailOtpService.php` — OTP generation, hashing, verification
- `app/Rules/SafeDocument.php` — server-side upload validation
- `tests/Feature`, `tests/Unit` — PHPUnit; `tests/Browser` — Playwright + axe-core
