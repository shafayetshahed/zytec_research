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

## Quotations and payments

Students pay you outside the website (bank transfer, bKash, Nagad, Rocket, Wise, PayPal, cash). The portal records quotations and payments; it does not take card payments.

1. **Create the quotation**: on a submission, open **Quotation and payments**. Choose the currency (AUD, GBP, USD, CAD, BDT or MYR), the total price, the deposit needed to start (0 if none), an optional delivery date, the country (used for accounting) and an optional message to the student, such as how to pay.
   - **Save as draft** keeps it private.
   - **Save and send to student** emails the quotation and shows it, highlighted, on the student's request page and dashboard. The status moves to "Quotation sent". You can edit and re-send it; the currency is locked once payments exist.
2. **Confirm on WhatsApp** with the student as usual.
3. **Record each payment** under **Record a payment or refund**: amount, date received, method and transaction reference.
   - **Exchange rate**: leave the rate blank to use the market rate for that date (from a free rate service), or type the rate you actually received in BDT per 1 unit. BDT quotes need no rate. The rate and the BDT value are saved with the payment and never change later.
   - **Receipt**: the student gets a receipt email unless you untick the box. Refunds never send a receipt.
   - **Payment status** updates automatically: Awaiting payment → Part paid → Deposit paid → Paid in full. When the deposit is reached, the work status moves to "In progress".
4. **Complete the request**: a request with a quotation can only be marked **Completed** once it is **Paid in full**. Mistakes can be removed with **Delete**; deletions are recorded in the audit log.

## Clients and manual orders (WhatsApp, phone, email)

Orders taken outside the portal go into the same records, so accounting covers everything.

1. **Add the client once.** Go to **Students & clients → Add client**. Only the name is required; you can also add a WhatsApp number, email and country.
   - **One profile per person**: a person who sometimes orders in the portal and sometimes on WhatsApp keeps a single profile. If they already registered in the portal, just pick them; don't add them again.
   - **Portal access later**: if you add an email address, the client can later use **Forgot password** on the sign-in page to set a password, verify their email and see their orders in the portal.
2. **Add the order.** Click **New manual order**, from Accounting, from Students & clients, or from the client's own page, where the client is pre-selected. Fill in:
   - where the order came from (WhatsApp, phone, email, in person or other);
   - the work name, with the module code if you have one;
   - the country, currency, price, deposit/advance and order date;
   - optionally, the delivery date and internal notes.

   The order gets its own reference (for example `ZR-2610-K7Q2MX`) and quotation number. Its status starts as **Accepted**.
3. **Record payments** on the order's page exactly as for portal orders. The deposit moves the work to **In progress**, the receipt PDF is available for sharing on WhatsApp, and the final invoice is issued when it is paid in full.
4. **Emails** go to the client only if they have an email address (you can tick **Email the quotation PDF** when adding the order). Manual orders never trigger the "New brief" email to the business inbox.

In **Accounting**, filter by **Source** to compare portal and WhatsApp income. The **By client** table shows what each client paid in the period and still owes now.

## Order and payment reference

Every request has one reference, created when the student submits it (for example `ZR-2610-K7Q2MX`). Students are asked to:

- write it in the reference or note field of bKash, Nagad, Rocket, bank, Wise or any other payment;
- mention it when they send a payment screenshot by email or WhatsApp.

The reference and these instructions appear in the quotation email (and its subject line), on the quotation PDF, on the student's request page while a balance is outstanding, and in receipt emails. Search for it in **Submissions** to find the request, then record the payment, putting the bKash or bank transaction ID in **Transaction reference**.

## Quotations, receipts and invoices (PDF)

The site produces three PDF documents with your logo, your details and the client's name and email. It never includes the student's WhatsApp number or your exchange rates.

| Document | Number | Issued when | Where to find it |
| --- | --- | --- | --- |
| Quotation | `QUO-2026-0001` | You first send a quotation (re-sending keeps the number) | Attached to the quotation email; **Quotation** button on the submission; **Quotation PDF** on the student's page |
| Payment receipt | `RCP-2026-0001` | Each payment you record, including advances and deposits (not refunds) | Attached to the receipt email; **Receipt** link beside each payment, for you and the student |
| Final invoice, marked PAID | `INV-2026-0001` | The first time the balance is fully paid | Attached to the final receipt email; **Invoice** button on the submission; **Final invoice (paid)** for the student |

- **Numbering**: numbers run in sequence per year and are never reused.
- **Business details**: add a postal address or registration details to the documents with `ZYTECS_INVOICE_ADDRESS` in `.env` (separate lines with `|`, for example `House 1, Road 2|Dhaka 1200|Bangladesh`). Change the closing line with `ZYTECS_INVOICE_NOTE`.
- **Deleted payments**: if you delete a payment so a request is no longer paid in full, its invoice stops being downloadable until it is fully paid again. The invoice keeps the same number.

## Accounting (`/admin/accounting`)

- **Periods**: this month, last month, this year, last year, all time, or custom dates. Filter by country and currency.
- **Totals**: net income in BDT, payments received, refunds given, and an estimate of everything still outstanding at today's rates.
- **Breakdowns**:
  - by currency and by country, with the native amount and the BDT value;
  - by month and by year, with a column per currency and a BDT total;
  - outstanding balances per request, what was quoted in the period, and every transaction.
- **Export CSV** downloads the transactions for the selected filters, for your accountant or spreadsheet.
- **How income is counted**: on the date each payment was received (cash basis), using the rate saved with that payment.
- **Retention**: payment records are kept even after a request is deleted under the retention policy. They keep only the request reference and country, not the student's details or documents.

## Email notifications

Each new brief, enquiry, quotation and payment receipt creates a notification record. **Sent** means the mail server accepted it. **Pending** means it is queued or waiting for an automatic retry. **Failed** means automatic retries were used up. Use **Retry now** on the submission page or in Notifications after fixing mail settings. Briefs are never lost because of email problems.

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
