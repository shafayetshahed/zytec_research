<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('components.pagination');

        Password::defaults(fn () => $this->app->isProduction()
            ? Password::min(10)->letters()->numbers()->uncompromised()
            : Password::min(10)->letters()->numbers());

        $this->configureRateLimiting();

        // Reset links are always built from APP_URL, never from the request's Host header.
        ResetPassword::createUrlUsing(fn (User $user, string $token): string => rtrim((string) config('app.url'), '/')
            .route('password.reset', ['token' => $token, 'email' => $user->getEmailForPasswordReset()], false));
    }

    private function configureRateLimiting(): void
    {
        $normalisedEmail = fn (Request $request): string => Str::transliterate(Str::lower(trim((string) $request->input('email'))));
        $emailKey = fn (Request $request): string => $normalisedEmail($request).'|'.$request->ip();

        RateLimiter::for('login', fn (Request $request) => [
            Limit::perMinute(5)->by('login:'.$emailKey($request)),
            Limit::perMinute(20)->by('login-ip:'.$request->ip()),
            // Account-wide cap so rotating IP addresses cannot brute-force one account.
            Limit::perHour(30)->by('login-account:'.$normalisedEmail($request)),
        ]);

        RateLimiter::for('register', fn (Request $request) => Limit::perHour(10)->by('register:'.$request->ip()));

        RateLimiter::for('password-reset', fn (Request $request) => [
            Limit::perMinute(3)->by('reset:'.$emailKey($request)),
            Limit::perHour(20)->by('reset-ip:'.$request->ip()),
        ]);

        RateLimiter::for('otp-verify', fn (Request $request) => [
            Limit::perMinute(10)->by('otp-verify:'.$request->user()?->getKey()),
            Limit::perMinute(30)->by('otp-verify-ip:'.$request->ip()),
        ]);

        RateLimiter::for('otp-resend', fn (Request $request) => [
            Limit::perHour(6)->by('otp-resend:'.$request->user()?->getKey()),
            Limit::perHour(20)->by('otp-resend-ip:'.$request->ip()),
        ]);

        RateLimiter::for('submissions', fn (Request $request) => Limit::perHour(15)->by('submit:'.$request->user()?->getKey()));

        RateLimiter::for('enquiries', fn (Request $request) => Limit::perHour(5)->by('enquiry:'.$request->ip()));

        RateLimiter::for('downloads', fn (Request $request) => Limit::perMinute(60)->by('download:'.$request->user()?->getKey()));
    }
}
