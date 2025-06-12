<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Services\GoogleCalendarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class GoogleCalendarIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private Task $task;
    private $googleCalendarService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'google_calendar_id' => 'test-calendar-id'
        ]);

        $this->task = Task::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => now()->addDays(7),
            'status' => 'pending'
        ]);

        // Mock Google Calendar Service
        $this->googleCalendarService = Mockery::mock(GoogleCalendarService::class);
        $this->app->instance(GoogleCalendarService::class, $this->googleCalendarService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_sync_to_google_calendar()
    {
        $event = new \stdClass();
        $event->id = 'test-event-id';

        $this->googleCalendarService
            ->shouldReceive('addEvent')
            ->once()
            ->with($this->task)
            ->andReturn($event);

        $result = $this->task->syncToGoogleCalendar();

        $this->assertTrue($result);
        $this->assertEquals('test-event-id', $this->task->google_calendar_event_id);
    }

    public function test_sync_fails_without_calendar_id()
    {
        $this->user->update(['google_calendar_id' => null]);

        $result = $this->task->syncToGoogleCalendar();

        $this->assertFalse($result);
        $this->assertNull($this->task->google_calendar_event_id);
    }

    public function test_remove_from_google_calendar()
    {
        $this->task->update(['google_calendar_event_id' => 'test-event-id']);

        $this->googleCalendarService
            ->shouldReceive('deleteEvent')
            ->once()
            ->with($this->task)
            ->andReturn(true);

        $result = $this->task->removeFromGoogleCalendar();

        $this->assertTrue($result);
        $this->assertNull($this->task->google_calendar_event_id);
    }

    public function test_remove_fails_without_event_id()
    {
        $result = $this->task->removeFromGoogleCalendar();

        $this->assertFalse($result);
    }

    public function test_update_google_calendar_event()
    {
        $this->task->update(['google_calendar_event_id' => 'test-event-id']);

        $this->googleCalendarService
            ->shouldReceive('updateEvent')
            ->once()
            ->with($this->task)
            ->andReturn(true);

        $result = $this->task->updateGoogleCalendarEvent();

        $this->assertTrue($result);
    }

    public function test_update_fails_without_event_id()
    {
        $result = $this->task->updateGoogleCalendarEvent();

        $this->assertFalse($result);
    }
}
