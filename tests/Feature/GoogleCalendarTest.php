<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Services\GoogleCalendarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;
use Google\Service\Calendar\Event as GoogleCalendarEvent;

class GoogleCalendarTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $googleCalendarService;
    private User $user;
    private string $password = 'password123';

    protected function setUp(): void
    {
        parent::setUp();
        $this->googleCalendarService = Mockery::mock(GoogleCalendarService::class);
        $this->app->instance(GoogleCalendarService::class, $this->googleCalendarService);
        $this->user = User::factory()->create([
            'password' => bcrypt($this->password)
        ]);
    }

    public function test_user_can_connect_google_calendar()
    {
        $response = $this->actingAs($this->user)
            ->get(route('google.calendar.connect'));

        $response->assertStatus(302);
        $this->assertStringContainsString('accounts.google.com', $response->headers->get('Location'));
    }

    public function test_user_can_disconnect_google_calendar()
    {
        $this->user->update([
            'google_calendar_token' => 'test-token',
            'google_calendar_refresh_token' => 'test-refresh-token'
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('google.calendar.disconnect'));

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('success', 'Successfully disconnected from Google Calendar');

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'google_calendar_token' => null,
            'google_calendar_refresh_token' => null
        ]);
    }

    public function test_task_can_be_added_to_google_calendar()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id
        ]);

        $this->user->update([
            'google_calendar_token' => 'test-token',
            'google_calendar_refresh_token' => 'test-refresh-token'
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('google.calendar.add', $task));

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('success', 'Task added to Google Calendar');
        $this->assertEquals('test-event-id', $task->fresh()->google_calendar_event_id);
    }

    public function test_task_can_be_removed_from_google_calendar()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'google_calendar_event_id' => 'test-event-id'
        ]);

        $this->user->update([
            'google_calendar_token' => 'test-token',
            'google_calendar_refresh_token' => 'test-refresh-token'
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('google.calendar.remove', $task));

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('success', 'Task removed from Google Calendar');
        $this->assertNull($task->fresh()->google_calendar_event_id);
    }

    public function test_google_calendar_operations_require_authentication()
    {
        $task = Task::factory()->create();

        // Test connect route
        $response = $this->get(route('google.calendar.connect'));
        $response->assertRedirect(route('login'));

        // Test disconnect route
        $response = $this->post(route('google.calendar.disconnect'));
        $response->assertRedirect(route('login'));

        // Test add route
        $response = $this->post(route('google.calendar.add', $task));
        $response->assertRedirect(route('login'));

        // Test remove route
        $response = $this->post(route('google.calendar.remove', $task));
        $response->assertRedirect(route('login'));
    }

    public function test_google_calendar_operations_require_valid_tokens()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('google.calendar.add', $task));

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('error', 'Failed to add task to Google Calendar');
    }

    public function test_task_can_be_synced_to_google_calendar()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id
        ]);

        $this->user->update([
            'google_calendar_token' => 'test-token',
            'google_calendar_refresh_token' => 'test-refresh-token'
        ]);

        $result = $task->syncToGoogleCalendar();

        $this->assertTrue($result);
        $this->assertEquals('test-event-id', $task->fresh()->google_calendar_event_id);
    }

    public function test_task_can_be_updated_in_google_calendar()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'google_calendar_event_id' => 'test-event-id'
        ]);

        $this->user->update([
            'google_calendar_token' => 'test-token',
            'google_calendar_refresh_token' => 'test-refresh-token'
        ]);

        $result = $task->updateGoogleCalendarEvent();

        $this->assertTrue($result);
        $this->assertEquals('test-event-id', $task->fresh()->google_calendar_event_id);
    }

    public function test_sync_fails_without_google_calendar_id()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id
        ]);

        $result = $task->syncToGoogleCalendar();

        $this->assertFalse($result);
        $this->assertNull($task->fresh()->google_calendar_event_id);
    }

    public function test_remove_fails_without_event_id()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id
        ]);

        $result = $task->removeFromGoogleCalendar();

        $this->assertFalse($result);
    }

    public function test_update_creates_new_event_if_none_exists()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id
        ]);

        $this->user->update([
            'google_calendar_token' => 'test-token',
            'google_calendar_refresh_token' => 'test-refresh-token'
        ]);

        $result = $task->updateGoogleCalendarEvent();

        $this->assertTrue($result);
        $this->assertEquals('test-event-id', $task->fresh()->google_calendar_event_id);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
