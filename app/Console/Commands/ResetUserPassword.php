<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetUserPassword extends Command
{
    protected $signature = 'user:reset-password {email} {--password=}';
    protected $description = 'Reset a user password by email';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->option('password') ?? $this->generateRandomPassword();

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '$email' not found!");
            return 1;
        }

        $user->password = $password;
        $user->save();

        $this->info("Password reset successfully for: {$user->name} ({$user->email})");
        $this->line("New password: $password");
        $this->warn("Please inform the user to change this password after login.");

        return 0;
    }

    private function generateRandomPassword($length = 12)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        return substr(str_shuffle(str_repeat($chars, ceil($length / strlen($chars)))), 0, $length);
    }
}
