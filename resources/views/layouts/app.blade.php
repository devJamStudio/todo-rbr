<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laravel'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <a href="{{ route('tasks.index') }}" class="text-xl font-bold text-gray-800">
                                Task Manager
                            </a>
                        </div>

                        @auth
                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <a href="{{ route('tasks.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                Tasks
                            </a>
                        </div>
                        @endauth
                    </div>

                    <!-- User Dropdown -->
                    @auth
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <div class="ml-3 relative">
                            <div class="flex items-center">
                                <span class="text-sm text-gray-500 mr-4">{{ Auth::user()->email }}</span>
                                <div class="relative" x-data="{ open: false }" @click.away="open = false" @close.stop="open = false">
                                    <div @click="open = ! open">
                                        <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition duration-150 ease-in-out">
                                            <svg class="h-8 w-8 rounded-full" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                        </button>
                                    </div>

                                    <div x-show="open"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="transform opacity-0 scale-95"
                                        x-transition:enter-end="transform opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="transform opacity-100 scale-100"
                                        x-transition:leave-end="transform opacity-0 scale-95"
                                        class="absolute right-0 z-50 mt-2 w-48 rounded-md shadow-lg origin-top-right"
                                        style="display: none;">
                                        <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white">
                                            <!-- Edit Profile -->
                                            <a href="{{ route('profile.edit') }}" class="block w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 text-left">
                                                Edit Profile
                                            </a>

                                            <!-- Google Calendar Connection -->
                                            @if(Auth::user()->google_calendar_id)
                                                <form method="POST" action="{{ route('google.calendar.disconnect') }}" class="block w-full">
                                                    @csrf
                                                    <button type="submit" class="w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 text-left">
                                                        Disconnect Google Calendar
                                                    </button>
                                                </form>
                                            @else
                                                <a href="{{ route('google.calendar.connect') }}" class="block w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 text-left">
                                                    Connect Google Calendar
                                                </a>
                                            @endif

                                            <!-- Logout -->
                                            <form method="POST" action="{{ route('logout') }}" class="block w-full">
                                                @csrf
                                                <button type="submit" class="w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 text-left">
                                                    Logout
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <a href="{{ route('login') }}" class="text-sm text-gray-700 hover:text-gray-900">Login</a>
                        <a href="{{ route('register') }}" class="ml-4 text-sm text-gray-700 hover:text-gray-900">Register</a>
                    </div>
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>

        <!-- Notifications -->
        @if(session('success'))
            <x-notification
                type="success"
                :message="session('success')"
                :show="true"
            />
        @endif

        @if(session('error'))
            <x-notification
                type="error"
                :message="session('error')"
                :show="true"
            />
        @endif

        @if($errors->any())
            @foreach($errors->all() as $error)
                <x-notification
                    type="error"
                    :message="$error"
                    :show="true"
                />
            @endforeach
        @endif
    </div>

    @stack('scripts')
</body>
</html>
