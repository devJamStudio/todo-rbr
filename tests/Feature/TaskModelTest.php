<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDueNotification;
use App\Notifications\TaskDueReminder;
use App\Notifications\TaskRevisitReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Carbon\Carbon;

class TaskModelTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();

        $this->user = User::factory()->create();
        $this->task = Task::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => now()->addDays(7),
            'status' => 'pending'
        ]);
    }

    public function test_task_creates_history_on_creation()
    {
        $this->assertDatabaseHas('task_histories', [
            'task_id' => $this->task->id,
            'field' => 'created',
            'new_value' => 'Task created'
        ]);
    }

    public function test_task_creates_history_on_update()
    {
        $this->task->update([
            'name' => 'Updated Name',
            'description' => 'Updated Description'
        ]);

        $this->assertDatabaseHas('task_histories', [
            'task_id' => $this->task->id,
            'field' => 'name',
            'new_value' => 'Updated Name'
        ]);

        $this->assertDatabaseHas('task_histories', [
            'task_id' => $this->task->id,
            'field' => 'description',
            'new_value' => 'Updated Description'
        ]);
    }

    public function test_task_history_retrieval()
    {
        $this->task->update(['name' => 'Updated Name']);
        $this->task->update(['name' => 'Final Name']);

        $history = $this->task->getHistoryForField('name');
        $this->assertCount(2, $history);
        $this->assertEquals('Final Name', $history[0]->new_value);
        $this->assertEquals('Updated Name', $history[1]->new_value);

        $latest = $this->task->getLatestHistoryForField('name');
        $this->assertEquals('Final Name', $latest->new_value);
    }

    public function test_share_token_generation()
    {
        $token = $this->task->generateShareToken();
        $this->assertNotNull($token);
        $this->assertNotNull($this->task->share_token_expires_at);
        $this->assertTrue($this->task->share_token_expires_at->isFuture());
    }

    public function test_share_token_validation()
    {
        $this->task->share_token = 'test-token';
        $this->task->share_token_expires_at = now()->addDay();
        $this->task->save();

        $this->assertTrue($this->task->isShareTokenValid());

        $this->task->share_token_expires_at = now()->subDay();
        $this->task->save();

        $this->assertFalse($this->task->isShareTokenValid());
    }

    public function test_share_link_generation()
    {
        $link = $this->task->getShareLink();
        $this->assertStringContainsString('tasks/shared/', $link);
        $this->assertNotNull($this->task->share_token);
    }

    public function test_public_availability()
    {
        $this->task->makePubliclyAvailable();
        $this->assertTrue($this->task->isPubliclyAvailable());

        $this->task->makePrivate();
        $this->assertFalse($this->task->isPubliclyAvailable());
    }

    public function test_due_notification_sending()
    {
        $this->task->sendDueNotification();
        Notification::assertSentTo(
            $this->user,
            TaskDueNotification::class
        );

        // Should not send notification for completed tasks
        $this->task->update(['status' => 'completed']);
        $this->task->sendDueNotification();
        Notification::assertNotSentTo(
            $this->user,
            TaskDueNotification::class
        );
    }

    public function test_due_reminder_sending()
    {
        $this->task->sendDueReminder();
        Notification::assertSentTo(
            $this->user,
            TaskDueReminder::class
        );

        // Should not send reminder for completed tasks
        $this->task->update(['status' => 'completed']);
        $this->task->sendDueReminder();
        Notification::assertNotSentTo(
            $this->user,
            TaskDueReminder::class
        );
    }

    public function test_revisit_reminder_sending()
    {
        $this->task->update(['last_reviewed_at' => now()->subDays(8)]);
        $this->task->sendRevisitReminder();
        Notification::assertSentTo(
            $this->user,
            TaskRevisitReminder::class
        );

        // Should not send reminder if recently reviewed
        $this->task->update(['last_reviewed_at' => now()->subDays(6)]);
        $this->task->sendRevisitReminder();
        Notification::assertNotSentTo(
            $this->user,
            TaskRevisitReminder::class
        );
    }
}
