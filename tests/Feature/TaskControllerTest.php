<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDueNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker, WithoutMiddleware;

    private User $user;
    private string $password = 'password123';

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->user = User::factory()->create([
            'password' => bcrypt($this->password)
        ]);
    }

    public function test_user_can_view_tasks()
    {
        $tasks = Task::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('tasks.index'));

        $response->assertStatus(200);
        $response->assertViewIs('tasks.index');
        $response->assertViewHas('tasks');
        $response->assertSee($tasks[0]->name);
    }

    public function test_user_can_create_task()
    {
        $taskData = [
            'name' => 'Test Task',
            'description' => 'Test Description',
            'priority' => 'high',
            'status' => 'pending',
            'due_date' => now()->addDays(7)->format('Y-m-d')
        ];

        $response = $this->actingAs($this->user)
            ->post(route('tasks.store'), $taskData);

        $response->assertRedirect(route('tasks.index'));
        $this->assertDatabaseHas('tasks', [
            'name' => $taskData['name'],
            'user_id' => $this->user->id
        ]);
    }

    public function test_user_can_update_task()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id
        ]);

        $updateData = [
            'name' => 'Updated Task',
            'description' => 'Updated Description',
            'priority' => 'medium',
            'status' => 'in_progress',
            'due_date' => now()->addDays(14)->format('Y-m-d')
        ];

        $response = $this->actingAs($this->user)
            ->put(route('tasks.update', $task), $updateData);

        $response->assertRedirect(route('tasks.index'));
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'name' => $updateData['name']
        ]);
    }

    public function test_user_can_delete_task()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('tasks.destroy', $task));

        $response->assertRedirect(route('tasks.index'));
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_user_can_view_task_history()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id
        ]);

        $task->update([
            'name' => 'Updated Name',
            'description' => 'Updated Description'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('tasks.history', $task));

        $response->assertStatus(200);
        $response->assertViewIs('tasks.history');
        $response->assertViewHas('histories');
    }

    public function test_user_can_share_task()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('tasks.share', $task));

        $response->assertStatus(200);
        $response->assertJsonStructure(['share_link']);

        $task->refresh();
        $this->assertNotNull($task->share_token);
        $this->assertNotNull($task->share_token_expires_at);
    }

    public function test_public_can_view_shared_task()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'share_token' => 'test-token',
            'share_token_expires_at' => now()->addDay()
        ]);

        $response = $this->get(route('tasks.shared', 'test-token'));

        $response->assertStatus(200);
        $response->assertViewIs('tasks.shared');
        $response->assertViewHas('task');
        $response->assertSee($task->name);
    }

    public function test_user_cannot_access_other_users_tasks()
    {
        $otherUser = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $otherUser->id
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('tasks.show', $task));

        $response->assertStatus(403);
    }

    public function test_task_filters_work_correctly()
    {
        $highPriorityTask = Task::factory()->create([
            'user_id' => $this->user->id,
            'priority' => 'high',
            'status' => 'pending'
        ]);
        $completedTask = Task::factory()->create([
            'user_id' => $this->user->id,
            'priority' => 'medium',
            'status' => 'completed'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('tasks.index', [
                'priority' => 'high',
                'status' => 'pending'
            ]));

        $response->assertStatus(200);
        $response->assertViewIs('tasks.index');
        $response->assertViewHas('tasks', function ($tasks) use ($highPriorityTask, $completedTask) {
            return $tasks->contains($highPriorityTask) && !$tasks->contains($completedTask);
        });
    }

    public function test_email_notification_is_sent_for_due_tasks()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => now()->addDay(),
            'status' => 'pending'
        ]);

        $this->artisan('tasks:notify-due');

        Notification::assertSentTo(
            $this->user,
            TaskDueNotification::class,
            function ($notification) use ($task) {
                return $notification->task->id === $task->id;
            }
        );
    }

    public function test_task_validation_rules_are_enforced()
    {
        $response = $this->actingAs($this->user)
            ->post(route('tasks.store'), [
                'name' => '',
                'priority' => 'invalid',
                'status' => 'invalid',
                'due_date' => 'invalid-date'
            ]);

        $response->assertSessionHasErrors(['name', 'priority', 'status', 'due_date']);
    }

    public function test_share_token_expiration()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'share_token' => 'test-token',
            'share_token_expires_at' => now()->subDay()
        ]);

        $response = $this->get(route('tasks.shared', 'test-token'));

        $response->assertStatus(404);
    }

    public function test_task_history_pagination()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id
        ]);

        // Create 15 history entries
        for ($i = 0; $i < 15; $i++) {
            $task->histories()->create([
                'user_id' => $this->user->id,
                'user_name' => $this->user->name,
                'field' => 'updated',
                'old_value' => null,
                'new_value' => 'Update ' . $i
            ]);
        }

        $response = $this->actingAs($this->user)
            ->get(route('tasks.history', $task));

        $response->assertStatus(200);
        $response->assertViewIs('tasks.history');
        $response->assertViewHas('histories');
        $response->assertViewHas('task');
    }

    public function test_task_filtering_by_status()
    {
        Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);

        Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('tasks.index', ['status' => 'pending']));

        $response->assertStatus(200);
        $response->assertViewHas('tasks');
        $response->assertSee('pending');
        $response->assertDontSee('completed');
    }

    public function test_task_filtering_by_priority()
    {
        Task::factory()->create([
            'user_id' => $this->user->id,
            'priority' => 'high'
        ]);

        Task::factory()->create([
            'user_id' => $this->user->id,
            'priority' => 'low'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('tasks.index', ['priority' => 'high']));

        $response->assertStatus(200);
        $response->assertViewHas('tasks');
        $response->assertSee('high');
        $response->assertDontSee('low');
    }

    public function test_task_sorting_by_due_date()
    {
        $task1 = Task::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => now()->addDays(2)
        ]);

        $task2 = Task::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => now()->addDay()
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('tasks.index', ['sort' => 'due_date', 'direction' => 'asc']));

        $response->assertStatus(200);
        $response->assertViewHas('tasks');
        $tasks = $response->viewData('tasks');
        $this->assertEquals($task2->id, $tasks->first()->id);
    }
}
