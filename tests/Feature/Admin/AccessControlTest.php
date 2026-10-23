<?php

namespace Tests\Feature\Admin;

use App\Models\Enquiry;
use App\Models\NotificationLog;
use App\Models\Submission;
use App\Models\SubmissionNote;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * Every admin endpoint, built lazily because route parameters need records.
     *
     * @return list<array{0: string, 1: string}>
     */
    private function adminEndpoints(): array
    {
        $submission = Submission::factory()->create();
        $student = $submission->student;
        $log = new NotificationLog;
        $log->submission_id = $submission->id;
        $log->type = NotificationLog::TYPE_NEW_SUBMISSION;
        $log->recipient = 'research@zytecs.com';
        $log->status = 'failed';
        $log->save();
        $enquiry = Enquiry::create(['name' => 'A', 'email' => 'a@example.test', 'enquiry_type' => 'general', 'message' => 'Hello there team']);

        return [
            ['get', route('admin.dashboard')],
            ['get', route('admin.submissions.index')],
            ['get', route('admin.submissions.show', $submission)],
            ['put', route('admin.submissions.status', $submission)],
            ['post', route('admin.submissions.notes', $submission)],
            ['post', route('admin.submissions.notifications.retry', [$submission, $log])],
            ['get', route('admin.students.index')],
            ['get', route('admin.students.show', $student)],
            ['post', route('admin.students.suspend', $student)],
            ['post', route('admin.students.reactivate', $student)],
            ['delete', route('admin.students.destroy', $student)],
            ['get', route('admin.notifications.index')],
            ['post', route('admin.notifications.retry', $log)],
            ['get', route('admin.enquiries.index')],
            ['get', route('admin.enquiries.show', $enquiry)],
            ['post', route('admin.enquiries.handled', $enquiry)],
            ['put', route('admin.submissions.quotation', $submission)],
            ['post', route('admin.submissions.payments.store', $submission)],
            ['get', route('admin.accounting')],
            ['get', route('admin.accounting.export')],
            ['get', route('admin.settings')],
            ['get', route('admin.audit')],
        ];
    }

    public function test_guests_are_redirected_to_login_from_every_admin_route(): void
    {
        foreach ($this->adminEndpoints() as [$method, $url]) {
            $this->{$method}($url)->assertRedirect(route('login'));
        }
    }

    public function test_students_are_forbidden_from_every_admin_route(): void
    {
        $student = User::factory()->create();

        foreach ($this->adminEndpoints() as [$method, $url]) {
            $this->actingAs($student)->{$method}($url)->assertForbidden();
        }

        $this->assertSame(0, SubmissionNote::count());
        $this->assertTrue(User::whereKey(Submission::first()->user_id)->exists());
    }

    public function test_admins_can_reach_admin_pages(): void
    {
        $admin = User::factory()->admin()->create();

        foreach ($this->adminEndpoints() as [$method, $url]) {
            if ($method === 'get') {
                $this->actingAs($admin)->get($url)->assertOk();
            }
        }
    }

    public function test_suspended_admin_is_signed_out(): void
    {
        $admin = User::factory()->admin()->suspended()->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_there_is_no_public_admin_registration(): void
    {
        $this->get('/admin/register')->assertNotFound();
        $this->post('/admin/register', ['email' => 'x@example.test'])->assertNotFound();
        $this->assertFalse(app('router')->has('admin.register'));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function privatePages(): array
    {
        return [['/student/dashboard'], ['/admin'], ['/login'], ['/register']];
    }

    #[DataProvider('privatePages')]
    public function test_private_pages_are_not_indexable(string $path): void
    {
        $response = $this->get($path);

        $response->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }
}
