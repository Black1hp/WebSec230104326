<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'user:create-admin {email} {password?}';
    protected $description = 'Create an admin user with the specified email';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password') ?? 'admin123';

        $user = User::where('email', $email)->first();
        
        if ($user) {
            $user->role = 'admin';
            $user->save();
            $this->info("Existing user {$email} has been set as admin successfully!");
        } else {
            User::create([
                'name' => 'Admin',
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin'
            ]);
            $this->info("Admin user {$email} has been created successfully!");
            $this->info("Password: {$password}");
        }
        
        return 0;
    }
}
