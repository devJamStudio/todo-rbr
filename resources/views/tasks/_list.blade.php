@forelse($tasks as $task)
    <div class="p-6 border-b border-gray-200 last:border-b-0">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">
                    <a href="{{ route('tasks.show', $task) }}" class="hover:text-blue-600">
                        {{ $task->name }}
                    </a>
                </h2>
                <p class="mt-1 text-gray-600">{{ Str::limit($task->description, 100) }}</p>
                <div class="mt-2 flex items-center space-x-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $task->priority === 'high' ? 'bg-red-100 text-red-800' :
                           ($task->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' :
                           'bg-green-100 text-green-800') }}">
                        {{ ucfirst($task->priority) }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $task->status === 'completed' ? 'bg-green-100 text-green-800' :
                           ($task->status === 'in_progress' ? 'bg-blue-100 text-blue-800' :
                           'bg-gray-100 text-gray-800') }}">
                        {{ $task->status === 'in_progress' ? 'In Progress' : ucfirst($task->status) }}
                    </span>
                    <span class="text-sm text-gray-500">
                        Due: {{ $task->due_date->format('M d, Y') }}
                    </span>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('tasks.history', $task) }}" class="text-gray-400 hover:text-gray-500" title="View History">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </a>
                <a href="{{ route('tasks.share', $task) }}" class="text-green-400 hover:text-green-500" title="Share">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
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
    </div>
@empty
    <div class="p-6 text-center text-gray-500">
        No tasks found. Create your first task!
    </div>
@endforelse
