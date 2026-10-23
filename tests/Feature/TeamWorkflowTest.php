<?php

namespace Tests\Feature;

use App\Enums\DeliverableStatus;
use App\Enums\UserRole;
use App\Mail\OrderUpdate;
use App\Mail\TeamInvitation;
use App\Models\Deliverable;
use App\Models\Message;
use App\Models\NotificationLog;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\MakesUploads;
use Tests\TestCase;

class TeamWorkflowTest extends TestCase
{
    use LazilyRefreshDatabase, MakesUploads;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    private function expert(array $attributes = []): User
    {
        return User::factory()->create(array_merge(['role' => UserRole::Expert, 'whatsapp_number' => null], $attributes));
    }

    private function order(?User $expert = null): Submission
    {
        $student = User::firstWhere('email', 'secret.student@example.test')
            ?? User::factory()->create(['name' => 'Secret Student', 'email' => 'secret.student@example.test', 'whatsapp_number' => '+447700900321']);

        return Submission::factory()->for($student, 'student')->create([
            'assignment_name' => 'Brand audit', 'institution' => 'Hidden University',
            'contact_email' => 'secret.student@example.test', 'whatsapp_number' => '+447700900321',
            'expert_id' => $expert?->id, 'assigned_at' => $expert ? now() : null,
        ]);
    }

    private function quote(Submission $submission, string $total = '100'): void
    {
        $this->actingAs($this->admin)->put(route('admin.submissions.quotation', $submission), ['currency' => 'BDT', 'total_amount' => $total, 'deposit_amount' => '0', 'action' => 'send']);
    }

    private function pay(Submission $submission, string $amount): void
    {
        $this->actingAs($this->admin)->post(route('admin.submissions.payments.store', $submission), ['type' => 'payment', 'amount' => $amount, 'paid_on' => now()->toDateString(), 'method' => 'bkash']);
    }

    private function uploadAsExpert(User $expert, Submission $order): Deliverable
    {
        $this->actingAs($expert)->post(route('team.orders.materials.store', $order->reference), [
            'material' => $this->docxUpload('process-notes.docx'), 'note' => 'Step-by-step plan', 'confirm_guidance' => '1',
        ])->assertSessionHasNoErrors();

        return Deliverable::latest('id')->firstOrFail();
    }

    public function test_admin_adds_team_member_who_sets_password_and_lands_in_team_portal(): void
    {
        Mail::fake();

        $this->actingAs($this->admin)->post(route('admin.team.store'), ['name' => 'Writer One', 'email' => 'Writer@Example.test', 'whatsapp_number' => '+8801711000000'])
            ->assertRedirect(route('admin.team.index'))->assertSessionHas('status');

        $member = User::where('email', 'writer@example.test')->sole();
        $this->assertTrue($member->isExpert());
        $this->assertNotNull($member->email_verified_at);

        $url = null;
        Mail::assertSent(TeamInvitation::class, function (TeamInvitation $mail) use (&$url) {
            $url = $mail->setPasswordUrl;

            return $mail->hasTo('writer@example.test');
        });
        $this->assertStringStartsWith(rtrim(config('app.url'), '/').'/reset-password/', $url);

        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        $token = basename((string) parse_url($url, PHP_URL_PATH));
        auth()->logout();
        $this->post(route('password.update'), ['token' => $token, 'email' => $query['email'], 'password' => 'writer-pass-123', 'password_confirmation' => 'writer-pass-123'])->assertRedirect(route('login'));
        $this->post(route('login'), ['email' => 'writer@example.test', 'password' => 'writer-pass-123'])->assertRedirect(route('team.dashboard'));
    }

    public function test_team_management_is_admin_only_and_suspension_signs_out(): void
    {
        $expert = $this->expert();

        $this->actingAs($expert)->get(route('admin.team.index'))->assertForbidden();
        $this->actingAs(User::factory()->create())->get(route('admin.team.index'))->assertForbidden();

        $this->actingAs($this->admin)->post(route('admin.team.suspend', $expert))->assertSessionHas('status');
        $this->actingAs($expert->fresh())->get(route('team.dashboard'))->assertRedirect(route('login'));

        $student = User::factory()->create();
        $this->actingAs($this->admin)->post(route('admin.team.suspend', $student))->assertNotFound();
    }

    public function test_assignment_notifies_team_member_and_only_active_members_can_be_chosen(): void
    {
        Mail::fake();
        $expert = $this->expert(['email' => 'expert@example.test']);
        $order = $this->order();

        $this->actingAs($this->admin)->put(route('admin.submissions.assign', $order), ['expert_id' => $expert->id, 'expert_instructions' => 'Focus on structure'])
            ->assertSessionHas('status', 'Assigned. The team member has been emailed.');

        $this->assertSame($expert->id, $order->fresh()->expert_id);
        Mail::assertSent(OrderUpdate::class, fn (OrderUpdate $mail) => $mail->hasTo('expert@example.test') && $mail->kind === NotificationLog::TYPE_EXPERT_ASSIGNED);

        $suspended = $this->expert(['status' => 'suspended']);
        $student = User::factory()->create();
        $this->actingAs($this->admin)->put(route('admin.submissions.assign', $order), ['expert_id' => $suspended->id])->assertSessionHasErrors('expert_id');
        $this->actingAs($this->admin)->put(route('admin.submissions.assign', $order), ['expert_id' => $student->id])->assertSessionHasErrors('expert_id');
    }

    public function test_team_member_sees_only_assigned_orders_without_student_details(): void
    {
        Storage::fake('local');
        $expert = $this->expert();
        $mine = $this->order($expert);
        $mine->expert_instructions = 'Explain the SWOT method';
        $mine->save();
        $notMine = $this->order($this->expert());

        $this->actingAs($expert)->get(route('team.dashboard'))->assertOk()->assertSee($mine->reference)->assertDontSee($notMine->reference);

        $this->actingAs($expert)->get(route('team.orders.show', $mine->reference))
            ->assertOk()
            ->assertSee('Brand audit')->assertSee('Explain the SWOT method')
            ->assertDontSee('Secret Student')->assertDontSee('secret.student@example.test')
            ->assertDontSee('+447700900321')->assertDontSee('Hidden University');

        $this->actingAs($expert)->get(route('team.orders.show', $notMine->reference))->assertNotFound();
        $this->actingAs($expert)->get(route('student.dashboard'))->assertForbidden();
        $this->actingAs($expert)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($expert)->get(route('admin.documents.quotation', $mine->reference))->assertForbidden();
    }

    public function test_team_member_can_download_brief_of_assigned_order_only(): void
    {
        Storage::fake('local');
        $expert = $this->expert();
        $order = $this->order($expert);
        Storage::disk('local')->put('briefs/b.pdf', '%PDF-1.4');
        $file = new SubmissionFile;
        $file->forceFill(['submission_id' => $order->id, 'is_brief' => true, 'original_name' => 'Brief.pdf', 'storage_path' => 'briefs/b.pdf', 'mime_type' => 'application/pdf', 'extension' => 'pdf', 'size' => 8, 'sha256' => str_repeat('a', 64)])->save();

        $this->actingAs($expert)->get(route('team.orders.files.download', [$order->reference, $file->id]))->assertOk()->assertDownload('Brief.pdf');
        $this->actingAs($this->expert())->get(route('team.orders.files.download', [$order->reference, $file->id]))->assertNotFound();
    }

    public function test_upload_needs_guidance_confirmation_and_safe_file_and_alerts_admin(): void
    {
        Storage::fake('local');
        Mail::fake();
        $expert = $this->expert();
        $order = $this->order($expert);

        $this->actingAs($expert)->post(route('team.orders.materials.store', $order->reference), ['material' => $this->docxUpload()])->assertSessionHasErrors('confirm_guidance');
        $this->actingAs($expert)->post(route('team.orders.materials.store', $order->reference), ['material' => UploadedFile::fake()->createWithContent('x.pdf', '<?php echo 1;'), 'confirm_guidance' => '1'])->assertSessionHasErrors('material');
        $this->assertSame(0, Deliverable::count());

        $deliverable = $this->uploadAsExpert($expert, $order);

        $this->assertSame(DeliverableStatus::PendingReview, $deliverable->status);
        Storage::disk('local')->assertExists($deliverable->storage_path);
        Mail::assertSent(OrderUpdate::class, fn (OrderUpdate $mail) => $mail->hasTo('research@zytecs.com') && $mail->kind === NotificationLog::TYPE_MATERIALS_UPLOADED);
        $this->actingAs($this->admin)->get(route('admin.submissions.show', $order))->assertSee('Awaiting review')->assertSee('process-notes.docx');
        $this->actingAs($this->admin)->get(route('admin.submissions.index', ['materials' => 'pending']))->assertSee($order->reference);
    }

    public function test_approved_materials_wait_for_full_payment_then_release_and_email_student(): void
    {
        Storage::fake('local');
        Mail::fake();
        $expert = $this->expert();
        $order = $this->order($expert);
        $this->quote($order, '1000');
        $deliverable = $this->uploadAsExpert($expert, $order);
        $student = $order->student;

        $this->actingAs($student)->get(route('student.submissions.show', $order->reference))->assertDontSee('process-notes.docx');
        $this->actingAs($student)->get(route('student.materials.download', [$order->reference, $deliverable->id]))->assertNotFound();

        $this->actingAs($this->admin)->post(route('admin.submissions.materials.approve', [$order, $deliverable]))->assertSessionHas('status');
        $this->assertNull($deliverable->fresh()->released_at);
        $this->actingAs($student)->get(route('student.submissions.show', $order->reference))->assertSee('Your materials are ready')->assertDontSee('process-notes.docx');

        $this->pay($order, '500');
        $this->assertNull($deliverable->fresh()->released_at);

        $this->pay($order, '500');
        $this->assertNotNull($deliverable->fresh()->released_at);
        Mail::assertSent(OrderUpdate::class, fn (OrderUpdate $mail) => $mail->hasTo('secret.student@example.test') && $mail->kind === NotificationLog::TYPE_MATERIALS_READY);

        $this->actingAs($student)->get(route('student.submissions.show', $order->reference))->assertSee('process-notes.docx')->assertDontSee($expert->name);
        $this->actingAs($student)->get(route('student.materials.download', [$order->reference, $deliverable->id]))->assertOk()->assertDownload('process-notes.docx');
        $this->actingAs(User::factory()->create())->get(route('student.materials.download', [$order->reference, $deliverable->id]))->assertNotFound();
    }

    public function test_early_release_and_orders_without_quotation(): void
    {
        Storage::fake('local');
        Mail::fake();
        $expert = $this->expert();
        $quoted = $this->order($expert);
        $this->quote($quoted);
        $early = $this->uploadAsExpert($expert, $quoted);

        $this->actingAs($this->admin)->post(route('admin.submissions.materials.approve', [$quoted, $early]), ['release_before_payment' => '1']);
        $this->assertNotNull($early->fresh()->released_at);

        $unquoted = $this->order($expert);
        $free = $this->uploadAsExpert($expert, $unquoted);
        $this->actingAs($this->admin)->post(route('admin.submissions.materials.approve', [$unquoted, $free]));
        $this->assertNotNull($free->fresh()->released_at);
    }

    public function test_sending_back_requires_a_note_which_the_team_member_sees(): void
    {
        Storage::fake('local');
        Mail::fake();
        $expert = $this->expert(['email' => 'expert@example.test']);
        $order = $this->order($expert);
        $deliverable = $this->uploadAsExpert($expert, $order);

        $this->actingAs($this->admin)->post(route('admin.submissions.materials.return', [$order, $deliverable]), [])->assertSessionHasErrors('review_note');
        $this->actingAs($this->admin)->post(route('admin.submissions.materials.return', [$order, $deliverable]), ['review_note' => 'Add more on referencing'])->assertSessionHas('status');

        $this->assertSame(DeliverableStatus::Returned, $deliverable->fresh()->status);
        $this->actingAs($expert)->get(route('team.orders.show', $order->reference))->assertSee('Add more on referencing')->assertSee('Sent back');
        Mail::assertSent(OrderUpdate::class, fn (OrderUpdate $mail) => $mail->hasTo('expert@example.test'));
    }

    public function test_material_routes_check_ownership(): void
    {
        Storage::fake('local');
        Mail::fake();
        $expert = $this->expert();
        $order = $this->order($expert);
        $other = $this->order($expert);
        $deliverable = $this->uploadAsExpert($expert, $order);

        $this->actingAs($this->admin)->post(route('admin.submissions.materials.approve', [$other, $deliverable]))->assertNotFound();
        $this->actingAs($this->admin)->get(route('admin.submissions.materials.download', [$other, $deliverable]))->assertNotFound();
        $this->actingAs($this->expert())->get(route('team.orders.materials.download', [$order->reference, $deliverable->id]))->assertNotFound();
        $this->actingAs($expert)->get(route('team.orders.materials.download', [$order->reference, $deliverable->id]))->assertOk();
    }

    public function test_admin_upload_is_approved_immediately(): void
    {
        Storage::fake('local');
        Mail::fake();
        $order = $this->order();

        $this->actingAs($this->admin)->post(route('admin.submissions.materials.store', $order), ['material' => $this->pdfUpload('feedback.pdf'), 'confirm_guidance' => '1'])->assertSessionHas('status');

        $deliverable = Deliverable::sole();
        $this->assertSame(DeliverableStatus::Approved, $deliverable->status);
        $this->assertNotNull($deliverable->released_at); // no quotation, so released at once
    }

    public function test_student_chat_with_alerts_throttling_and_anonymous_team_name(): void
    {
        Storage::fake('local');
        Mail::fake();
        $order = $this->order();
        $student = $order->student;

        $this->actingAs($student)->post(route('student.messages.store', $order->reference), ['body' => 'Can you explain section 2?'])->assertRedirect();
        $this->actingAs($student)->post(route('student.messages.store', $order->reference), ['body' => 'Also the references'])->assertRedirect();
        Mail::assertSent(OrderUpdate::class, 1);

        $this->actingAs($this->admin)->get(route('admin.messages'))->assertSee($order->reference)->assertSee('2 unread');
        $this->actingAs($this->admin)->get(route('admin.submissions.show', $order))->assertSee('Can you explain section 2?');
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertViewHas('metrics', fn ($m) => $m['unread_messages'] === 0);

        $this->actingAs($this->admin)->post(route('admin.submissions.messages.store', [$order, 'student']), ['body' => 'Sure, see the attached notes', 'attachment' => $this->pdfUpload('notes.pdf')]);
        Mail::assertSent(OrderUpdate::class, fn (OrderUpdate $mail) => $mail->hasTo('secret.student@example.test') && $mail->kind === NotificationLog::TYPE_MESSAGE_TO_STUDENT);

        $this->actingAs($student)->get(route('student.dashboard'))->assertSee('1 new message');
        $page = $this->actingAs($student)->get(route('student.submissions.show', $order->reference));
        $page->assertSee('Sure, see the attached notes')->assertSee('Zytecs Research')->assertDontSee($this->admin->name);
        $this->actingAs($student)->get(route('student.dashboard'))->assertDontSee('new message');

        $attachment = Message::whereNotNull('attachment_path')->sole();
        $this->actingAs($student)->get(route('student.messages.attachment', [$order->reference, $attachment->id]))->assertOk()->assertDownload('notes.pdf');
        $this->actingAs(User::factory()->create())->get(route('student.messages.attachment', [$order->reference, $attachment->id]))->assertNotFound();
    }

    public function test_threads_are_separated_between_student_and_team(): void
    {
        Storage::fake('local');
        Mail::fake();
        $expert = $this->expert();
        $order = $this->order($expert);

        $this->actingAs($order->student)->post(route('student.messages.store', $order->reference), ['body' => 'STUDENT-ONLY question', 'attachment' => $this->pdfUpload('draft.pdf')]);
        $this->actingAs($expert)->post(route('team.orders.messages.store', $order->reference), ['body' => 'TEAM-ONLY note']);

        $this->actingAs($expert)->get(route('team.orders.show', $order->reference))->assertSee('TEAM-ONLY note')->assertDontSee('STUDENT-ONLY question');
        $this->actingAs($order->student)->get(route('student.submissions.show', $order->reference))->assertSee('STUDENT-ONLY question')->assertDontSee('TEAM-ONLY note');
        $this->actingAs($this->admin)->get(route('admin.submissions.show', $order))->assertSee('STUDENT-ONLY question')->assertSee('TEAM-ONLY note');

        $studentAttachment = Message::whereNotNull('attachment_path')->sole();
        $this->actingAs($expert)->get(route('team.orders.messages.attachment', [$order->reference, $studentAttachment->id]))->assertNotFound();
    }

    public function test_message_validation_and_escaping(): void
    {
        $order = $this->order();

        $this->actingAs($order->student)->post(route('student.messages.store', $order->reference), [])->assertSessionHasErrors(['body' => 'Write a message or attach a file.']);
        $this->actingAs($order->student)->post(route('student.messages.store', $order->reference), ['body' => '<script>alert(1)</script>']);

        $this->actingAs($order->student)->get(route('student.submissions.show', $order->reference))->assertDontSee('<script>alert(1)</script>', false);
        $this->actingAs(User::factory()->create())->post(route('student.messages.store', $order->reference), ['body' => 'hi'])->assertNotFound();
        $this->actingAs($this->admin)->post(route('admin.submissions.messages.store', [$order, 'team']), ['body' => 'hi'])->assertStatus(422);
    }

    public function test_deleting_a_student_removes_materials_and_attachments(): void
    {
        Storage::fake('local');
        Mail::fake();
        $expert = $this->expert();
        $order = $this->order($expert);
        $deliverable = $this->uploadAsExpert($expert, $order);
        $this->actingAs($order->student)->post(route('student.messages.store', $order->reference), ['body' => 'draft', 'attachment' => $this->pdfUpload('draft.pdf')]);
        $attachmentPath = Message::sole()->attachment_path;

        $this->actingAs($this->admin)->delete(route('admin.students.destroy', $order->student), ['confirm_email' => 'secret.student@example.test'])->assertRedirect();

        Storage::disk('local')->assertMissing($deliverable->storage_path);
        Storage::disk('local')->assertMissing($attachmentPath);
        $this->assertSame(0, Deliverable::count() + Message::count());
    }
}
