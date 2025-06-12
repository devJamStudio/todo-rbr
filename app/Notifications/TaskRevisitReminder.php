<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskRevisitReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Task $task)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Revisit Task: ' . $this->task->name)
            ->line('It\'s been a while since you last reviewed your task "' . $this->task->name . '".')
            ->line('Last reviewed: ' . $this->task->last_reviewed_at->format('Y-m-d H:i'))
            ->action('View Task', route('tasks.show', $this->task))
            ->line('Thank you for using our application!');
    }
}
