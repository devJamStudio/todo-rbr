@extends('layouts.app')

@section('title', $task->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">{{ $task->name }}</h1>
                <div class="flex space-x-2">
                    <a href="{{ route('tasks.history', $task) }}" class="text-gray-400 hover:text-gray-500" title="View History">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </a>
                    <a href="{{ route('tasks.edit', $task) }}" class="text-blue-400 hover:text-blue-500" title="Edit">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </a>
                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-500" title="Delete" onclick="return confirm('Are you sure you want to delete this task?')">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>

            <div class="space-y-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-700">Description</h2>
                    <p class="mt-2 text-gray-600">{{ $task->description ?: 'No description provided.' }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-700">Priority</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium
                            {{ $task->priority === 'high' ? 'bg-red-100 text-red-800' :
                               ($task->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' :
                               'bg-green-100 text-green-800') }}">
                            {{ ucfirst($task->priority) }}
                        </span>
                    </div>

                    <div>
                        <h2 class="text-lg font-semibold text-gray-700">Status</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium
                            {{ $task->status === 'completed' ? 'bg-green-100 text-green-800' :
                               ($task->status === 'in_progress' ? 'bg-blue-100 text-blue-800' :
                               'bg-gray-100 text-gray-800') }}">
                            {{ $task->status === 'in_progress' ? 'In Progress' : ucfirst($task->status) }}
                        </span>
                    </div>

                    <div>
                        <h2 class="text-lg font-semibold text-gray-700">Due Date</h2>
                        <p class="mt-2 text-gray-600">{{ $task->due_date->format('F j, Y') }}</p>
                    </div>

                    @if($task->google_event_id)
                    <div>
                        <h2 class="text-lg font-semibold text-gray-700">Google Calendar</h2>
                        <p class="mt-2 text-gray-600">Added to Google Calendar</p>
                        <form action="{{ route('tasks.calendar.remove', $task) }}" method="POST" class="mt-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-600 hover:text-red-800">Remove from Calendar</button>
                        </form>
                    </div>
                    @else
                    <div>
                        <h2 class="text-lg font-semibold text-gray-700">Google Calendar</h2>
                        <form action="{{ route('tasks.calendar.add', $task) }}" method="POST" class="mt-2">
                            @csrf
                            <button type="submit" class="text-sm text-blue-600 hover:text-blue-800">Add to Calendar</button>
                        </form>
                    </div>
                    @endif
                </div>

                <div class="pt-6 border-t border-gray-200">
                    <a href="{{ route('tasks.index') }}" class="text-blue-600 hover:text-blue-800">
                        ← Back to Tasks
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
