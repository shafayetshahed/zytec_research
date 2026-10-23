<?php

namespace Database\Factories;

use App\Enums\SubmissionStatus;
use App\Models\Submission;
use App\Models\User;
use App\Support\SubmissionReference;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Submission>
 */
class SubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference' => fn () => SubmissionReference::generate(),
            'user_id' => User::factory(),
            'client_token' => fn () => (string) Str::uuid(),
            'assignment_name' => fake()->sentence(4),
            'module_code' => strtoupper(fake()->bothify('???###')),
            'contact_email' => fn (array $attributes) => User::find($attributes['user_id'])->email,
            'whatsapp_number' => fn (array $attributes) => User::find($attributes['user_id'])->whatsapp_number ?? '+447700900000',
            'status' => SubmissionStatus::New,
            'payment_status' => 'not_quoted',
            'source' => 'portal',
            'submitted_at' => now(),
        ];
    }

    public function status(SubmissionStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
            'closed_at' => $status->isFinal() ? now() : null,
        ]);
    }
}
