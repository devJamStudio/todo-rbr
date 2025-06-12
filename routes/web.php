<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\GoogleCalendarController;
use App\Models\User;

// Redirect root to tasks if authenticated, otherwise to login
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('tasks.index');
    }
    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [UserController::class, 'create'])->name('register');
    Route::post('/register', [UserController::class, 'store'])->name('register');
    Route::get('/login', [UserController::class, 'login'])->name('login');
    Route::post('/login', [UserController::class, 'authenticate'])->name('login.authenticate');

    // Password Reset Routes
    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');

    Route::post('/forgot-password', function (Request $request) {
        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink($request->only('email'));
        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    })->name('password.email');

    Route::get('/reset-password/{token}', function ($token) {
        return view('auth.reset-password', ['token' => $token]);
    })->name('password.reset');

    Route::post('/reset-password', function (Request $request) {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    })->name('password.update');
});

Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [UserController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [UserController::class, 'update'])->name('profile.update');

    // Tasks
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::get('/tasks/{task}/history', [TaskController::class, 'history'])->name('tasks.history');
    Route::get('/tasks/{task}/share', [TaskController::class, 'share'])->name('tasks.share');
    Route::post('/tasks/{task}/share', [TaskController::class, 'generateShareLink'])->name('tasks.generate-share-link');

    // Google Calendar Integration
    Route::get('/google/calendar/connect', [GoogleCalendarController::class, 'connect'])->name('google.calendar.connect');
    Route::get('/google/calendar/callback', [GoogleCalendarController::class, 'callback'])->name('google.calendar.callback');
    Route::post('/google/calendar/disconnect', [GoogleCalendarController::class, 'disconnect'])->name('google.calendar.disconnect');
    Route::post('/tasks/{task}/calendar', [TaskController::class, 'addToCalendar'])->name('tasks.calendar.add');
    Route::delete('/tasks/{task}/calendar', [TaskController::class, 'removeFromCalendar'])->name('tasks.calendar.remove');

    // Logout
    Route::post('/logout', [UserController::class, 'logout'])->name('logout');
});

// Public Task View (Shared)
Route::get('/tasks/shared/{token}', [TaskController::class, 'shared'])->name('tasks.shared');

// Google Calendar Integration
Route::middleware(['auth'])->group(function () {
    Route::get('/google/calendar/connect', [GoogleCalendarController::class, 'connect'])->name('google.calendar.connect');
    Route::get('/google/calendar/callback', [GoogleCalendarController::class, 'callback'])->name('google.calendar.callback');
    Route::post('/google/calendar/disconnect', [GoogleCalendarController::class, 'disconnect'])->name('google.calendar.disconnect');
    Route::post('/google/calendar/add/{task}', [GoogleCalendarController::class, 'addEvent'])->name('google.calendar.add');
    Route::post('/google/calendar/remove/{task}', [GoogleCalendarController::class, 'removeEvent'])->name('google.calendar.remove');
});
