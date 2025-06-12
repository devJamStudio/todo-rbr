<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GoogleCalendarController extends Controller
{
    protected $calendarService;

    public function __construct(GoogleCalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    public function connect()
    {
        try {
            $service = app(GoogleCalendarService::class);
            return redirect($service->connect());
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('tasks.index')
                ->with('error', 'Google Calendar integration is not configured. Please contact the administrator.');
        } catch (\Exception $e) {
            Log::error('Failed to connect to Google Calendar', [
                'error' => $e->getMessage()
            ]);
            return redirect()->route('tasks.index')
                ->with('error', 'Failed to connect to Google Calendar. Please try again later.');
        }
    }

    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('tasks.index')
                ->with('error', 'Failed to connect to Google Calendar');
        }

        $code = $request->get('code');
        $client = $this->calendarService->getClient();
        $token = $client->fetchAccessTokenWithAuthCode($code);

        $user = Auth::user();
        $user->update([
            'google_calendar_token' => $token['access_token'],
            'google_calendar_refresh_token' => $token['refresh_token'] ?? null,
        ]);

        return redirect()->route('tasks.index')
            ->with('success', 'Successfully connected to Google Calendar');
    }

    public function disconnect()
    {
        $this->calendarService->disconnect(Auth::user());

        return redirect()->route('tasks.index')
            ->with('success', 'Successfully disconnected from Google Calendar');
    }

    public function addEvent(Task $task)
    {
        try {
            $this->calendarService->addEvent($task);

            return redirect()->route('tasks.index')
                ->with('success', 'Task added to Google Calendar');
        } catch (\Exception $e) {
            return redirect()->route('tasks.index')
                ->with('error', 'Failed to add task to Google Calendar');
        }
    }

    public function removeEvent(Task $task)
    {
        try {
            $this->calendarService->removeEvent($task);

            return redirect()->route('tasks.index')
                ->with('success', 'Task removed from Google Calendar');
        } catch (\Exception $e) {
            return redirect()->route('tasks.index')
                ->with('error', 'Failed to remove task from Google Calendar');
        }
    }
}
