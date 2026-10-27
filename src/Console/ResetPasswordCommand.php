<?php

namespace Veltrix\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetPasswordCommand extends Command
{
    protected $signature = 'veltrix:reset-password
                            {username? : Administrator username}
                            {--password= : New password}';

    protected $aliases = ['admin:reset-password'];

    protected $description = 'Reset password for a Veltrix administrator';

    public function handle(): int
    {
        $userModel = config('veltrix.database.users_model', config('admin.database.users_model'));

        $username = $this->argument('username') ?: $this->ask('Administrator username');

        $user = $userModel::where('username', $username)->first();
        if (!$user) {
            $this->error("User [{$username}] does not exist!");
            return Command::FAILURE;
        }

        $password = $this->option('password') ?: $this->secret('Enter new password (or press enter for random)');
        if (!$password) {
            $password = Str::random(16);
            $this->comment("Generated password: <info>{$password}</info>");
        }

        $user->password = Hash::make($password);
        $user->save();

        $this->info("Password for administrator [{$username}] reset successfully!");
        return Command::SUCCESS;
    }
}
