<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/** The only supported way to create the first admin. No credentials are hard-coded anywhere. */
class CreateAdmin extends Command
{
    protected $signature = 'vyora:create-admin {email} {--name=Admin} {--role=admin : admin or staff}';
    protected $description = 'Create or promote an admin/staff user (password is prompted, never stored in the repo).';

    public function handle(): int
    {
        $role = $this->option('role');
        if (! in_array($role, ['admin', 'staff'], true)) {
            $this->error('Role must be admin or staff.');

            return self::FAILURE;
        }
        $user = User::where('email', $this->argument('email'))->first();
        if (! $user) {
            $password = $this->secret('Password (min 8, letters and numbers)');
            $v = validator(['p' => $password], ['p' => ['required', Password::min(8)->letters()->numbers()]]);
            if ($v->fails()) {
                $this->error($v->errors()->first());

                return self::FAILURE;
            }
            $user = User::create(['name' => $this->option('name'), 'email' => $this->argument('email'), 'password' => Hash::make($password)]);
            $user->forceFill(['email_verified_at' => now()])->save();
        }
        $user->forceFill(['role' => $role])->save();
        $this->info("{$user->email} is now {$role}.");

        return self::SUCCESS;
    }
}
