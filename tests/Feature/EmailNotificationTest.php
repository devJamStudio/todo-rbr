<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDueNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailNotificationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->withoutMiddleware();
    }

    public function test_notification_is_sent_for_due_tasks()
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'name' => 'Test Task',
            'description' => 'Test Description',
            'priority' => 'high',
            'status' => 'pending',
            'due_date' => now()->addDay(),
        ]);

        $this->artisan('tasks:check-due');

        Notification::assertSentTo(
            $user,
            TaskDueNotification::class
        );
    }

    public function test_notification_is_not_sent_for_completed_tasks()
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'name' => 'Test Task',
            'description' => 'Test Description',
            'priority' => 'high',
            'status' => 'completed',
            'due_date' => now()->addDay(),
        ]);

        $this->artisan('tasks:check-due');

        Notification::assertNotSentTo(
            $user,
            TaskDueNotification::class
        );
    }

    public function test_notification_is_not_sent_for_far_future_tasks()
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'name' => 'Test Task',
            'description' => 'Test Description',
            'priority' => 'high',
            'status' => 'pending',
            'due_date' => now()->addDays(2),
        ]);

        $this->artisan('tasks:check-due');

        Notification::assertNotSentTo(
            $user,
            TaskDueNotification::class
        );
    }

    public function test_notification_contains_correct_task_information()
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'name' => 'Test Task',
            'description' => 'Test Description',
            'priority' => 'high',
            'status' => 'pending',
            'due_date' => now()->addDay(),
        ]);

        $this->artisan('tasks:check-due');

        Notification::assertSentTo(
            $user,
            function (TaskDueNotification $notification) use ($task) {
                return $notification->task->id === $task->id;
            }
        );
    }

    public function test_multiple_notifications_are_sent_for_multiple_tasks()
    {
        $user = User::factory()->create();

        $task1 = Task::create([
            'user_id' => $user->id,
            'name' => 'Task 1',
            'description' => 'Description 1',
            'priority' => 'high',
            'status' => 'pending',
            'due_date' => now()->addDay(),
        ]);

        $task2 = Task::create([
            'user_id' => $user->id,
            'name' => 'Task 2',
            'description' => 'Description 2',
            'priority' => 'medium',
            'status' => 'pending',
            'due_date' => now()->addDay(),
        ]);

        $this->artisan('tasks:check-due');

        Notification::assertSentTo(
            $user,
            TaskDueNotification::class,
            function ($notification) use ($task1) {
                return $notification->task->id === $task1->id;
            }
        );

        Notification::assertSentTo(
            $user,
            TaskDueNotification::class,
            function ($notification) use ($task2) {
                return $notification->task->id === $task2->id;
            }
        );
    }

    public function test_notification_is_sent_to_correct_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $task1 = Task::create([
            'user_id' => $user1->id,
            'name' => 'User 1 Task',
            'description' => 'Description 1',
            'priority' => 'high',
            'status' => 'pending',
            'due_date' => now()->addDay(),
        ]);

        $task2 = Task::create([
            'user_id' => $user2->id,
            'name' => 'User 2 Task',
            'description' => 'Description 2',
            'priority' => 'high',
            'status' => 'pending',
            'due_date' => now()->addDay(),
        ]);

        $this->artisan('tasks:check-due');

        Notification::assertSentTo(
            $user1,
            TaskDueNotification::class,
            function ($notification) use ($task1) {
                return $notification->task->id === $task1->id;
            }
        );

        Notification::assertNotSentTo(
            $user1,
            TaskDueNotification::class,
            function ($notification) use ($task2) {
                return $notification->task->id === $task2->id;
            }
        );

        Notification::assertSentTo(
            $user2,
            TaskDueNotification::class,
            function ($notification) use ($task2) {
                return $notification->task->id === $task2->id;
            }
        );

        Notification::assertNotSentTo(
            $user2,
            TaskDueNotification::class,
            function ($notification) use ($task1) {
                return $notification->task->id === $task1->id;
            }
        );
    }
}
