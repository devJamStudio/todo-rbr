<?php

namespace App\Services;

use App\Models\User;
use App\Models\Task;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    protected $client;
    protected $service;

    public function __construct()
    {
        $this->client = new Client();

        $clientId = config('google_calendar.client_id');
        $clientSecret = config('google_calendar.client_secret');

        if (empty($clientId) || empty($clientSecret)) {
            throw new \InvalidArgumentException(
                'Google Calendar credentials are not configured. Please set GOOGLE_CALENDAR_CLIENT_ID and GOOGLE_CALENDAR_CLIENT_SECRET in your .env file.'
            );
        }

        $this->client->setClientId($clientId);
        $this->client->setClientSecret($clientSecret);
        $this->client->setRedirectUri(config('google_calendar.redirect_uri'));
        $this->client->setScopes(config('google_calendar.scopes'));
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        $this->client->setIncludeGrantedScopes(true);
    }

    public function connect()
    {
        return $this->client->createAuthUrl();
    }

    public function disconnect(User $user)
    {
        try {
            if ($user->google_calendar_token) {
                $this->client->setAccessToken($user->google_calendar_token);
                $this->client->revokeToken();
            }

            $user->update([
                'google_calendar_token' => null,
                'google_calendar_refresh_token' => null,
                'google_calendar_id' => null,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to disconnect from Google Calendar', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            return false;
        }
    }

    public function addEvent(Task $task)
    {
        $user = $task->user;

        if (!$user->google_calendar_token) {
            throw new \Exception('Google Calendar not connected');
        }

        try {
            $this->client->setAccessToken($user->google_calendar_token);

            if ($this->client->isAccessTokenExpired()) {
                $this->refreshToken($user);
            }

            $this->service = new Calendar($this->client);

            $event = new Event([
                'summary' => $task->name,
                'description' => $task->description,
                'start' => [
                    'dateTime' => $task->due_date->format('c'),
                    'timeZone' => config('app.timezone'),
                ],
                'end' => [
                    'dateTime' => $task->due_date->addHour()->format('c'),
                    'timeZone' => config('app.timezone'),
                ],
            ]);

            $calendarEvent = $this->service->events->insert('primary', $event);

            $task->update([
                'google_calendar_event_id' => $calendarEvent->getId(),
            ]);

            return $calendarEvent;
        } catch (\Exception $e) {
            Log::error('Failed to add event to Google Calendar', [
                'error' => $e->getMessage(),
                'task_id' => $task->id,
                'user_id' => $user->id
            ]);
            throw $e;
        }
    }

    public function removeEvent(Task $task)
    {
        if (!$task->google_calendar_event_id) {
            return false;
        }

        $user = $task->user;

        if (!$user->google_calendar_token) {
            throw new \Exception('Google Calendar not connected');
        }

        try {
            $this->client->setAccessToken($user->google_calendar_token);

            if ($this->client->isAccessTokenExpired()) {
                $this->refreshToken($user);
            }

            $this->service = new Calendar($this->client);

            $this->service->events->delete('primary', $task->google_calendar_event_id);

            $task->update([
                'google_calendar_event_id' => null,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to remove event from Google Calendar', [
                'error' => $e->getMessage(),
                'task_id' => $task->id,
                'user_id' => $user->id
            ]);
            return false;
        }
    }

    public function getClient()
    {
        return $this->client;
    }

    protected function refreshToken(User $user)
    {
        if (!$user->google_calendar_refresh_token) {
            throw new \Exception('No refresh token available');
        }

        $this->client->setAccessToken($user->google_calendar_token);
        $newToken = $this->client->fetchAccessTokenWithRefreshToken($user->google_calendar_refresh_token);

        $user->update([
            'google_calendar_token' => $newToken['access_token'],
            'google_calendar_refresh_token' => $newToken['refresh_token'] ?? $user->google_calendar_refresh_token,
        ]);
    }
}
