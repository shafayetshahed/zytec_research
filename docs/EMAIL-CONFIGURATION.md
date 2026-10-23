# Email configuration

The application sends three kinds of email:

| Email | To | When | Delivery |
| --- | --- | --- | --- |
| Verification code (OTP) | Student | Registration and "Send a new code" | Synchronous, so the student sees an error immediately if sending fails |
| New brief | `ZYTECS_NOTIFY_ADDRESS` (default `research@zytecs.com`) | After a submission is saved | Queued, retried, logged |
| New enquiry | `ZYTECS_NOTIFY_ADDRESS` | After the contact form is submitted | Queued, retried, logged |
| Password reset link | Student or admin | Forgot password | Synchronous (Laravel notification) |

**Status: real email delivery has NOT been verified.** No SMTP credentials were available during development; all automated tests use Laravel's fake mail transport and local development writes emails to `storage/logs/mail.log`.

## 1. Choose a transport

Any SMTP service works. For deliverability, use a transactional provider (for example Postmark, Amazon SES, Mailgun, Brevo or Resend) or the SMTP server supplied with the `zytecs.com` mailbox.

Example `.env` (placeholders):

```
MAIL_MAILER=smtp
MAIL_SCHEME=smtps
MAIL_HOST=smtp.example-provider.com
MAIL_PORT=465
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-smtp-password
MAIL_FROM_ADDRESS="research@zytecs.com"
MAIL_FROM_NAME="Zytecs Research"
ZYTECS_NOTIFY_ADDRESS=research@zytecs.com
```

Use port 587 with `MAIL_SCHEME=smtp` if your provider requires STARTTLS. Remove `MAIL_LOG_CHANNEL` in production.

## 2. Domain authentication

Add the provider's **SPF** and **DKIM** DNS records for `zytecs.com`, and a **DMARC** record (start with `p=none`, move to `quarantine` once reports are clean). Without these, verification codes are likely to land in spam.

## 3. Attachments (optional)

By default the new-brief email contains a **secure admin-panel link** (sign-in required) instead of the file. To also attach small briefs, set `ZYTECS_NOTIFY_ATTACH_MAX_KB` (for example `5120` for 5 MB). Larger files are never attached; the link is always included. Email is not an encrypted channel, so leaving attachments off is the more private choice.

## 4. Verify delivery (staging, then production)

1. `php artisan config:clear`
2. Register a test account with an inbox you control; confirm the six-digit code arrives, is readable, and works.
3. Submit a synthetic brief (a dummy PDF); confirm `research@zytecs.com` receives "New brief ZR-…", the details are correct, and the button opens the admin page after signing in.
4. In Admin → Notifications, confirm the record shows **Sent**.
5. Use "Forgot password" and confirm the reset link works and expires after 60 minutes.
6. Temporarily set a wrong `MAIL_PASSWORD`, submit another brief, confirm the record shows **Pending/Failed** with a generic error (no credentials), restore the password, and use **Retry now**.
7. Record the results (date, provider, inbox/spam placement) in `docs/TEST-RESULTS.md`.

## 5. Failure handling

- Notification jobs retry up to `ZYTECS_NOTIFY_MAX_ATTEMPTS` times (default 3) with back-off of 1, 5 and 15 minutes.
- Each attempt updates the `notification_logs` table: status, attempts, timestamps and a sanitised error such as `Delivery failed (TransportException).` Raw provider messages and credentials are never stored.
- Failed notifications are counted on the admin dashboard and can be retried from the submission page or Admin → Notifications.
- A brief is **never lost** because of an email failure: the submission is saved first.
- If the verification email fails at registration, the account is still created and the student is told to request a new code.

## 6. What is never logged

Verification codes, passwords, reset tokens, SMTP credentials and document contents are not written to application logs or the audit log. The only exception is the local-development `log` mailer, which writes whole emails to `storage/logs/mail.log`; the Admin → Settings page warns if it is active.
