<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use App\Models\TaskHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $password = 'password123';

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'password' => bcrypt($this->password)
        ]);
    }

    public function test_task_can_be_created()
    {
        $taskData = [
            'name' => 'Test Task',
            'description' => 'Test Description',
            'priority' => 'high',
            'status' => 'pending',
            'due_date' => now()->addDay(),
            'user_id' => $this->user->id
        ];

        $task = Task::create($taskData);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals($taskData['name'], $task->name);
        $this->assertEquals($taskData['description'], $task->description);
        $this->assertEquals($taskData['priority'], $task->priority);
        $this->assertEquals($taskData['status'], $task->status);
        $this->assertEquals($taskData['due_date']->format('Y-m-d'), $task->due_date->format('Y-m-d'));
        $this->assertEquals($taskData['user_id'], $task->user_id);
    }

    public function test_task_has_share_token_after_creation()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id
        ]);

        $this->assertNotNull($task->share_token);
        $this->assertIsString($task->share_token);
        $this->assertEquals(32, strlen($task->share_token));
    }

    public function test_task_history_is_created_for_all_field_changes()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id
        ]);

        $task->update([
            'name' => 'Updated Name',
            'description' => 'Updated Description',
            'priority' => 'high',
            'status' => 'in_progress'
        ]);

        $history = $task->histories()->get();

        $this->assertCount(4, $history); // Including the 'created' history
        $this->assertTrue($history->contains('field', 'created'));
        $this->assertTrue($history->contains('field', 'name'));
        $this->assertTrue($history->contains('field', 'description'));
        $this->assertTrue($history->contains('field', 'priority'));
        $this->assertTrue($history->contains('field', 'status'));

        // Verify the values
        $this->assertEquals('Updated Name', $task->getLatestHistoryForField('name')->new_value);
        $this->assertEquals('Updated Description', $task->getLatestHistoryForField('description')->new_value);
        $this->assertEquals('high', $task->getLatestHistoryForField('priority')->new_value);
        $this->assertEquals('in_progress', $task->getLatestHistoryForField('status')->new_value);
    }

    public function test_task_history_includes_user_information()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id
        ]);

        $task->update([
            'name' => 'Updated Name'
        ]);

        $history = TaskHistory::where('task_id', $task->id)->first();

        $this->assertEquals($this->user->id, $history->user_id);
        $this->assertEquals($this->user->name, $history->user_name);
    }

    public function test_task_history_is_ordered_by_most_recent_first()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id
        ]);

        $task->update(['name' => 'First Update']);
        $task->update(['name' => 'Second Update']);
        $task->update(['name' => 'Third Update']);

        $history = $task->getHistoryForField('name');

        $this->assertEquals('Third Update', $history[0]->new_value);
        $this->assertEquals('Second Update', $history[1]->new_value);
        $this->assertEquals('First Update', $history[2]->new_value);
        $this->assertTrue($history[0]->created_at->gt($history[1]->created_at));
        $this->assertTrue($history[1]->created_at->gt($history[2]->created_at));
    }

    public function test_task_can_generate_share_link()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id
        ]);

        $shareLink = $task->getShareLink();

        $this->assertIsString($shareLink);
        $this->assertStringContainsString($task->share_token, $shareLink);
        $this->assertStringContainsString(route('tasks.shared', ['token' => $task->share_token]), $shareLink);
    }

    public function test_task_is_publicly_available()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id
        ]);

        $task->makePubliclyAvailable();

        $this->assertTrue($task->isPubliclyAvailable());
        $this->assertNotNull($task->share_token);
        $this->assertTrue($task->share_token_expires_at->isFuture());
    }

    public function test_task_is_not_publicly_available_after_expiration()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id
        ]);

        $task->makePubliclyAvailable();
        $task->share_token_expires_at = now()->subDay();
        $task->save();

        $this->assertFalse($task->isPubliclyAvailable());
    }
}
