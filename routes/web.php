<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\EmailOtpController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Student;
use App\Http\Controllers\Team;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public website
|--------------------------------------------------------------------------
*/

Route::controller(PageController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/about', 'about')->name('about');
    Route::get('/services', 'services')->name('services.index');
    Route::get('/services/{slug}', 'service')->where('slug', '[a-z0-9-]+')->name('services.show');
    Route::get('/subjects', 'subjects')->name('subjects');
    Route::get('/portfolio', 'portfolio')->name('portfolio');
    Route::get('/how-it-works', 'howItWorks')->name('how-it-works');
    Route::get('/why-choose-us', 'whyChooseUs')->name('why-choose-us');
    Route::get('/faq', 'faq')->name('faq');
    Route::get('/academic-integrity', 'academicIntegrity')->name('academic-integrity');
    Route::get('/confidentiality', 'confidentiality')->name('confidentiality');
    Route::get('/privacy-policy', 'privacy')->name('privacy');
    Route::get('/terms', 'terms')->name('terms');
});

Route::permanentRedirect('/faqs', '/faq');
Route::permanentRedirect('/privacy', '/privacy-policy');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:enquiries')->name('contact.store');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::middleware(['private', 'guest'])->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:register');

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:login');

    Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'email'])->middleware('throttle:password-reset')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->middleware('throttle:password-reset')->name('password.update');
});

Route::middleware(['private', 'auth', 'active'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/verify-email', [EmailOtpController::class, 'show'])->name('verification.notice');
    Route::post('/verify-email', [EmailOtpController::class, 'verify'])->middleware('throttle:otp-verify')->name('verification.verify');
    Route::post('/verify-email/resend', [EmailOtpController::class, 'resend'])->middleware('throttle:otp-resend')->name('verification.send');
});

/*
|--------------------------------------------------------------------------
| Student portal
|--------------------------------------------------------------------------
*/

Route::prefix('student')->name('student.')->middleware(['private', 'auth', 'active', 'verified', 'role:student'])->group(function () {
    Route::get('/dashboard', Student\DashboardController::class)->name('dashboard');

    Route::get('/profile', [Student\ProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [Student\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [Student\ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/deletion-request', [Student\ProfileController::class, 'requestDeletion'])->name('profile.deletion');

    Route::get('/submissions', fn () => redirect()->route('student.dashboard'))->name('submissions.index');
    Route::get('/submissions/create', [Student\SubmissionController::class, 'create'])->name('submissions.create');
    Route::post('/submissions', [Student\SubmissionController::class, 'store'])->middleware('throttle:submissions')->name('submissions.store');
    Route::get('/submissions/{reference}', [Student\SubmissionController::class, 'show'])->name('submissions.show');
    Route::get('/submissions/{reference}/files/{file}', [Student\SubmissionController::class, 'download'])
        ->whereNumber('file')->middleware('throttle:downloads')->name('submissions.files.download');

    Route::post('/submissions/{reference}/messages', [Student\WorkController::class, 'postMessage'])->middleware('throttle:messages')->name('messages.store');

    Route::middleware('throttle:downloads')->group(function () {
        Route::get('/submissions/{reference}/materials/{deliverable}', [Student\WorkController::class, 'downloadMaterial'])->whereNumber('deliverable')->name('materials.download');
        Route::get('/submissions/{reference}/messages/{message}/attachment', [Student\WorkController::class, 'downloadAttachment'])->whereNumber('message')->name('messages.attachment');
        Route::get('/submissions/{reference}/quotation.pdf', [DocumentController::class, 'quotation'])->name('documents.quotation');
        Route::get('/submissions/{reference}/receipts/{payment}.pdf', [DocumentController::class, 'receipt'])->whereNumber('payment')->name('documents.receipt');
        Route::get('/submissions/{reference}/invoice.pdf', [DocumentController::class, 'invoice'])->name('documents.invoice');
    });
});

/*
|--------------------------------------------------------------------------
| Administration (no public registration; see `php artisan zytecs:create-admin`)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['private', 'auth', 'active', 'verified', 'role:admin'])->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    Route::get('/submissions', [Admin\SubmissionController::class, 'index'])->name('submissions.index');
    Route::get('/submissions/{submission}', [Admin\SubmissionController::class, 'show'])->name('submissions.show');
    Route::put('/submissions/{submission}/status', [Admin\SubmissionController::class, 'updateStatus'])->name('submissions.status');
    Route::post('/submissions/{submission}/notes', [Admin\SubmissionController::class, 'storeNote'])->name('submissions.notes');
    Route::post('/submissions/{submission}/notifications/{notification}/retry', [Admin\SubmissionController::class, 'retryNotification'])->name('submissions.notifications.retry');
    Route::put('/submissions/{submission}/quotation', [Admin\QuotationController::class, 'save'])->name('submissions.quotation');
    Route::post('/submissions/{submission}/payments', [Admin\PaymentController::class, 'store'])->name('submissions.payments.store');
    Route::delete('/submissions/{submission}/payments/{payment}', [Admin\PaymentController::class, 'destroy'])->name('submissions.payments.destroy');
    Route::put('/submissions/{submission}/assignment', [Admin\OrderWorkController::class, 'assign'])->name('submissions.assign');
    Route::post('/submissions/{submission}/materials', [Admin\OrderWorkController::class, 'uploadMaterial'])->name('submissions.materials.store');
    Route::post('/submissions/{submission}/materials/{deliverable}/approve', [Admin\OrderWorkController::class, 'approveMaterial'])->name('submissions.materials.approve');
    Route::post('/submissions/{submission}/materials/{deliverable}/return', [Admin\OrderWorkController::class, 'returnMaterial'])->name('submissions.materials.return');
    Route::get('/submissions/{submission}/materials/{deliverable}', [Admin\OrderWorkController::class, 'downloadMaterial'])->middleware('throttle:downloads')->name('submissions.materials.download');
    Route::post('/submissions/{submission}/messages/{channel}', [Admin\OrderWorkController::class, 'postMessage'])->whereIn('channel', ['student', 'team'])->middleware('throttle:messages')->name('submissions.messages.store');
    Route::get('/submissions/{submission}/messages/{message}/attachment', [Admin\OrderWorkController::class, 'downloadAttachment'])->middleware('throttle:downloads')->name('submissions.messages.attachment');
    Route::get('/submissions/{submission}/files/{file}', [Admin\SubmissionController::class, 'download'])->middleware('throttle:downloads')->name('submissions.files.download');

    Route::get('/clients/create', [Admin\ClientController::class, 'create'])->name('clients.create');
    Route::post('/clients', [Admin\ClientController::class, 'store'])->name('clients.store');
    Route::get('/students/{student}/edit', [Admin\ClientController::class, 'edit'])->whereNumber('student')->name('clients.edit');
    Route::put('/students/{student}', [Admin\ClientController::class, 'update'])->whereNumber('student')->name('clients.update');

    Route::get('/team', [Admin\TeamController::class, 'index'])->name('team.index');
    Route::get('/team/create', [Admin\TeamController::class, 'create'])->name('team.create');
    Route::post('/team', [Admin\TeamController::class, 'store'])->name('team.store');
    Route::post('/team/{member}/invite', [Admin\TeamController::class, 'resendInvite'])->whereNumber('member')->name('team.invite');
    Route::post('/team/{member}/suspend', [Admin\TeamController::class, 'suspend'])->whereNumber('member')->name('team.suspend');
    Route::post('/team/{member}/reactivate', [Admin\TeamController::class, 'reactivate'])->whereNumber('member')->name('team.reactivate');

    Route::get('/orders/create', [Admin\ManualOrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [Admin\ManualOrderController::class, 'store'])->name('orders.store');

    Route::get('/students', [Admin\StudentController::class, 'index'])->name('students.index');
    Route::get('/students/{student}', [Admin\StudentController::class, 'show'])->whereNumber('student')->name('students.show');
    Route::post('/students/{student}/suspend', [Admin\StudentController::class, 'suspend'])->name('students.suspend');
    Route::post('/students/{student}/reactivate', [Admin\StudentController::class, 'reactivate'])->name('students.reactivate');
    Route::delete('/students/{student}', [Admin\StudentController::class, 'destroy'])->name('students.destroy');

    Route::get('/notifications', [Admin\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/retry', [Admin\NotificationController::class, 'retry'])->name('notifications.retry');

    Route::get('/enquiries', [Admin\EnquiryController::class, 'index'])->name('enquiries.index');
    Route::get('/enquiries/{enquiry}', [Admin\EnquiryController::class, 'show'])->name('enquiries.show');
    Route::post('/enquiries/{enquiry}/handled', [Admin\EnquiryController::class, 'markHandled'])->name('enquiries.handled');

    Route::middleware('throttle:downloads')->group(function () {
        Route::get('/documents/{reference}/quotation.pdf', [DocumentController::class, 'quotation'])->name('documents.quotation');
        Route::get('/documents/{reference}/receipts/{payment}.pdf', [DocumentController::class, 'receipt'])->whereNumber('payment')->name('documents.receipt');
        Route::get('/documents/{reference}/invoice.pdf', [DocumentController::class, 'invoice'])->name('documents.invoice');
    });

    Route::get('/messages', [Admin\MessagesController::class, 'index'])->name('messages');

    Route::get('/dues', [Admin\DuesController::class, 'index'])->name('dues');
    Route::get('/dues/export', [Admin\DuesController::class, 'export'])->name('dues.export');

    Route::get('/accounting', [Admin\AccountingController::class, 'index'])->name('accounting');
    Route::get('/accounting/export', [Admin\AccountingController::class, 'export'])->name('accounting.export');

    Route::get('/settings', [Admin\SettingsController::class, 'index'])->name('settings');
    Route::get('/audit-log', [Admin\SettingsController::class, 'audit'])->name('audit');
});

/*
|--------------------------------------------------------------------------
| Team portal (team members invited by an administrator)
|--------------------------------------------------------------------------
*/

Route::prefix('team')->name('team.')->middleware(['private', 'auth', 'active', 'verified', 'role:expert'])->group(function () {
    Route::get('/', [Team\PortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders/{reference}', [Team\PortalController::class, 'show'])->name('orders.show');
    Route::post('/orders/{reference}/materials', [Team\PortalController::class, 'uploadMaterial'])->name('orders.materials.store');
    Route::post('/orders/{reference}/messages', [Team\PortalController::class, 'postMessage'])->middleware('throttle:messages')->name('orders.messages.store');

    Route::middleware('throttle:downloads')->group(function () {
        Route::get('/orders/{reference}/files/{file}', [Team\PortalController::class, 'downloadBrief'])->whereNumber('file')->name('orders.files.download');
        Route::get('/orders/{reference}/materials/{deliverable}', [Team\PortalController::class, 'downloadMaterial'])->whereNumber('deliverable')->name('orders.materials.download');
        Route::get('/orders/{reference}/messages/{message}/attachment', [Team\PortalController::class, 'downloadAttachment'])->whereNumber('message')->name('orders.messages.attachment');
    });
});
