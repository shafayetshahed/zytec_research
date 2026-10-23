<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EnquiryController extends Controller
{
    public function index(): View
    {
        return view('admin.enquiries.index', [
            'enquiries' => Enquiry::latest()->paginate(25),
        ]);
    }

    public function show(Enquiry $enquiry): View
    {
        return view('admin.enquiries.show', ['enquiry' => $enquiry]);
    }

    public function markHandled(Enquiry $enquiry, AuditLogger $audit): RedirectResponse
    {
        $enquiry->handled_at = $enquiry->handled_at ? null : now();
        $enquiry->save();

        $audit->record($enquiry->handled_at ? 'enquiry.handled' : 'enquiry.reopened', $enquiry);

        return back()->with('status', $enquiry->handled_at ? 'Marked as handled.' : 'Marked as open.');
    }
}
