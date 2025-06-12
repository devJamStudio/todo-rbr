<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        Log::info('WelcomeNotification constructed', [
            'timestamp' => now()->toDateTimeString(),
            'memory_usage' => memory_get_usage(true)
        ]);
    }

    public function via($notifiable): array
    {
        Log::info('WelcomeNotification via method called', [
            'channels' => ['mail'],
            'notifiable' => $notifiable->email,
            'mail_config' => [
                'driver' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name')
            ],
            'queue_connection' => config('queue.default'),
            'queue_name' => config('queue.connections.database.queue')
        ]);
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        Log::info('WelcomeNotification toMail method called', [
            'notifiable' => $notifiable->email,
            'app_name' => config('app.name'),
            'mail_from' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name'),
            'timestamp' => now()->toDateTimeString()
        ]);

        try {
            $message = (new MailMessage)
                ->subject('Welcome to ' . config('app.name'))
                ->greeting('Welcome ' . $notifiable->name . '!')
                ->line('Thank you for registering with our application.')
                ->line('You can now start creating and managing your tasks.')
                ->action('Go to your tasks', route('tasks.index'))
                ->line('If you have any questions, feel free to contact our support team.');

            Log::info('Welcome email message created successfully', [
                'user_id' => $notifiable->id,
                'email' => $notifiable->email,
                'subject' => $message->subject,
                'timestamp' => now()->toDateTimeString()
            ]);

            return $message;
        } catch (\Exception $e) {
            Log::error('Failed to create welcome email message', [
                'error' => $e->getMessage(),
                'user_id' => $notifiable->id,
                'email' => $notifiable->email,
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()->toDateTimeString()
            ]);
            throw $e;
        }
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
