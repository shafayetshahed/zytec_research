<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\EmailOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request, EmailOtpService $otp, AuditLogger $audit): RedirectResponse
    {
        $user = new User;
        $user->fill([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'whatsapp_number' => $request->normalizedWhatsApp(),
        ]);
        $user->role = UserRole::Student;
        $user->status = AccountStatus::Active;
        $user->terms_accepted_at = now();
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        $audit->record('account.registered', $user, actorId: $user->id);

        try {
            $otp->issue($user);
        } catch (Throwable $exception) {
            Log::error('Verification code email could not be sent after registration.', ['user' => $user->id, 'exception' => class_basename($exception)]);

            return redirect()->route('verification.notice')->with('error', 'Your account was created, but we could not send the verification email. Please use "Send a new code" in a minute.');
        }

        return redirect()->route('verification.notice')->with('status', 'We have sent a six-digit code to '.$user->email.'.');
    }
}
