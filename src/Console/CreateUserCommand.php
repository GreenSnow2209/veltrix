<?php

namespace Veltrix\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateUserCommand extends Command
{
    protected $signature = 'veltrix:create-user
                            {--user= : Username}
                            {--password= : Password}
                            {--name= : Name}';

    protected $aliases = ['admin:create-user'];

    protected $description = 'Create a new Veltrix administrator';

    public function handle(): int
    {
        $userModel = config('veltrix.database.users_model', config('admin.database.users_model'));
        $roleModel = config('veltrix.database.roles_model', config('admin.database.roles_model'));

        $username = $this->option('user') ?: $this->ask('Username');
        $name = $this->option('name') ?: $this->ask('Name', $username);
        $password = $this->option('password') ?: $this->secret('Password');

        if (!$password) {
            $password = Str::random(16);
            $this->comment("Generated random password: <info>{$password}</info>");
        }

        $validator = Validator::make([
            'username' => $username,
            'name'     => $name,
            'password' => $password,
        ], [
            'username' => 'required|min:3',
            'name'     => 'required',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return Command::FAILURE;
        }

        $user = $userModel::create([
            'username' => $username,
            'name'     => $name,
            'password' => Hash::make($password),
        ]);

        $adminRole = $roleModel::where('slug', 'administrator')->first();
        if ($adminRole) {
            $user->roles()->attach($adminRole->id);
        }

        $this->info("Administrator [{$username}] created successfully!");
        return Command::SUCCESS;
    }
}
