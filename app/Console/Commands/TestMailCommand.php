<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Mail\Message;

class TestMailCommand extends Command
{
    protected $signature = 'mail:test {email?}';
    protected $description = 'Test the email configuration by sending a test email';

    public function handle()
    {
        $email = $this->argument('email') ?? config('mail.from.address');

        $this->info('Testing email configuration...');
        $this->info('Mail driver: ' . config('mail.default'));
        $this->info('Mail host: ' . config('mail.mailers.smtp.host'));
        $this->info('Mail port: ' . config('mail.mailers.smtp.port'));
        $this->info('Mail from: ' . config('mail.from.address'));
        $this->info('Mail from name: ' . config('mail.from.name'));

        try {
            Mail::raw('This is a test email from your Laravel application.', function (Message $message) use ($email) {
                $message->to($email)
                    ->subject('Test Email from Laravel')
                    ->line('This is a test email to verify your mail configuration is working correctly.')
                    ->line('If you received this email, your mail configuration is working properly!');
            });

            $this->info('Test email sent successfully to: ' . $email);
            Log::info('Test email sent successfully', [
                'to' => $email,
                'driver' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host')
            ]);
        } catch (\Exception $e) {
            $this->error('Failed to send test email: ' . $e->getMessage());
            Log::error('Failed to send test email', [
                'error' => $e->getMessage(),
                'to' => $email,
                'driver' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host')
            ]);
        }
    }
}
