# Administrator guide

## Signing in

Go to `/login` and sign in with an administrator account. You land on `/admin`. Administrators are created on the server:

```bash
php artisan zytecs:create-admin someone@zytecs.com --name="Their Name"
```

To change your password, use **Forgot password** on the sign-in page. There is no public admin sign-up.

## Overview (`/admin`)

Live counts from the database: registered students, verified accounts, total submissions, New, Under review, Awaiting student response, Completed/closed/declined, and failed notifications. Banners appear for unhandled enquiries and pending deletion requests.

## Submissions

- **Search** by student name, email, reference, assignment name or module code; **filter** by status, country, subject and date range. 20 per page.
- **Open** a submission to see student contact details (with a WhatsApp link), brief details, files, notification status, status history and internal notes.
- **Download** files with the Download button. Every download is recorded in the audit log. Open files in Protected View; uploads are not virus-scanned.
- **Status**: choose a new status and optionally add a note. Only record events that actually happened (for example, choose "Quotation sent" after you have sent it). Completed, Closed and Declined set the closure date that starts the retention clock. "Close submission" is a shortcut.
- **Internal notes** are visible only to administrators, never to students.
- Students see the status label and a short explanation on their dashboard.

Statuses: New → Under review → Awaiting student response → Quotation sent → Accepted → In progress → Completed, or Closed / Declined.

## Email notifications

Each new brief and enquiry creates a notification record. **Sent** means the mail server accepted it. **Pending** means it is queued or waiting for an automatic retry. **Failed** means automatic retries were used up. Use **Retry now** on the submission page or in Notifications after fixing mail settings. Briefs are never lost because of email problems.

## Students

- Search by name, email or WhatsApp; filter unverified, suspended, or deletion requested.
- **Suspend** signs the student out immediately and blocks sign-in. **Reactivate** restores access.
- **Delete permanently** (type the student's email to confirm) removes the account, all submissions, notes and stored files. Use it to process deletion requests after checking whether any records must legally be kept. Passwords and verification codes are never shown.

## Enquiries

Contact-form messages. Reply by WhatsApp or email, then **Mark as handled**. Enquiries older than 12 months are deleted automatically.

## Audit log and settings

- **Audit log**: who did what and when (sign-ins, downloads, status changes, suspensions, deletions, retries).
- **Settings**: read-only view of operational settings with warnings (for example if email is not configured). Change settings in the server `.env` file.

## Retention (automatic)

- Completed, closed and declined submissions and their files: deleted 365 days after closure (`ZYTECS_RETENTION_DAYS`). Preview with `php artisan zytecs:prune-submissions --dry-run`.
- Enquiries: 12 months. Expired verification-code records: 7 days.

## Content review (before launch and periodically)

Site content lives in files, so changes are made by a developer:

| Content | File |
| --- | --- |
| Business details, contact numbers, experience years | `config/zytecs.php` |
| Services (17) | `resources/content/services.php` |
| FAQs | `resources/content/faqs.php` |
| Portfolio and subject directory | `resources/content/portfolio.php` (see `docs/PORTFOLIO-DRAFTS.md`) |
| Page text | `resources/views/pages/*.blade.php` |

Please confirm that every listed service is one the business genuinely provides **in the way described** (guidance, feedback, proofreading — not writing work for submission), and that the FAQ answers on turnaround, revisions and refunds match how you actually work.
