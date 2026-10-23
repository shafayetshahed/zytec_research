<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\UpdatePasswordRequest;
use App\Http\Requests\Student\UpdateProfileRequest;
use App\Services\AuditLogger;
use App\Support\WhatsAppNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('student.profile', ['user' => $request->user()]);
    }

    public function update(UpdateProfileRequest $request, AuditLogger $audit): RedirectResponse
    {
        $user = $request->user();
        $user->name = $request->validated('name');
        $user->whatsapp_number = WhatsAppNumber::normalize($request->validated('whatsapp_number'));
        $user->save();

        $audit->record('account.profile_updated', $user);

        return back()->with('status', 'Your profile has been updated.');
    }

    public function updatePassword(UpdatePasswordRequest $request, AuditLogger $audit): RedirectResponse
    {
        $user = $request->user();
        $user->password = $request->validated('password');
        // Invalidate "keep me signed in" cookies on every other device.
        $user->setRememberToken(Str::random(60));
        $user->save();

        DB::table('sessions')->where('user_id', $user->id)->where('id', '!=', $request->session()->getId())->delete();
        $request->session()->regenerate();

        $audit->record('account.password_changed', $user);

        return back()->with('status', 'Your password has been changed and other sessions were signed out.');
    }

    public function requestDeletion(Request $request, AuditLogger $audit): RedirectResponse
    {
        $request->validate(['confirm_deletion' => ['accepted']], ['confirm_deletion.accepted' => 'Please tick the box to confirm your request.']);

        $user = $request->user();

        if ($user->deletion_requested_at === null) {
            $user->deletion_requested_at = now();
            $user->save();
            $audit->record('account.deletion_requested', $user);
        }

        return back()->with('status', 'Your deletion request has been recorded. We will confirm by email once it has been processed.');
    }
}
