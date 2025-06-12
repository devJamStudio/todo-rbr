<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TaskDueNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class NotifyDueTasks extends Command
{
    protected $signature = 'tasks:notify-due';
    protected $description = 'Send notifications for tasks that are due soon';

    public function handle()
    {
        $tasks = Task::where('status', '!=', 'completed')
            ->where('due_date', '<=', now()->addDay())
            ->where('due_date', '>', now())
            ->with('user')
            ->get();

        foreach ($tasks as $task) {
            if ($task->user) {
                Notification::send($task->user, new TaskDueNotification($task));
            }
        }

        $this->info('Due task notifications sent successfully.');
    }
}
