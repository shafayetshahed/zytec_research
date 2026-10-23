<?php

namespace App\Models;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Services\EmailOtpService;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property string|null $whatsapp_number
 * @property string|null $country
 * @property int|null $created_by
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property UserRole $role
 * @property AccountStatus $status
 * @property Carbon|null $terms_accepted_at
 * @property Carbon|null $last_login_at
 * @property Carbon|null $deletion_requested_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['name', 'email', 'password', 'whatsapp_number', 'country'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'last_login_at' => 'datetime',
            'deletion_requested_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => AccountStatus::class,
        ];
    }

    /**
     * @return HasMany<Submission, $this>
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * @return HasMany<EmailOtp, $this>
     */
    public function emailOtps(): HasMany
    {
        return $this->hasMany(EmailOtp::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isStudent(): bool
    {
        return $this->role === UserRole::Student;
    }

    /**
     * True for client profiles an administrator created for off-site orders.
     */
    public function isManualClient(): bool
    {
        return $this->created_by !== null;
    }

    /**
     * Best available contact line for admin screens.
     */
    public function contactSummary(): string
    {
        return collect([$this->email, $this->whatsapp_number])->filter()->implode(' · ') ?: 'No contact details';
    }

    public function isActive(): bool
    {
        return $this->status === AccountStatus::Active;
    }

    /**
     * Email verification uses a one-time passcode rather than a signed link.
     */
    public function sendEmailVerificationNotification(): void
    {
        app(EmailOtpService::class)->issue($this);
    }

    /**
     * @param  Builder<User>  $query
     */
    public function scopeStudents(Builder $query): void
    {
        $query->where('role', UserRole::Student);
    }
}
