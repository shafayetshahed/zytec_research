<?php

namespace Tests\Feature\Admin;

use App\Enums\NotificationStatus;
use App\Enums\SubmissionStatus;
use App\Mail\NewSubmissionReceived;
use App\Models\NotificationLog;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubmissionManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create(['name' => 'Admin One']);
    }

    public function test_dashboard_shows_real_counts(): void
    {
        User::factory(3)->create();
        User::factory()->unverified()->create();
        Submission::factory(2)->create();
        Submission::factory()->status(SubmissionStatus::UnderReview)->create();
        Submission::factory()->status(SubmissionStatus::Completed)->create();

        $response = $this->actingAs($this->admin())->get(route('admin.dashboard'))->assertOk();

        $metrics = $response->viewData('metrics');
        $this->assertSame(8, $metrics['students']); // 4 standalone + 4 submission owners
        $this->assertSame(7, $metrics['verified']);
        $this->assertSame(4, $metrics['submissions']);
        $this->assertSame(2, $metrics['new']);
        $this->assertSame(1, $metrics['under_review']);
        $this->assertSame(1, $metrics['finished']);
        $this->assertSame(0, $metrics['failed_notifications']);
    }

    public function test_search_filters_and_pagination(): void
    {
        $alice = User::factory()->create(['name' => 'Zebedee Quorn', 'email' => 'zebedee@example.test']);
        $target = Submission::factory()->for($alice, 'student')->create(['module_code' => 'MBIS406', 'assignment_name' => 'Xylophonic data report', 'country' => 'Australia', 'contact_email' => 'zebedee@example.test']);
        Submission::factory(25)->create(['country' => 'United Kingdom', 'module_code' => 'ABC100', 'assignment_name' => 'Generic essay']);
        $admin = $this->admin();

        foreach (['Zebedee', 'zebedee@example', 'MBIS406', 'Xylophonic', $target->reference] as $term) {
            $this->actingAs($admin)->get(route('admin.submissions.index', ['q' => $term]))
                ->assertOk()->assertSee($target->reference)->assertViewHas('submissions', fn ($p) => $p->total() === 1);
        }

        $this->actingAs($admin)->get(route('admin.submissions.index', ['country' => 'Australia']))
            ->assertViewHas('submissions', fn ($p) => $p->total() === 1);

        $this->actingAs($admin)->get(route('admin.submissions.index'))
            ->assertViewHas('submissions', fn ($p) => $p->total() === 26 && $p->count() === 20);

        $this->actingAs($admin)->get(route('admin.submissions.index', ['status' => 'not-a-status']))->assertSessionHasErrors('status');
    }

    public function test_search_wildcards_are_treated_literally(): void
    {
        Submission::factory(3)->create();

        $this->actingAs($this->admin())->get(route('admin.submissions.index', ['q' => '%']))
            ->assertViewHas('submissions', fn ($p) => $p->total() === 0);
    }

    public function test_detail_page_shows_metadata_and_contact(): void
    {
        $student = User::factory()->create(['name' => 'Bilal Khan', 'email' => 'bilal@example.test', 'whatsapp_number' => '+447700900555']);
        $submission = Submission::factory()->for($student, 'student')->create([
            'assignment_name' => 'Thermo report', 'module_code' => 'EGR9019', 'institution' => 'Example Uni',
            'contact_email' => 'bilal@example.test', 'whatsapp_number' => '+447700900555',
        ]);

        $this->actingAs($this->admin())->get(route('admin.submissions.show', $submission))
            ->assertOk()
            ->assertSee('Bilal Khan')->assertSee('bilal@example.test')->assertSee('+447700900555')
            ->assertSee('Thermo report')->assertSee('EGR9019')->assertSee('Example Uni');
    }

    public function test_status_change_is_recorded_with_admin_and_persists(): void
    {
        $submission = Submission::factory()->create();
        $admin = $this->admin();

        $this->actingAs($admin)->put(route('admin.submissions.status', $submission), ['status' => 'under_review', 'note' => 'Looking now'])
            ->assertSessionHas('status');

        $this->assertSame(SubmissionStatus::UnderReview, $submission->fresh()->status);
        $this->assertDatabaseHas('submission_status_changes', ['submission_id' => $submission->id, 'admin_id' => $admin->id, 'from_status' => 'new', 'to_status' => 'under_review']);
        $this->assertDatabaseHas('submission_notes', ['submission_id' => $submission->id, 'admin_id' => $admin->id, 'body' => 'Looking now']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'submission.status_changed', 'actor_id' => $admin->id]);

        $this->actingAs($admin)->get(route('admin.submissions.show', $submission))->assertSee('Under review')->assertSee('Looking now');
    }

    public function test_closing_sets_closed_at_and_reopening_clears_it(): void
    {
        $submission = Submission::factory()->create();
        $admin = $this->admin();

        $this->actingAs($admin)->put(route('admin.submissions.status', $submission), ['status' => 'closed']);
        $this->assertNotNull($submission->fresh()->closed_at);

        $this->actingAs($admin)->put(route('admin.submissions.status', $submission), ['status' => 'in_progress']);
        $this->assertNull($submission->fresh()->closed_at);
    }

    public function test_invalid_status_is_rejected(): void
    {
        $submission = Submission::factory()->create();

        $this->actingAs($this->admin())->put(route('admin.submissions.status', $submission), ['status' => 'paid'])->assertSessionHasErrors('status');
        $this->assertSame(SubmissionStatus::New, $submission->fresh()->status);
    }

    public function test_internal_notes_are_never_shown_to_students(): void
    {
        $submission = Submission::factory()->create();
        $this->actingAs($this->admin())->post(route('admin.submissions.notes', $submission), ['body' => 'Internal: quote 50 later']);

        $this->actingAs($submission->student)->get(route('student.submissions.show', $submission->reference))
            ->assertOk()->assertDontSee('Internal: quote 50 later');
        $this->actingAs($submission->student)->get(route('student.dashboard'))->assertDontSee('Internal: quote 50 later');
    }

    public function test_admin_can_download_any_brief(): void
    {
        Storage::fake('local');
        $submission = Submission::factory()->create();
        Storage::disk('local')->put('briefs/x.pdf', '%PDF-1.4');
        $file = new SubmissionFile;
        $file->forceFill(['submission_id' => $submission->id, 'is_brief' => true, 'original_name' => 'Brief.pdf', 'storage_path' => 'briefs/x.pdf', 'mime_type' => 'application/pdf', 'extension' => 'pdf', 'size' => 8, 'sha256' => str_repeat('b', 64)])->save();

        $response = $this->actingAs($this->admin())->get(route('admin.submissions.files.download', [$submission, $file]));

        $response->assertOk()->assertDownload('Brief.pdf');
        $this->assertDatabaseHas('audit_logs', ['action' => 'file.downloaded']);
    }

    public function test_file_must_belong_to_submission_in_url(): void
    {
        Storage::fake('local');
        $submission = Submission::factory()->create();
        $other = Submission::factory()->create();
        $file = new SubmissionFile;
        $file->forceFill(['submission_id' => $other->id, 'is_brief' => true, 'original_name' => 'B.pdf', 'storage_path' => 'briefs/y.pdf', 'mime_type' => 'application/pdf', 'extension' => 'pdf', 'size' => 8, 'sha256' => str_repeat('c', 64)])->save();

        $this->actingAs($this->admin())->get(route('admin.submissions.files.download', [$submission, $file]))->assertNotFound();
    }

    public function test_failed_notification_can_be_retried(): void
    {
        $submission = Submission::factory()->create();
        $log = new NotificationLog;
        $log->forceFill(['submission_id' => $submission->id, 'type' => NotificationLog::TYPE_NEW_SUBMISSION, 'recipient' => 'research@zytecs.com', 'status' => 'failed', 'attempts' => 3, 'error_summary' => 'Delivery failed (TransportException).'])->save();
        Mail::fake();

        $this->actingAs($this->admin())->post(route('admin.submissions.notifications.retry', [$submission, $log]))->assertSessionHas('status', 'Notification sent.');

        Mail::assertSent(NewSubmissionReceived::class);
        $log->refresh();
        $this->assertSame(NotificationStatus::Sent, $log->status);
        $this->assertSame(4, $log->attempts);
        $this->assertNull($log->error_summary);
    }

    public function test_retry_that_fails_again_reports_error(): void
    {
        $submission = Submission::factory()->create();
        $log = new NotificationLog;
        $log->forceFill(['submission_id' => $submission->id, 'type' => NotificationLog::TYPE_NEW_SUBMISSION, 'recipient' => 'research@zytecs.com', 'status' => 'failed', 'attempts' => 3])->save();
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('still down'));

        $this->actingAs($this->admin())->post(route('admin.notifications.retry', $log))->assertSessionHas('error');

        $this->assertSame(NotificationStatus::Failed, $log->fresh()->status);
    }

    public function test_dashboard_counts_failed_notifications(): void
    {
        $submission = Submission::factory()->create();
        $log = new NotificationLog;
        $log->forceFill(['submission_id' => $submission->id, 'type' => NotificationLog::TYPE_NEW_SUBMISSION, 'recipient' => 'research@zytecs.com', 'status' => 'failed'])->save();

        $this->actingAs($this->admin())->get(route('admin.dashboard'))->assertViewHas('metrics', fn ($m) => $m['failed_notifications'] === 1);
    }

    public function test_admin_pages_escape_student_input(): void
    {
        $student = User::factory()->create(['name' => '<script>alert("x")</script>']);
        $submission = Submission::factory()->for($student, 'student')->create(['description' => '<iframe src=//evil>']);

        $this->actingAs($this->admin())->get(route('admin.submissions.show', $submission))
            ->assertDontSee('<script>alert("x")</script>', false)
            ->assertDontSee('<iframe src=//evil>', false);
    }
}
