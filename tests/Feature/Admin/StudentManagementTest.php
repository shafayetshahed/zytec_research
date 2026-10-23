<?php

namespace Tests\Feature\Admin;

use App\Enums\AccountStatus;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_students_can_be_searched_and_filtered(): void
    {
        User::factory()->create(['name' => 'Rafi Ahmed', 'email' => 'rafi@example.test']);
        User::factory()->unverified()->create(['name' => 'Unverified Person']);
        User::factory(3)->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.students.index', ['q' => 'rafi']))
            ->assertOk()->assertSee('Rafi Ahmed')->assertViewHas('students', fn ($p) => $p->total() === 1);

        $this->actingAs($admin)->get(route('admin.students.index', ['filter' => 'unverified']))
            ->assertViewHas('students', fn ($p) => $p->total() === 1);

        $this->actingAs($admin)->get(route('admin.students.index'))
            ->assertViewHas('students', fn ($p) => $p->total() === 5)
            ->assertDontSee('password');
    }

    public function test_student_profile_never_shows_secrets(): void
    {
        $student = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.students.show', $student))
            ->assertOk()
            ->assertSee($student->email)
            ->assertDontSee($student->password)
            ->assertDontSee((string) $student->remember_token);
    }

    public function test_admin_profile_cannot_be_opened_as_student(): void
    {
        $otherAdmin = User::factory()->admin()->create();

        $this->actingAs(User::factory()->admin()->create())->get(route('admin.students.show', $otherAdmin))->assertNotFound();
    }

    public function test_suspend_and_reactivate(): void
    {
        $student = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.students.suspend', $student))->assertSessionHas('status');
        $this->assertSame(AccountStatus::Suspended, $student->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'student.suspended', 'actor_id' => $admin->id]);

        $this->actingAs($admin)->post(route('admin.students.reactivate', $student));
        $this->assertSame(AccountStatus::Active, $student->fresh()->status);
    }

    public function test_admin_cannot_suspend_another_admin(): void
    {
        $otherAdmin = User::factory()->admin()->create();

        $this->actingAs(User::factory()->admin()->create())->post(route('admin.students.suspend', $otherAdmin))->assertNotFound();
        $this->assertTrue($otherAdmin->fresh()->isActive());
    }

    public function test_deletion_requires_typed_confirmation_and_removes_everything(): void
    {
        Storage::fake('local');
        $student = User::factory()->create(['email' => 'gone@example.test']);
        $submission = Submission::factory()->for($student, 'student')->create();
        Storage::disk('local')->put('briefs/gone.pdf', '%PDF-1.4');
        $file = new SubmissionFile;
        $file->forceFill(['submission_id' => $submission->id, 'is_brief' => true, 'original_name' => 'g.pdf', 'storage_path' => 'briefs/gone.pdf', 'mime_type' => 'application/pdf', 'extension' => 'pdf', 'size' => 8, 'sha256' => str_repeat('d', 64)])->save();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->delete(route('admin.students.destroy', $student), ['confirm_email' => 'wrong@example.test'])
            ->assertSessionHasErrors('confirm_email');
        $this->assertModelExists($student);

        $this->actingAs($admin)->delete(route('admin.students.destroy', $student), ['confirm_email' => 'gone@example.test'])
            ->assertRedirect(route('admin.students.index'));

        $this->assertModelMissing($student);
        $this->assertModelMissing($submission);
        $this->assertSame(0, SubmissionFile::count());
        Storage::disk('local')->assertMissing('briefs/gone.pdf');
        $this->assertDatabaseHas('audit_logs', ['action' => 'student.deleted', 'actor_id' => $admin->id]);
    }
}
