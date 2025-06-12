<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TaskDueReminder;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendTaskReminders extends Command
{
    protected $signature = 'tasks:send-reminders';
    protected $description = 'Send reminders for tasks due tomorrow';

    public function handle()
    {
        $tomorrow = Carbon::tomorrow()->startOfDay();

        $tasks = Task::whereDate('due_date', $tomorrow)
            ->where('status', '!=', 'completed')
            ->with('user')
            ->get();

        foreach ($tasks as $task) {
            $task->user->notify(new TaskDueReminder($task));
            $this->info("Reminder sent for task: {$task->name}");
        }

        $this->info("Task reminders sent successfully! ({$tasks->count()} tasks)");
    }
}
