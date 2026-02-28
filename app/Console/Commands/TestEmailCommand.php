<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;

class TestEmailCommand extends Command
{
    protected $signature = 'email:test {email=rebbouhf1@gmail.com}';
    protected $description = 'Test email sending via SMTP';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("Sending test email to: $email");
        
        try {
            Mail::to($email)->send(new ResetPasswordMail('http://localhost/test-reset', 'Test User'));
            $this->info('✅ Email sent/queued successfully!');
            $this->info('Check your mailbox or see logs with: php artisan log:tail');
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
        }
    }
}
