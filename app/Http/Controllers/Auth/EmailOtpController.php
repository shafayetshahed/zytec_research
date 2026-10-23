<?php

namespace App\Http\Controllers\Auth;

use App\Enums\OtpResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Services\AuditLogger;
use App\Services\EmailOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class EmailOtpController extends Controller
{
    public function show(Request $request, EmailOtpService $otp): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('student.dashboard');
        }

        return view('auth.verify-email', [
            'maskedEmail' => $this->mask($user->email),
            'resendIn' => $otp->secondsUntilResend($user),
            'expiresMinutes' => (int) config('zytecs.otp.expires_minutes'),
        ]);
    }

    public function verify(VerifyOtpRequest $request, EmailOtpService $otp, AuditLogger $audit): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('student.dashboard');
        }

        $result = $otp->verify($user, $request->validated('code'));

        if ($result !== OtpResult::Verified) {
            $audit->record('account.otp_failed', $user, ['reason' => $result->value]);

            return back()->withErrors(['code' => $result->message()]);
        }

        $audit->record('account.email_verified', $user);
        $request->session()->regenerate();

        return redirect()->route($user->isAdmin() ? 'admin.dashboard' : 'student.dashboard')
            ->with('status', $result->message());
    }

    public function resend(Request $request, EmailOtpService $otp): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('student.dashboard');
        }

        $wait = $otp->secondsUntilResend($user);

        if ($wait > 0) {
            return back()->with('error', "Please wait {$wait} seconds before requesting another code.");
        }

        try {
            $otp->issue($user);
        } catch (Throwable $exception) {
            Log::error('Verification code email could not be sent.', ['user' => $user->id, 'exception' => class_basename($exception)]);

            return back()->with('error', 'We could not send the email just now. Please try again shortly.');
        }

        return back()->with('status', 'A new code has been sent. Any earlier code no longer works.');
    }

    private function mask(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2) + [1 => ''];

        return Str::substr($local, 0, 2).str_repeat('•', max(1, Str::length($local) - 2)).'@'.$domain;
    }
}
