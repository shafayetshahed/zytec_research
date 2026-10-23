<?php

namespace App\Http\Controllers;

use App\Enums\EnquiryType;
use App\Enums\NotificationStatus;
use App\Http\Requests\ContactRequest;
use App\Jobs\SendBusinessNotification;
use App\Models\Enquiry;
use App\Models\NotificationLog;
use App\Support\SiteContent;
use App\Support\WhatsAppNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('pages.contact', [
            'types' => EnquiryType::cases(),
            'countries' => SiteContent::countriesOfStudy(),
        ]);
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        $enquiry = Enquiry::create([
            ...$request->safe()->except(['website', 'whatsapp_number']),
            'email' => $request->validated('email') ? mb_strtolower($request->validated('email')) : null,
            'whatsapp_number' => WhatsAppNumber::normalize($request->validated('whatsapp_number')),
        ]);

        $log = new NotificationLog;
        $log->enquiry_id = $enquiry->id;
        $log->type = NotificationLog::TYPE_NEW_ENQUIRY;
        $log->recipient = (string) config('zytecs.notifications.admin_address');
        $log->status = NotificationStatus::Pending;
        $log->save();

        try {
            SendBusinessNotification::dispatch($log->id);
        } catch (Throwable $exception) {
            $log->status = NotificationStatus::Failed;
            $log->error_summary = 'Could not queue the notification ('.class_basename($exception).').';
            $log->save();
            Log::error('Enquiry notification could not be queued.', ['enquiry' => $enquiry->id, 'exception' => class_basename($exception)]);
        }

        return redirect()->route('contact')->with('status', 'Thank you. Your message has been received and we will reply as soon as we can. For anything urgent, message us on WhatsApp.');
    }
}
