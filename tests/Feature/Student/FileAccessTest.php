<?php

namespace Tests\Feature\Student;

use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function submissionWithFile(?User $owner = null): array
    {
        $owner ??= User::factory()->create();
        $submission = Submission::factory()->for($owner, 'student')->create();
        Storage::disk('local')->put('briefs/2026/10/'.$submission->reference.'.pdf', "%PDF-1.4\nsecret brief\n%%EOF");

        $file = new SubmissionFile;
        $file->submission_id = $submission->id;
        $file->is_brief = true;
        $file->original_name = 'Brief.pdf';
        $file->storage_path = 'briefs/2026/10/'.$submission->reference.'.pdf';
        $file->mime_type = 'application/pdf';
        $file->extension = 'pdf';
        $file->size = 30;
        $file->sha256 = str_repeat('a', 64);
        $file->save();

        return [$owner, $submission, $file];
    }

    public function test_owner_can_download_their_brief_with_safe_headers(): void
    {
        Storage::fake('local');
        [$owner, $submission, $file] = $this->submissionWithFile();

        $response = $this->actingAs($owner)->get(route('student.submissions.files.download', [$submission->reference, $file->id]));

        $response->assertOk();
        $this->assertSame("%PDF-1.4\nsecret brief\n%%EOF", $response->streamedContent());
        $this->assertStringContainsString('attachment', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('Brief.pdf', (string) $response->headers->get('Content-Disposition'));
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        $this->assertDatabaseHas('audit_logs', ['action' => 'file.downloaded', 'actor_id' => $owner->id]);
    }

    public function test_guest_cannot_download(): void
    {
        Storage::fake('local');
        [, $submission, $file] = $this->submissionWithFile();

        $this->get(route('student.submissions.files.download', [$submission->reference, $file->id]))->assertRedirect(route('login'));
    }

    public function test_other_student_cannot_view_or_download(): void
    {
        Storage::fake('local');
        [, $submission, $file] = $this->submissionWithFile();
        $intruder = User::factory()->create();

        $this->actingAs($intruder)->get(route('student.submissions.show', $submission->reference))->assertNotFound();
        $this->actingAs($intruder)->get(route('student.submissions.files.download', [$submission->reference, $file->id]))->assertNotFound();
    }

    public function test_file_id_from_another_submission_cannot_be_combined_with_own_reference(): void
    {
        Storage::fake('local');
        [$owner, $ownSubmission] = $this->submissionWithFile();
        [, , $otherFile] = $this->submissionWithFile();

        $this->actingAs($owner)->get(route('student.submissions.files.download', [$ownSubmission->reference, $otherFile->id]))->assertNotFound();
    }

    public function test_guessed_reference_returns_not_found(): void
    {
        $this->actingAs(User::factory()->create())->get(route('student.submissions.show', 'ZR-2610-AAAAAA'))->assertNotFound();
    }

    public function test_storage_is_not_publicly_served(): void
    {
        Storage::fake('local');
        [, , $file] = $this->submissionWithFile();

        $this->get('/storage/'.$file->storage_path)->assertNotFound();
        $this->get('/'.$file->storage_path)->assertNotFound();
        $this->assertFalse(config('filesystems.disks.local.serve'));
        $this->assertStringNotContainsString(public_path(), config('filesystems.disks.local.root'));
    }

    public function test_admin_cannot_use_student_routes(): void
    {
        Storage::fake('local');
        [, $submission, $file] = $this->submissionWithFile();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('student.submissions.files.download', [$submission->reference, $file->id]))
            ->assertForbidden();
    }
}
