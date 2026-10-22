<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    protected $signature = 'make:admin {--name= : The name of the administrator} {--email= : The email of the administrator} {--password= : The administrator password}';

    protected $description = 'Create a new administrator user with full privileges';

    public function handle(): int
    {
        $this->info('=== Create Admin User ===');

        $name = $this->option('name') ?: $this->ask('Enter administrator name');
        $email = $this->option('email') ?: $this->ask('Enter administrator email');
        $password = $this->option('password') ?: $this->secret('Enter administrator password (min 8 chars)');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->info("Admin account '{$user->email}' created successfully!");

        return self::SUCCESS;
    }
}
