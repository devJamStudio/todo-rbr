@extends('layouts.app')

@section('title', 'Shared Task')

@section('content')
<div class="min-h-screen bg-gray-100 py-6 flex flex-col justify-center sm:py-12">
    <div class="relative py-3 sm:max-w-xl sm:mx-auto">
        <x-card>
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    {{ $task->name }}
                </h3>

                <div class="mt-2 max-w-xl text-sm text-gray-500">
                    <p>{{ $task->description }}</p>
                </div>

                <div class="mt-5">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Priority</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($task->priority) }}</dd>
                        </div>

                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($task->status) }}</dd>
                        </div>

                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Due Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $task->due_date->format('F j, Y') }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Share Info -->
                <div class="pt-6 border-t border-gray-200">
                    <p class="text-sm text-gray-500">
                        This link will expire on {{ $task->share_token_expires_at->format('F j, Y') }}.
                    </p>
                </div>
            </div>
        </x-card>
    </div>
</div>
@endsection
