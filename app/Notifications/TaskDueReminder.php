<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskDueReminder extends Notification implements ShouldQueue
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
            ->subject('Reminder: Task Due Soon - ' . $this->task->name)
            ->line('This is a reminder that your task "' . $this->task->name . '" is due soon.')
            ->line('Due date: ' . $this->task->due_date->format('Y-m-d H:i'))
            ->action('View Task', route('tasks.show', $this->task))
            ->line('Thank you for using our application!');
    }
}
