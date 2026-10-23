<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Business details
    |--------------------------------------------------------------------------
    |
    | Public contact details for Zytecs Research. WhatsApp is the primary
    | enquiry channel, email is secondary, and the telephone number is for
    | emergencies only.
    |
    */

    'business' => [
        'name' => 'Zytecs Research',
        'url' => env('ZYTECS_PUBLIC_URL', 'https://research.zytecs.com'),
        'email' => env('ZYTECS_BUSINESS_EMAIL', 'research@zytecs.com'),
        'whatsapp_url' => 'https://wa.me/message/V3FR2DQG5IJ7J1',
        'emergency_phone' => '+8801871333382',
        'emergency_phone_display' => '+880 1871 333382',
        'experience_years' => 5,
        // Set to true once the privacy policy and terms have had legal review.
        'policies_reviewed' => (bool) env('ZYTECS_POLICIES_REVIEWED', false),
        'policies_updated' => '23 October 2026',
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | New-brief and enquiry notifications are sent to this address. Small
    | briefs can optionally be attached; set the limit to 0 to always rely on
    | the authenticated admin-panel link instead.
    |
    */

    'notifications' => [
        'admin_address' => env('ZYTECS_NOTIFY_ADDRESS', 'research@zytecs.com'),
        'attach_max_kb' => (int) env('ZYTECS_NOTIFY_ATTACH_MAX_KB', 0),
        'max_attempts' => (int) env('ZYTECS_NOTIFY_MAX_ATTEMPTS', 3),
        // Drain the queue from the scheduler (for hosts without a supervisor-managed worker).
        'scheduled_queue_worker' => (bool) env('ZYTECS_SCHEDULED_QUEUE_WORKER', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Email one-time passcodes
    |--------------------------------------------------------------------------
    */

    'otp' => [
        'length' => 6,
        'expires_minutes' => (int) env('ZYTECS_OTP_EXPIRES_MINUTES', 10),
        'max_attempts' => (int) env('ZYTECS_OTP_MAX_ATTEMPTS', 5),
        'resend_cooldown_seconds' => (int) env('ZYTECS_OTP_RESEND_COOLDOWN', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Brief uploads
    |--------------------------------------------------------------------------
    |
    | Each allowed extension maps to the MIME types that the server-side
    | content sniffer (finfo) may report for it. Anything outside this list is
    | rejected regardless of what the browser claims.
    |
    */

    'uploads' => [
        'disk' => env('ZYTECS_UPLOAD_DISK', 'local'),
        'directory' => 'briefs',
        'max_kb' => (int) env('ZYTECS_UPLOAD_MAX_KB', 20480),
        'max_supplementary_files' => 4,
        'allowed' => [
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword', 'application/CDFV2', 'application/x-ole-storage'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
            'ppt' => ['application/vnd.ms-powerpoint', 'application/CDFV2', 'application/x-ole-storage'],
            'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip'],
            'xls' => ['application/vnd.ms-excel', 'application/CDFV2', 'application/x-ole-storage'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Accounting
    |--------------------------------------------------------------------------
    |
    | Exchange rates to BDT come from the free, keyless fawazahmed0
    | currency-api (CC0), with a mirror as fallback. {date} is YYYY-MM-DD or
    | "latest"; {base} is a lower-case currency code. Rates are suggestions:
    | the administrator can always overwrite them with the rate received.
    |
    */

    'accounting' => [
        'base_currency' => 'BDT',
        'rate_urls' => [
            'https://cdn.jsdelivr.net/npm/@fawazahmed0/currency-api@{date}/v1/currencies/{base}.json',
            'https://{date}.currency-api.pages.dev/v1/currencies/{base}.json',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Retention
    |--------------------------------------------------------------------------
    |
    | Closed, completed and declined submissions (and their files) become
    | eligible for deletion after this many days. Run
    | `php artisan zytecs:prune-submissions` (scheduled daily) to apply it.
    |
    */

    'retention' => [
        'closed_submission_days' => (int) env('ZYTECS_RETENTION_DAYS', 365),
    ],

];
