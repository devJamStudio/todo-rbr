<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TaskDueNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckDueTasks extends Command
{
    protected $signature = 'tasks:check-due';
    protected $description = 'Check for tasks due tomorrow and send notifications';

    public function handle()
    {
        $tomorrow = now()->addDay()->startOfDay();
        $endOfTomorrow = $tomorrow->copy()->endOfDay();

        Log::info('Checking tasks due between', [
            'start' => $tomorrow->toDateTimeString(),
            'end' => $endOfTomorrow->toDateTimeString()
        ]);

        $tasks = Task::whereBetween('due_date', [$tomorrow, $endOfTomorrow])
                    ->where('status', '!=', 'completed')
                    ->with('user')
                    ->get();

        Log::info('Found tasks due tomorrow', [
            'count' => $tasks->count(),
            'tasks' => $tasks->pluck('id')->toArray()
        ]);

        foreach ($tasks as $task) {
            try {
                $task->user->notify(new TaskDueNotification($task));
                Log::info('Sent notification for task', [
                    'task_id' => $task->id,
                    'user_id' => $task->user_id
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send notification', [
                    'task_id' => $task->id,
                    'user_id' => $task->user_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info("Sent notifications for {$tasks->count()} tasks due tomorrow.");
    }
}
