<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/** Create or promote the first admin: php artisan admin:make you@example.com */
class MakeAdmin extends Command
{
    protected $signature = 'admin:make {email}';

    protected $description = 'Give admin access to a user (created if missing)';

    public function handle(): int
    {
        $email = mb_strtolower(trim($this->argument('email')));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email.');

            return self::FAILURE;
        }
        $user = User::firstOrCreate(['email' => $email], ['email_verified_at' => now()]);
        $user->forceFill(['is_admin' => true])->save();
        $this->info("{$email} is now an admin. Sign in with this email, then open /admin.");

        return self::SUCCESS;
    }
}
