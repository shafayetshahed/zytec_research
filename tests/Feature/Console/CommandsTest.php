<?php

namespace Tests\Feature\Console;

use App\Enums\SubmissionStatus;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CommandsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_create_admin_generates_verified_admin(): void
    {
        $this->artisan('zytecs:create-admin', ['email' => 'Owner@Example.test', '--name' => 'Owner', '--generate-password' => true])
            ->expectsOutputToContain('Administrator owner@example.test created.')
            ->assertSuccessful();

        $admin = User::where('email', 'owner@example.test')->sole();
        $this->assertTrue($admin->isAdmin());
        $this->assertNotNull($admin->email_verified_at);
    }

    public function test_create_admin_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'owner@example.test']);

        $this->artisan('zytecs:create-admin', ['email' => 'owner@example.test', '--generate-password' => true])->assertFailed();
    }

    public function test_create_admin_rejects_weak_prompted_password(): void
    {
        $this->artisan('zytecs:create-admin', ['email' => 'owner@example.test'])
            ->expectsQuestion('Password for the new administrator', 'short')
            ->assertFailed();

        $this->assertSame(0, User::count());
    }

    public function test_prune_removes_only_old_closed_submissions_and_their_files(): void
    {
        Storage::fake('local');
        $this->freezeTime();
        $old = Submission::factory()->status(SubmissionStatus::Closed)->create(['closed_at' => now()->subDays(400)]);
        $recent = Submission::factory()->status(SubmissionStatus::Completed)->create(['closed_at' => now()->subDays(10)]);
        $open = Submission::factory()->create();
        Storage::disk('local')->put('briefs/old.pdf', 'x');
        $file = new SubmissionFile;
        $file->forceFill(['submission_id' => $old->id, 'is_brief' => true, 'original_name' => 'o.pdf', 'storage_path' => 'briefs/old.pdf', 'mime_type' => 'application/pdf', 'extension' => 'pdf', 'size' => 1, 'sha256' => str_repeat('f', 64)])->save();

        $this->artisan('zytecs:prune-submissions', ['--dry-run' => true])->expectsOutputToContain($old->reference)->assertSuccessful();
        $this->assertModelExists($old);

        $this->artisan('zytecs:prune-submissions')->assertSuccessful();

        $this->assertModelMissing($old);
        $this->assertModelExists($recent);
        $this->assertModelExists($open);
        Storage::disk('local')->assertMissing('briefs/old.pdf');
    }

    public function test_demo_seeder_refuses_production(): void
    {
        $this->app['env'] = 'production';

        $this->expectException(\RuntimeException::class);
        (new DemoSeeder)->run();
    }
}
