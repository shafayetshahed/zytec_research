<?php

namespace Tests\Feature\Student;

use App\Actions\SubmitBrief;
use App\Enums\NotificationStatus;
use App\Enums\SubmissionStatus;
use App\Jobs\SendBusinessNotification;
use App\Mail\NewSubmissionReceived;
use App\Models\NotificationLog;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\MakesUploads;
use Tests\TestCase;

class SubmissionTest extends TestCase
{
    use LazilyRefreshDatabase, MakesUploads;

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'assignment_name' => 'Strategic analysis report',
            'module_code' => 'mgmt402',
            'brief' => $this->pdfUpload(),
            'whatsapp_number' => '+44 7700 900123',
            'confirm_whatsapp' => '1',
            'terms' => '1',
            'client_token' => (string) Str::uuid(),
        ], $overrides);
    }

    public function test_form_renders_with_registered_whatsapp(): void
    {
        $user = User::factory()->create(['whatsapp_number' => '+447700900999']);

        $this->actingAs($user)->get(route('student.submissions.create'))
            ->assertOk()
            ->assertSee('+447700900999')
            ->assertSee('name="client_token"', false);
    }

    public function test_valid_submission_is_stored_privately_and_confirmed(): void
    {
        Storage::fake('local');
        Mail::fake();
        $this->freezeTime();
        $user = User::factory()->create(['email' => 'student@example.test', 'whatsapp_number' => '+447700900123']);

        $response = $this->actingAs($user)->post(route('student.submissions.store'), $this->payload([
            'brief' => $this->pdfUpload('My Brief (final).pdf'),
            'institution' => 'Example University',
            'country' => 'United Kingdom',
            'subject' => 'Business and management',
            'assessment_type' => 'Report',
            'deadline' => now()->addWeek()->toDateString(),
            'description' => 'Help me understand the rubric.',
        ]));

        $submission = Submission::with('files')->sole();
        $response->assertRedirect(route('student.submissions.show', $submission->reference));

        $this->assertMatchesRegularExpression('/^ZR-\d{4}-[2-9A-HJ-NP-Z]{6}$/', $submission->reference);
        $this->assertSame($user->id, $submission->user_id);
        $this->assertSame('MGMT402', $submission->module_code);
        $this->assertSame('student@example.test', $submission->contact_email);
        $this->assertSame('+447700900123', $submission->whatsapp_number);
        $this->assertSame(SubmissionStatus::New, $submission->status);
        $this->assertSame(now()->startOfSecond()->toDateTimeString(), $submission->submitted_at->toDateTimeString());
        $this->assertSame('Example University', $submission->institution);

        $file = $submission->files->sole();
        $this->assertTrue($file->is_brief);
        $this->assertSame('My Brief (final).pdf', $file->original_name);
        $this->assertSame('application/pdf', $file->mime_type);
        $this->assertStringStartsWith('briefs/', $file->storage_path);
        $this->assertStringNotContainsString('My Brief', $file->storage_path);
        Storage::disk('local')->assertExists($file->storage_path);
        $this->assertSame(64, strlen($file->sha256));

        $this->assertDatabaseHas('submission_status_changes', ['submission_id' => $submission->id, 'from_status' => null, 'to_status' => 'new']);

        $this->actingAs($user)->get(route('student.submissions.show', $submission->reference))
            ->assertOk()
            ->assertSee('Your brief has been submitted.')
            ->assertSee($submission->reference)
            ->assertSee('Strategic analysis report')
            ->assertSee('MGMT402')
            ->assertSee('My Brief (final).pdf');
    }

    public function test_submission_notifies_business_after_saving(): void
    {
        Storage::fake('local');
        Mail::fake();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('student.submissions.store'), $this->payload());

        $submission = Submission::sole();
        Mail::assertSent(NewSubmissionReceived::class, fn (NewSubmissionReceived $mail) => $mail->hasTo('research@zytecs.com') && $mail->submission->is($submission));

        $log = NotificationLog::sole();
        $this->assertSame(NotificationStatus::Sent, $log->status);
        $this->assertSame(1, $log->attempts);
        $this->assertSame($submission->id, $log->submission_id);
    }

    public function test_notification_job_is_queued_after_commit(): void
    {
        Storage::fake('local');
        Bus::fake([SendBusinessNotification::class]);

        $this->actingAs(User::factory()->create())->post(route('student.submissions.store'), $this->payload());

        Bus::assertDispatched(SendBusinessNotification::class, fn (SendBusinessNotification $job) => $job->notificationLogId === NotificationLog::sole()->id);
    }

    public function test_supplementary_files_are_saved(): void
    {
        Storage::fake('local');
        Mail::fake();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('student.submissions.store'), $this->payload([
            'brief' => $this->docxUpload(),
            'supplementary' => [$this->pngUpload('rubric.png'), $this->pdfUpload('slides.pdf')],
        ]))->assertRedirect();

        $files = Submission::sole()->files;
        $this->assertCount(3, $files);
        $this->assertSame(1, $files->where('is_brief', true)->count());
        $this->assertSame('brief.docx', $files->firstWhere('is_brief', true)->original_name);
    }

    public function test_updated_whatsapp_number_is_saved_to_profile(): void
    {
        Storage::fake('local');
        Mail::fake();
        $user = User::factory()->create(['whatsapp_number' => '+447700900111']);

        $this->actingAs($user)->post(route('student.submissions.store'), $this->payload(['whatsapp_number' => '+61 412 345 678']));

        $this->assertSame('+61412345678', Submission::sole()->whatsapp_number);
        $this->assertSame('+61412345678', $user->fresh()->whatsapp_number);
    }

    public function test_required_fields(): void
    {
        Storage::fake('local');

        $this->actingAs(User::factory()->create())->post(route('student.submissions.store'), [])
            ->assertSessionHasErrors(['assignment_name', 'module_code', 'brief', 'whatsapp_number', 'confirm_whatsapp', 'terms', 'client_token']);

        $this->assertSame(0, Submission::count());
    }

    /**
     * @return array<string, array{0: \Closure(self): UploadedFile}>
     */
    public static function rejectedFiles(): array
    {
        return [
            'php script' => [fn (self $t) => UploadedFile::fake()->createWithContent('brief.php', '<?php echo 1;')],
            'php disguised as pdf' => [fn (self $t) => UploadedFile::fake()->createWithContent('brief.pdf', '<?php system($_GET["c"]); ?>')],
            'html disguised as docx' => [fn (self $t) => UploadedFile::fake()->createWithContent('brief.docx', '<html><script>alert(1)</script></html>')],
            'zip renamed to docx' => [fn (self $t) => $t->plainZipUpload('brief.docx')],
            'executable' => [fn (self $t) => UploadedFile::fake()->createWithContent('brief.exe', "MZ\x90\x00binary")],
            'svg image' => [fn (self $t) => UploadedFile::fake()->createWithContent('brief.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>')],
            'empty pdf' => [fn (self $t) => UploadedFile::fake()->createWithContent('brief.pdf', '')],
            'pdf named png' => [fn (self $t) => $t->pdfUpload('brief.png')],
            'double extension' => [fn (self $t) => UploadedFile::fake()->createWithContent('brief.pdf.php', "%PDF-1.4\n%%EOF")],
        ];
    }

    #[DataProvider('rejectedFiles')]
    public function test_unsafe_or_mismatched_files_are_rejected(\Closure $makeFile): void
    {
        Storage::fake('local');

        $this->actingAs(User::factory()->create())
            ->post(route('student.submissions.store'), $this->payload(['brief' => $makeFile($this)]))
            ->assertSessionHasErrors('brief');

        $this->assertSame(0, Submission::count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_oversized_file_is_rejected(): void
    {
        Storage::fake('local');
        config(['zytecs.uploads.max_kb' => 50]);

        $this->actingAs(User::factory()->create())
            ->post(route('student.submissions.store'), $this->payload(['brief' => $this->pdfUpload('big.pdf', 60)]))
            ->assertSessionHasErrors('brief');

        $this->assertSame(0, Submission::count());
    }

    public function test_too_many_supplementary_files_are_rejected(): void
    {
        Storage::fake('local');

        $this->actingAs(User::factory()->create())
            ->post(route('student.submissions.store'), $this->payload([
                'supplementary' => array_map(fn ($i) => $this->pdfUpload("s{$i}.pdf"), range(1, 5)),
            ]))
            ->assertSessionHasErrors('supplementary');
    }

    public function test_path_traversal_filename_is_neutralised(): void
    {
        Storage::fake('local');
        Mail::fake();

        $this->actingAs(User::factory()->create())
            ->post(route('student.submissions.store'), $this->payload(['brief' => $this->pdfUpload('../../../etc/passwd.pdf')]));

        $file = Submission::sole()->files->sole();
        $this->assertSame('passwd.pdf', $file->original_name);
        $this->assertStringNotContainsString('..', $file->storage_path);
    }

    public function test_module_code_rejects_markup(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('student.submissions.store'), $this->payload(['module_code' => '<script>']))
            ->assertSessionHasErrors('module_code');
    }

    public function test_past_deadline_is_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('student.submissions.store'), $this->payload(['deadline' => now()->subDay()->toDateString()]))
            ->assertSessionHasErrors(['deadline' => 'The deadline cannot be in the past.']);
    }

    public function test_optional_fields_are_restricted_to_known_values(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('student.submissions.store'), $this->payload(['country' => 'Atlantis', 'assessment_type' => 'Exam sitting']))
            ->assertSessionHasErrors(['country', 'assessment_type']);
    }

    public function test_retried_request_with_same_token_does_not_duplicate(): void
    {
        Storage::fake('local');
        Mail::fake();
        $user = User::factory()->create();
        $token = (string) Str::uuid();

        $first = $this->actingAs($user)->post(route('student.submissions.store'), $this->payload(['client_token' => $token]));
        $second = $this->actingAs($user)->post(route('student.submissions.store'), $this->payload(['client_token' => $token]));

        $this->assertSame(1, Submission::count());
        $this->assertSame($first->headers->get('Location'), $second->headers->get('Location'));
        $this->assertCount(1, Storage::disk('local')->allFiles());
        Mail::assertSentCount(1);
    }

    public function test_separate_submissions_get_unique_references(): void
    {
        Storage::fake('local');
        Mail::fake();
        $user = User::factory()->create();

        foreach (range(1, 5) as $i) {
            $this->actingAs($user)->post(route('student.submissions.store'), $this->payload());
        }

        $this->assertSame(5, Submission::distinct()->count('reference'));
    }

    public function test_database_failure_removes_stored_file_and_shows_no_success(): void
    {
        Storage::fake('local');
        Mail::fake();
        $user = User::factory()->create();

        Submission::creating(function () {
            throw new \RuntimeException('Simulated database failure');
        });

        $this->actingAs($user)->post(route('student.submissions.store'), $this->payload())
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(0, Submission::count());
        $this->assertSame(0, SubmissionFile::count());
        $this->assertSame([], Storage::disk('local')->allFiles());
        Mail::assertNothingSent();
    }

    public function test_failed_email_does_not_lose_the_submission(): void
    {
        Storage::fake('local');
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('Connection refused'));
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('student.submissions.store'), $this->payload())->assertRedirect();

        $this->assertSame(1, Submission::count());
        $log = NotificationLog::sole();
        $this->assertNotSame(NotificationStatus::Sent, $log->status);
        $this->assertSame('Delivery failed (RuntimeException); retrying automatically.', $log->error_summary);
        $this->assertStringNotContainsString('Connection refused', (string) $log->error_summary);
    }

    public function test_submissions_are_rate_limited(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 15; $i++) {
            $this->actingAs($user)->post(route('student.submissions.store'), []);
        }

        $this->actingAs($user)->post(route('student.submissions.store'), [])->assertStatus(429);
    }

    public function test_dashboard_lists_own_submissions_only(): void
    {
        $user = User::factory()->create();
        $mine = Submission::factory()->for($user, 'student')->create(['assignment_name' => 'My essay']);
        $theirs = Submission::factory()->create(['assignment_name' => 'Someone elses essay']);

        $this->actingAs($user)->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee($mine->reference)
            ->assertSee('My essay')
            ->assertDontSee($theirs->reference)
            ->assertDontSee('Someone elses essay');
    }

    public function test_dashboard_shows_empty_state(): void
    {
        $this->actingAs(User::factory()->create(['name' => 'New Student']))->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Welcome, New Student')
            ->assertSee('You have not submitted a brief yet.');
    }

    public function test_student_content_is_escaped(): void
    {
        $user = User::factory()->create(['name' => '<script>alert(1)</script>']);
        $submission = Submission::factory()->for($user, 'student')->create(['assignment_name' => '<img src=x onerror=alert(1)>', 'description' => '<b>bold</b>']);

        $this->actingAs($user)->get(route('student.submissions.show', $submission->reference))
            ->assertOk()
            ->assertDontSee('<img src=x onerror=alert(1)>', false)
            ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false);

        $this->actingAs($user)->get(route('student.dashboard'))->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_submit_brief_action_cleans_up_when_file_storage_fails(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $good = $this->pdfUpload('good.pdf');
        $bad = $this->pdfUpload('bad.pdf');
        unlink($bad->getRealPath());

        try {
            app(SubmitBrief::class)->handle($user, '+447700900123', [
                'assignment_name' => 'X', 'module_code' => 'X1', 'client_token' => (string) Str::uuid(),
            ], $good, [$bad]);
            $this->fail('Expected storage failure.');
        } catch (\Throwable) {
            // expected
        }

        $this->assertSame(0, Submission::count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }
}
