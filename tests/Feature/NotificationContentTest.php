<?php

namespace Tests\Feature;

use App\Mail\NewEnquiryReceived;
use App\Mail\NewSubmissionReceived;
use App\Models\Enquiry;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NotificationContentTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function submission(int $size = 2048): Submission
    {
        $student = User::factory()->create(['name' => 'Nadia Islam', 'whatsapp_number' => '+447700900321']);
        $submission = Submission::factory()->for($student, 'student')->create([
            'assignment_name' => 'Literature review', 'module_code' => 'MGMT403', 'institution' => 'Example College',
            'country' => 'Australia', 'subject' => 'Marketing and consumer behaviour', 'assessment_type' => 'Literature review',
            'deadline' => now()->addDays(10), 'description' => 'Need help structuring themes.',
            'contact_email' => 'nadia@example.test', 'whatsapp_number' => '+447700900321',
        ]);
        $path = 'briefs/n'.$size.'.pdf';
        Storage::disk('local')->put($path, str_repeat('x', $size));
        $file = new SubmissionFile;
        $file->forceFill(['submission_id' => $submission->id, 'is_brief' => true, 'original_name' => 'Brief LR.pdf', 'storage_path' => $path, 'mime_type' => 'application/pdf', 'extension' => 'pdf', 'size' => $size, 'sha256' => str_repeat('e', 64)])->save();

        return $submission->fresh();
    }

    public function test_new_submission_email_contains_required_details_and_secure_link(): void
    {
        Storage::fake('local');
        $submission = $this->submission();
        $mail = new NewSubmissionReceived($submission);

        $mail->assertHasSubject('New brief '.$submission->reference.' — MGMT403: Literature review');
        $mail->assertHasReplyTo('nadia@example.test');
        foreach ([$submission->reference, 'Nadia Islam', 'nadia@example.test', '+447700900321', 'Literature review', 'MGMT403', 'Example College', 'Australia', 'Marketing and consumer behaviour', 'Need help structuring themes.', 'Brief LR.pdf', 'application/pdf', '2.0 KB', route('admin.submissions.show', $submission)] as $expected) {
            $mail->assertSeeInHtml($expected, false);
        }
        $this->assertSame([], $mail->attachments());
    }

    public function test_small_brief_is_attached_when_enabled_and_large_one_is_not(): void
    {
        Storage::fake('local');
        config(['zytecs.notifications.attach_max_kb' => 5]);

        $small = new NewSubmissionReceived($this->submission(2048));
        $this->assertCount(1, $small->attachments());

        $large = new NewSubmissionReceived($this->submission(10 * 1024));
        $this->assertSame([], $large->attachments());
        $large->assertSeeInHtml('Open the submission in the admin panel');
    }

    public function test_submission_email_escapes_student_content(): void
    {
        Storage::fake('local');
        $submission = $this->submission();
        $submission->description = '<script>alert(1)</script>';
        $submission->save();

        $html = (new NewSubmissionReceived($submission->fresh()))->render();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
    }

    public function test_enquiry_email_contents(): void
    {
        $enquiry = Enquiry::create(['name' => 'Sam', 'email' => 'sam@example.test', 'enquiry_type' => 'quotation', 'message' => 'Can you help with my dissertation?', 'country' => 'Canada']);

        $mail = new NewEnquiryReceived($enquiry);
        $mail->assertHasSubject('New website enquiry — Request a quotation');
        $mail->assertSeeInHtml('Can you help with my dissertation?');
        $mail->assertSeeInHtml('Canada');
        $mail->assertSeeInHtml(route('admin.enquiries.show', $enquiry), false);
    }
}
