# Security

This document lists the controls in place, the results of the independent review, and the risks that remain. It is not a certification; nothing here claims legal or regulatory compliance.

## Controls

### Authentication and sessions
- Passwords hashed with bcrypt (Laravel `hashed` cast); production password rule: 10+ characters with letters and numbers, checked against known breaches (`uncompromised()`).
- Session regenerated on login, registration, OTP verification and password change; logout invalidates the session and CSRF token.
- Sessions are database-backed and encrypted, cookies `HttpOnly`, `SameSite=Lax`, `Secure` in production (`SESSION_SECURE_COOKIE=true`).
- Changing a password deletes the user's other sessions and rotates the remember-me token. Password reset deletes all sessions and rotates the token.
- Suspended accounts are signed out on their next request (`active` middleware) and cannot sign in.
- Rate limits: login 5/min per email+IP, 20/min per IP, 30/hour per account; registration 10/hour per IP; password reset 3/min per email+IP and 20/hour per IP; OTP verify 10/min per user and 30/min per IP; OTP resend 60 s cooldown plus 6/hour per user and 20/hour per IP; submissions 15/hour per user; enquiries 5/hour per IP; downloads 60/min per user.
- Password-reset links are always built from `APP_URL`, and in production requests with a `Host` other than `APP_URL`'s host (or its subdomains) are rejected (`trustHosts`).

### Email OTP
- Six digits from `random_int` (CSPRNG); stored only as HMAC-SHA256 keyed with `APP_KEY` and bound to the user ID; compared with `hash_equals`.
- Expires after 10 minutes (configurable), single use, maximum 5 attempts then invalidated, any earlier code invalidated when a new one is issued, verification runs in a locked transaction.
- Never placed in URLs, pages, application logs or the audit log.
- Unverified accounts cannot reach any student route or submit briefs (`verified` middleware).

### Authorisation
- Every admin route: `auth`, `active`, `verified`, `role:admin` (server-side). No admin registration route exists; admins are created with `php artisan zytecs:create-admin`.
- Students can only load their own submissions and files: lookups are scoped to the signed-in user and return 404 (not 403) for anyone else's reference or file ID.
- Admin actions on "students" refuse admin accounts (404). File downloads check that the file belongs to the submission in the URL.
- Mass assignment: `#[Fillable]` lists exclude `role`, `status`, `email_verified_at`, `user_id`, `reference` and `status`; these are set explicitly in code.

### Uploads and private files
- Allow-list of extensions (PDF, DOC/DOCX, PPT/PPTX, XLS/XLSX, JPG/JPEG, PNG), size limit 20 MB (configurable), non-empty.
- Server-side MIME detection (`finfo`) must match the extension; file signature (magic bytes) checked; DOCX/PPTX/XLSX must contain the right Office parts; macro-enabled Office Open XML (`macroEnabled` content types or `vbaProject.bin`) is rejected.
- Stored on the private `local` disk (`storage/app/private`, outside the web root, `serve=false`) under random UUID names; the original filename is sanitised (path stripped, control/bidi characters removed) and used only for display and `Content-Disposition`.
- Downloads only through authenticated, authorised controllers with `Content-Disposition: attachment`, `X-Content-Type-Options: nosniff` and `Cache-Control: private, no-store`; each download is audit-logged.
- Submissions are saved in a transaction; stored files are deleted if anything fails; duplicate form submissions are de-duplicated with a per-form token.

### Web protections
- CSRF tokens on all state-changing forms (Laravel `PreventRequestForgery`).
- Blade escaping everywhere; no unescaped user content. User text inserted into Markdown emails is Markdown-escaped so it cannot inject links.
- Headers: Content-Security-Policy (`script-src 'self'`, no inline scripts, `frame-ancestors 'none'`, `object-src 'none'`, `form-action 'self'`), `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, `Permissions-Policy`, HSTS over HTTPS.
- Private pages send `X-Robots-Tag: noindex, nofollow` and `Cache-Control: no-store` (also on redirects).
- SQL via Eloquent/query builder only; `LIKE` wildcards escaped; filters validated against enums/allow-lists.
- Contact form honeypot and rate limit.

### Quotations, payments and accounting
- Only administrators can create quotations, record or delete payments, and view or export accounting (server-side `role:admin`).
- Students see only their own sent quotations; drafts and exchange rates are never shown to them.
- Money is stored as fixed-point decimals. Each payment keeps its own exchange rate and BDT value, so reports never change after the fact.
- Payment records survive deletion of the request, keeping only the reference and country, for accounting purposes. The privacy policy says so.
- CSV export neutralises spreadsheet formulas in free-text fields.
- Exchange-rate lookups send only the currency code and date to the rate service, with a 5-second timeout and caching; no personal data leaves the server.

### Logging and audit
- `audit_logs` records quotations saved and sent, payments recorded and deleted, sign-ins, failed OTP attempts (reason only), verification, profile/password changes, deletion requests, submissions, downloads, status changes, notes, suspensions, deletions and notification retries, with actor, subject, IP and time.
- Notification logs store a sanitised error class only.
- No passwords, OTPs, tokens, SMTP credentials or document contents are logged by the application.

## Independent review (23 October 2026)

A separate review pass examined auth, OTP, uploads, access control, XSS/CSRF/SQLi, logging and headers. No critical or high-severity issues were found. Findings and outcomes:

| # | Severity | Finding | Outcome |
| --- | --- | --- | --- |
| 1 | Medium (deployment-dependent) | Password-reset links built from the request `Host` header could point to an attacker's domain | **Fixed**: links built from `APP_URL`; `trustHosts` enabled. Tests: `SecurityRegressionTest` |
| 2 | Medium | Password change did not revoke remember-me cookies | **Fixed**: remember token rotated. Test added |
| 3 | Low/medium | Markdown link injection via names/messages in emails | **Fixed**: `MarkdownText::escape` in all mail templates. Test added |
| 4 | Low | No account-wide login limit (IP rotation); raw email used in limiter key | **Fixed**: 30/hour per account; key normalised. Test added |
| 5 | Low | Registration reveals whether an email is registered | **Accepted** product trade-off (clear error for genuine users); mitigated by rate limit. Forgot-password stays generic |
| 6 | Low | Unicode bidi/zero-width characters kept in display filenames | **Fixed**. Test added |
| 7 | Low | Admin delete confirmation used a string `in:` rule | **Fixed**: `Rule::in`. Test added |
| 8 | Info | Dev defaults (`log` mailer, insecure cookie); no in-app admin password change; admin downloads unthrottled | Production values documented in `DEPLOYMENT.md`; Settings page warns; admins change passwords via "Forgot password"; admin downloads now throttled |
| 9 | Info | Valid documents can still carry active content (OLE macros, PDF JavaScript) | **Partly fixed** (macro-enabled OOXML rejected). See remaining risks |

## Remaining risks and recommendations

1. **No malware scanning.** Uploaded files are validated but not virus-scanned. Legacy `.doc/.xls/.ppt` files can contain macros and PDFs can contain JavaScript. Admins should open downloads in Protected View / a sandboxed viewer and keep macros disabled. Consider ClamAV on the server.
2. **No two-factor authentication for administrators.** Recommended before launch if feasible (TOTP).
3. **Registration enumeration** (finding 5) remains by design.
4. **Real email delivery is not verified** (no credentials were available). See `EMAIL-CONFIGURATION.md`.
5. **Backups** contain student documents and must be encrypted and access-controlled.
6. **Legal review** of the privacy policy and terms is outstanding; data-protection obligations differ across Bangladesh, the UK (UK GDPR), Australia (Privacy Act 1988), Canada (PIPEDA) and US states. Publishing a policy does not by itself make the business compliant.
7. **Shared hosting**: if `TRUSTED_PROXIES` is not set behind a proxy/CDN, all users may share one IP for rate limiting.
