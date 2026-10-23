<?php

namespace App\Console\Commands;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;

class CreateAdmin extends Command
{
    /**
     * @var string
     */
    protected $signature = 'zytecs:create-admin
        {email : Email address for the administrator}
        {--name=Administrator : Display name}
        {--generate-password : Generate a strong random password instead of prompting}';

    /**
     * @var string
     */
    protected $description = 'Create an administrator account (there is no public admin registration)';

    public function handle(): int
    {
        $email = mb_strtolower(trim((string) $this->argument('email')));
        $name = (string) $this->option('name');

        $validator = Validator::make(['email' => $email, 'name' => $name], [
            'email' => ['required', 'email:rfc', 'max:254', 'unique:users,email'],
            'name' => ['required', 'string', 'max:120'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        if ($this->option('generate-password') || ! $this->input->isInteractive()) {
            $plain = Str::password(20);
            $generated = true;
        } else {
            $plain = password('Password for the new administrator', required: true);
            $generated = false;

            $passwordCheck = Validator::make(['password' => $plain], ['password' => ['required', Password::min(12)->letters()->numbers()]]);

            if ($passwordCheck->fails()) {
                $this->error($passwordCheck->errors()->first('password'));

                return self::FAILURE;
            }
        }

        $admin = new User;
        $admin->fill(['name' => $name, 'email' => $email, 'password' => $plain]);
        $admin->role = UserRole::Admin;
        $admin->status = AccountStatus::Active;
        $admin->email_verified_at = now();
        $admin->terms_accepted_at = now();
        $admin->save();

        $this->info("Administrator {$email} created.");

        if ($generated) {
            $this->warn('Generated password (shown once — store it in a password manager; change it with "Forgot password" on the sign-in page):');
            $this->line($plain);
        }

        return self::SUCCESS;
    }
}
