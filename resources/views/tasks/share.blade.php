<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Share Task') }}: {{ $task->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <div class="mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Share Link</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Generate a shareable link for this task. The link will be valid for 7 days.
                        </p>
                    </div>

                    @if(isset($shareLink) || ($task->share_token && $task->isShareTokenValid()))
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Share Link</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <input type="text" readonly value="{{ $shareLink ?? $task->getShareLink() }}"
                                    class="flex-1 min-w-0 block w-full px-3 py-2 rounded-md border border-gray-300 bg-gray-50 text-gray-900 sm:text-sm">
                                <button onclick="copyToClipboard('{{ $shareLink ?? $task->getShareLink() }}')"
                                    class="ml-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    Copy
                                </button>
                            </div>
                            @if($task->share_token_expires_at)
                                <p class="mt-2 text-sm text-gray-500">
                                    Expires: {{ $task->share_token_expires_at->format('M d, Y H:i') }}
                                </p>
                            @endif
                        </div>
                    @endif

                    <form action="{{ route('tasks.generate-share-link', $task) }}" method="POST" class="mt-4">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            {{ ($task->share_token && $task->isShareTokenValid()) ? 'Regenerate Share Link' : 'Generate Share Link' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Share link copied to clipboard!');
            }).catch(function(err) {
                console.error('Could not copy text: ', err);
            });
        }
    </script>
    @endpush
</x-app-layout>
