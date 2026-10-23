<?php

namespace Database\Seeders;

use App\Enums\SubmissionStatus;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Synthetic local data. Refuses to run in production.
 * Demo administrator: admin@example.test, with a random password printed once.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('DemoSeeder must not run in production.');
        }

        $password = Str::password(16, symbols: false);

        User::factory()->admin()->create([
            'name' => 'Demo Administrator',
            'email' => 'admin@example.test',
            'password' => $password,
        ]);

        $this->command?->warn("Demo admin: admin@example.test / {$password}");

        User::factory(6)->create()->each(function (User $student) {
            Submission::factory(rand(0, 3))->for($student, 'student')->create([
                'contact_email' => $student->email,
                'whatsapp_number' => $student->whatsapp_number,
                'status' => collect(SubmissionStatus::cases())->random(),
            ]);
        });

        User::factory(2)->unverified()->create();
    }
}
