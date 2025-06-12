@extends('layouts.app')

@section('title', 'Task History')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">History for: {{ $task->name }}</h1>
                <a href="{{ route('tasks.show', $task) }}" class="text-blue-600 hover:text-blue-800">
                    ← Back to Task
                </a>
            </div>

            <div class="space-y-4">
                @forelse($histories as $history)
                    <div class="border-b border-gray-200 pb-4 last:border-b-0">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm text-gray-600">
                                    Changed {{ $history->field }} from
                                    <span class="font-medium">{{ $history->old_value ?: 'empty' }}</span>
                                    to
                                    <span class="font-medium">{{ $history->new_value ?: 'empty' }}</span>
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    By {{ $history->user->name }} on {{ $history->created_at->format('M d, Y H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">No history available for this task.</p>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $histories->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
