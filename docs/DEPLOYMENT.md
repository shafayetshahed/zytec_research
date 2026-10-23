# Deployment

Production target: **https://research.zytecs.com**. Deployment has **not** been performed from the development environment; these are the steps to do it.

## 1. Hosting requirements

| Need | Detail |
| --- | --- |
| PHP | 8.3+ with `bcmath ctype curl dom fileinfo gd intl mbstring openssl pdo_mysql tokenizer xml zip` |
| Database | MySQL 8.0+ or MariaDB 10.6+ (`utf8mb4`) |
| Web server | Nginx or Apache, **document root = the project's `public/` directory** |
| HTTPS | Required (Let's Encrypt or the host's certificate) |
| Cron | One entry, every minute (see below) |
| Disk | Private storage for briefs under `storage/app/private` — outside `public/` |
| Outbound HTTPS | Allowed to `cdn.jsdelivr.net` and `*.currency-api.pages.dev` for exchange rates (if blocked, enter rates manually) |
| PHP limits | `upload_max_filesize = 25M`, `post_max_size = 110M` (brief + 4 supporting files at 20 MB), `max_execution_time = 60`, `memory_limit = 256M` |

Shared hosting (cPanel) works if you can point the domain's document root at `public/` and add a cron job. If the host forces `public_html`, put the project one level above it and make `public_html` contain only the contents of `public/` (adjust `index.php` paths) — never place the whole project inside `public_html`.

## 2. First deployment

```bash
git clone <repository> zytecs-research && cd zytecs-research
```

```bash
composer install --no-dev --optimize-autoloader
```

```bash
cp .env.example .env && php artisan key:generate
```

Edit `.env` (see the table below), then:

```bash
php artisan migrate --force
```

```bash
npm ci && npm run build
```

(or build locally and upload `public/build/`)

```bash
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

```bash
php artisan zytecs:create-admin research@zytecs.com --name="Zytecs Research"
```

Permissions: the web server user must be able to write `storage/` and `bootstrap/cache/`; nothing else should be writable.

## 3. Production `.env`

| Key | Production value |
| --- | --- |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://research.zytecs.com` |
| `DB_CONNECTION` | `mysql` (plus `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) |
| `SESSION_DRIVER` | `database` (required: suspensions and password changes sign users out by deleting session rows) |
| `SESSION_SECURE_COOKIE` | `true` |
| `SESSION_ENCRYPT` | `true` |
| `QUEUE_CONNECTION` | `database` |
| `CACHE_STORE` | `database` (or `redis`) |
| `MAIL_*` | real SMTP/provider settings — see `docs/EMAIL-CONFIGURATION.md`. **Not** `log`. |
| `ZYTECS_NOTIFY_ADDRESS` | `research@zytecs.com` |
| `ZYTECS_POLICIES_REVIEWED` | `true` only after legal review of the privacy policy and terms |
| `TRUSTED_PROXIES` | Set if behind Cloudflare or a load balancer, so rate limits see real client IPs |
| `LOG_LEVEL` | `warning` |
| `BOOST_BROWSER_LOGS_WATCHER` | `false` (dev tool; not installed with `--no-dev`) |

Never commit `.env`. `.env.example` contains placeholders only.

## 4. Scheduler and queue

Add one cron entry:

```
* * * * * cd /path/to/zytecs-research && php artisan schedule:run >> /dev/null 2>&1
```

This runs: the queue worker every minute (`queue:work --stop-when-empty`, for hosts without Supervisor), the daily retention prune, `model:prune` (old enquiries and expired OTP records) and expired password-reset token cleanup.

If you can run a long-lived worker (VPS with Supervisor/systemd), set `ZYTECS_SCHEDULED_QUEUE_WORKER=false` and run `php artisan queue:work --tries=3 --max-time=3600` under a process manager instead.

## 5. Web server notes

- Deny access to dotfiles and anything outside `public/`.
- Do not enable directory listing.
- Laravel already sends `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, a Content-Security-Policy, and HSTS on HTTPS requests.
- Redirect `http://` to `https://` and `www.` to the apex domain at the server level.

## 6. Backups

- Database: daily encrypted dump, kept 30 days, stored off-server with restricted access.
- Files: `storage/app/private/briefs` daily to private (non-public) storage. Backups contain student documents, so treat them with the same access controls and retention as the live system.
- Test a restore before launch.

## 7. Updating

```bash
php artisan down --render="errors::503"
```

```bash
git pull && composer install --no-dev --optimize-autoloader && npm ci && npm run build
```

```bash
php artisan migrate --force && php artisan optimize
```

```bash
php artisan up
```

## 8. Pre-launch checklist

- [ ] HTTPS active; `APP_URL` uses `https://`; `SESSION_SECURE_COOKIE=true`
- [ ] `APP_DEBUG=false`, `APP_ENV=production`
- [ ] SMTP configured and a real test email received at `research@zytecs.com` (OTP and new-brief notification) — see `docs/EMAIL-CONFIGURATION.md`
- [ ] SPF, DKIM and DMARC records set for the sending domain
- [ ] First administrator created; demo data **not** seeded
- [ ] Cron entry active; `php artisan schedule:list` shows the tasks
- [ ] Admin → Settings page shows no warnings
- [ ] Privacy policy and terms reviewed by a lawyer, then `ZYTECS_POLICIES_REVIEWED=true`
- [ ] Owner has reviewed every service description, FAQ and the portfolio (`docs/ADMIN-GUIDE.md`, "Content review")
- [ ] Backups configured and a restore tested
- [ ] `robots.txt` sitemap URL matches the live domain; submit `sitemap.xml` in Google Search Console
- [ ] Run Lighthouse against the live site and record the results in `docs/TEST-RESULTS.md`
