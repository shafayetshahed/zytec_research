<?php

namespace Tests\Feature;

use App\Mail\EmailVerificationCode;
use App\Mail\NewEnquiryReceived;
use App\Models\Enquiry;
use App\Models\Submission;
use App\Models\User;
use App\Support\SafeFilename;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Concerns\MakesUploads;
use Tests\TestCase;

/**
 * Regression tests for findings from the independent security review (see docs/SECURITY.md).
 */
class SecurityRegressionTest extends TestCase
{
    use LazilyRefreshDatabase, MakesUploads;

    public function test_reset_link_ignores_spoofed_host_header(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'victim@example.test']);

        $this->withHeader('Host', 'evil.example')->post('http://evil.example/forgot-password', ['email' => 'victim@example.test']);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $url = $notification->toMail($user)->actionUrl;

            return str_starts_with($url, rtrim(config('app.url'), '/').'/reset-password/') && ! str_contains($url, 'evil.example');
        });
    }

    public function test_untrusted_host_is_rejected_in_production(): void
    {
        $this->app['env'] = 'production';
        config(['app.url' => 'https://research.zytecs.com']);

        try {
            $this->get('http://evil.example/')->assertStatus(400);
            $this->get('https://research.zytecs.com/')->assertOk();
        } finally {
            // Trusted hosts are static on the Symfony request; reset so other tests are unaffected.
            Request::setTrustedHosts([]);
        }
    }

    public function test_password_change_rotates_remember_token(): void
    {
        $user = User::factory()->create();
        $before = $user->remember_token;

        $this->actingAs($user)->put(route('student.profile.password'), [
            'current_password' => 'password-123', 'password' => 'another-pass-77', 'password_confirmation' => 'another-pass-77',
        ]);

        $this->assertNotSame($before, $user->fresh()->remember_token);
    }

    public function test_login_has_account_wide_limit_across_ips(): void
    {
        User::factory()->create(['email' => 'target@example.test']);

        for ($i = 0; $i < 30; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => "10.0.{$i}.1"])
                ->post(route('login'), ['email' => 'target@example.test', 'password' => 'wrong-password']);
        }

        $this->withServerVariables(['REMOTE_ADDR' => '10.9.9.9'])
            ->post(route('login'), ['email' => ' TARGET@example.test ', 'password' => 'password-123'])
            ->assertStatus(429);
    }

    public function test_markdown_links_cannot_be_injected_into_emails(): void
    {
        $enquiry = Enquiry::create(['name' => '[Admin login](https://evil.example)', 'email' => 'x@example.test', 'enquiry_type' => 'general', 'message' => 'Click [here](https://evil.example/phish) now']);

        $html = (new NewEnquiryReceived($enquiry))->render();
        $this->assertStringNotContainsString('href="https://evil.example', $html);

        $otpHtml = (new EmailVerificationCode('[Claim refund](https://evil.example)', '123456', 10))->render();
        $this->assertStringNotContainsString('href="https://evil.example', $otpHtml);
        $this->assertStringContainsString('Claim refund', $otpHtml);
    }

    public function test_macro_enabled_document_renamed_to_docx_is_rejected(): void
    {
        Storage::fake('local');
        $path = tempnam(sys_get_temp_dir(), 'docm');
        $zip = new \ZipArchive;
        $zip->open($path, \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Override PartName="/word/document.xml" ContentType="application/vnd.ms-word.document.macroEnabled.main+xml"/></Types>');
        $zip->addFromString('word/document.xml', '<w:document/>');
        $zip->addFromString('word/vbaProject.bin', 'VBA');
        $zip->close();

        $this->actingAs(User::factory()->create())->post(route('student.submissions.store'), [
            'assignment_name' => 'Report', 'module_code' => 'ABC1', 'brief' => UploadedFile::fake()->createWithContent('brief.docx', (string) file_get_contents($path)),
            'whatsapp_number' => '+447700900123', 'confirm_whatsapp' => '1', 'terms' => '1', 'client_token' => (string) Str::uuid(),
        ])->assertSessionHasErrors('brief');

        $this->assertSame(0, Submission::count());
    }

    public function test_bidi_override_characters_are_stripped_from_filenames(): void
    {
        $this->assertSame('reportxcod.pdf', SafeFilename::from("report\u{202E}xcod.pdf", 'pdf'));
        $this->assertSame('ab.pdf', SafeFilename::from("a\u{200B}b.pdf", 'pdf'));
    }

    public function test_delete_confirmation_requires_exact_email(): void
    {
        $student = User::factory()->create(['email' => 'one@example.test']);

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.students.destroy', $student), ['confirm_email' => 'one@example'])
            ->assertSessionHasErrors('confirm_email');

        $this->assertModelExists($student);
    }
}
