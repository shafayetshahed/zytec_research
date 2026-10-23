<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Support\Number;
use Illuminate\View\View;

/**
 * Read-only overview of operational settings. Secrets are never displayed.
 */
class SettingsController extends Controller
{
    public function index(): View
    {
        $mailer = (string) config('mail.default');

        return view('admin.settings', [
            'settings' => [
                'Environment' => app()->environment(),
                'Public URL' => config('app.url'),
                'Mail transport' => $mailer,
                'Mail sender' => config('mail.from.name').' <'.config('mail.from.address').'>',
                'Notification recipient' => config('zytecs.notifications.admin_address'),
                'Attach briefs up to' => config('zytecs.notifications.attach_max_kb') > 0 ? Number::fileSize(config('zytecs.notifications.attach_max_kb') * 1024) : 'Never (secure link only)',
                'Queue connection' => config('queue.default'),
                'OTP expiry' => config('zytecs.otp.expires_minutes').' minutes',
                'OTP attempts' => config('zytecs.otp.max_attempts'),
                'OTP resend cooldown' => config('zytecs.otp.resend_cooldown_seconds').' seconds',
                'Maximum upload size' => Number::fileSize(config('zytecs.uploads.max_kb') * 1024),
                'Allowed file types' => implode(', ', array_keys(config('zytecs.uploads.allowed'))),
                'Retention for closed submissions' => config('zytecs.retention.closed_submission_days').' days',
                'Session lifetime' => config('session.lifetime').' minutes',
                'Secure session cookie' => config('session.secure') ? 'Yes' : 'No',
            ],
            'warnings' => array_values(array_filter([
                in_array($mailer, ['log', 'array'], true) ? 'Mail is not being delivered (transport "'.$mailer.'"). Configure SMTP before launch, and never use the log transport in production because it writes verification codes to log files.' : null,
                app()->isProduction() && config('app.debug') ? 'APP_DEBUG is enabled in production. Disable it.' : null,
                app()->isProduction() && ! config('session.secure') ? 'SESSION_SECURE_COOKIE should be true in production (HTTPS).' : null,
                config('queue.default') === 'sync' ? 'The queue runs synchronously; a slow mail server will slow down submissions.' : null,
            ])),
        ]);
    }

    public function audit(): View
    {
        return view('admin.audit', [
            'logs' => AuditLog::with('actor')->latest('id')->paginate(50),
        ]);
    }
}
