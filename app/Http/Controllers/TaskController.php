<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDueNotification;
use Google\Client as Google_Client;
use Google\Service\Calendar as Google_Service_Calendar;
use Google\Service\Calendar\Event as Google_Service_Calendar_Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Database\Eloquent\Model;
use App\Services\GoogleCalendarService;

class TaskController extends BaseController
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('auth')->except(['shared']);
        $this->middleware('web');
    }

    public function index(Request $request)
    {
        $query = Task::where('user_id', Auth::id());

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('due_date')) {
            $query->whereDate('due_date', $request->due_date);
        }

        $tasks = $query->latest()->get();
        $loading = false;

        if ($request->ajax()) {
            return response()->json([
                'html' => view('tasks._list', compact('tasks'))->render()
            ]);
        }

        return view('tasks.index', compact('tasks', 'loading'));
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);
        return view('tasks.show', compact('task'));
    }

    public function create()
    {
        return view('tasks.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:pending,in_progress,completed',
            'due_date' => 'required|date|after:today',
        ]);

        $task = Task::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'status' => $validated['status'],
            'due_date' => $validated['due_date'],
        ]);

        return redirect()->route('tasks.index')
            ->with('success', 'Task created successfully.');
    }

    public function edit(Task $task)
    {
        $this->authorize('update', $task);
        return view('tasks.edit', compact('task'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:pending,in_progress,completed',
            'due_date' => 'required|date|after:today',
        ]);

        $task->update($validated);

        return redirect()->route('tasks.index')
            ->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $task->delete();

        return redirect()->route('tasks.index')
            ->with('success', 'Task deleted successfully.');
    }

    public function share(Task $task)
    {
        $this->authorize('update', $task);
        $shareLink = $task->generateShareLink();
        return view('tasks.share', compact('task', 'shareLink'));
    }

    /**
     * Show the form for sharing a task
     */
    public function showShareForm(Task $task)
    {
        $this->authorize('view', $task);
        return view('tasks.share', compact('task'));
    }

    /**
     * Generate a share link for a task
     */
    public function generateShareLink(Task $task)
    {
        $this->authorize('update', $task);
        $shareLink = $task->generateShareLink();

        return redirect()->route('tasks.share', $task)
            ->with('success', 'Share link generated successfully.')
            ->with('shareLink', $shareLink);
    }

    /**
     * Show a shared task
     */
    public function shared($token)
    {
        $task = Task::where('share_token', $token)->firstOrFail();

        if (!$task->isShareTokenValid()) {
            abort(404, 'This shared task link has expired.');
        }

        return view('tasks.shared', compact('task'));
    }

    public function history(Task $task)
    {
        $this->authorize('view', $task);
        $histories = $task->histories()
            ->with('user')
            ->latest()
            ->paginate(10);

        return view('tasks.history', compact('task', 'histories'));
    }

    public function addToCalendar(Task $task, GoogleCalendarService $calendarService)
    {
        $this->authorize('update', $task);
        try {
            $calendarService->addEvent($task);
            return redirect()->route('tasks.index')->with('success', 'Task added to Google Calendar!');
        } catch (\Exception $e) {
            Log::error('Google Calendar Error: ' . $e->getMessage());
            return redirect()->route('tasks.index')->with('error', 'Failed to add task to Google Calendar.');
        }
    }

    public function removeFromCalendar(Task $task, GoogleCalendarService $calendarService)
    {
        $this->authorize('update', $task);
        try {
            $calendarService->removeEvent($task);
            return back()->with('success', 'Task removed from Google Calendar.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to remove task from Google Calendar.');
        }
    }
}
