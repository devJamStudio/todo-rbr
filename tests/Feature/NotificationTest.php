<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDueNotification;
use App\Notifications\TaskDueReminder;
use App\Notifications\TaskRevisitReminder;
use App\Notifications\WelcomeNotification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;
use Carbon\Carbon;

class NotificationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private string $password = 'password123';

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->user = User::factory()->create([
            'password' => bcrypt($this->password)
        ]);
    }

    public function test_task_due_notification_is_sent()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => now()->addDay(),
            'status' => 'pending'
        ]);

        $task->sendDueNotification();

        Notification::assertSentTo(
            $this->user,
            TaskDueNotification::class,
            function ($notification) use ($task) {
                return $notification->task->id === $task->id;
            }
        );
    }

    public function test_task_due_reminder_is_sent()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => now()->addDay(),
            'status' => 'pending'
        ]);

        $task->sendDueReminder();

        Notification::assertSentTo(
            $this->user,
            TaskDueReminder::class,
            function ($notification) use ($task) {
                return $notification->task->id === $task->id;
            }
        );
    }

    public function test_task_revisit_reminder_is_sent()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'last_reviewed_at' => now()->subWeek(),
            'status' => 'pending'
        ]);

        $task->sendRevisitReminder();

        Notification::assertSentTo(
            $this->user,
            TaskRevisitReminder::class,
            function ($notification) use ($task) {
                return $notification->task->id === $task->id;
            }
        );
    }

    public function test_registration_notification_is_sent()
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            '_token' => csrf_token()
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('success', 'Registration successful! Welcome to ' . config('app.name'));

        $user = User::where('email', $userData['email'])->first();
        $this->assertNotNull($user);
        $this->assertEquals($userData['name'], $user->name);

        Notification::assertSentTo(
            $user,
            WelcomeNotification::class,
            function ($notification) use ($user) {
                return $notification->toMail($user)->subject === 'Welcome to ' . config('app.name');
            }
        );
    }

    public function test_password_reset_notification_is_sent()
    {
        $response = $this->post(route('password.email'), [
            'email' => $this->user->email
        ]);

        $response->assertSessionHas('status', 'We have emailed your password reset link!');

        Notification::assertSentTo(
            $this->user,
            ResetPassword::class,
            function ($notification) {
                return $notification->toMail($this->user)->subject === 'Reset Password Notification';
            }
        );
    }

    public function test_password_reset_link_is_valid()
    {
        $token = Password::createToken($this->user);

        $response = $this->get(route('password.reset', [
            'token' => $token,
            'email' => $this->user->email
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('auth.reset-password');
    }

    public function test_password_can_be_reset()
    {
        $token = Password::createToken($this->user);
        $newPassword = 'newpassword123';

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $this->user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', 'Your password has been reset!');

        $this->assertTrue(Hash::check($newPassword, $this->user->fresh()->password));
    }

    public function test_notification_is_not_sent_for_completed_tasks()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => now()->addDay(),
            'status' => 'completed'
        ]);

        $task->sendDueNotification();

        Notification::assertNotSentTo(
            $this->user,
            TaskDueNotification::class
        );
    }

    public function test_notification_is_not_sent_for_past_due_tasks()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => now()->subDay(),
            'status' => 'pending'
        ]);

        $task->sendDueNotification();

        Notification::assertNotSentTo(
            $this->user,
            TaskDueNotification::class
        );
    }

    public function test_multiple_notifications_are_sent_for_multiple_tasks()
    {
        $tasks = Task::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'due_date' => now()->addDay(),
            'status' => 'pending'
        ]);

        foreach ($tasks as $task) {
            $task->sendDueNotification();
        }

        Notification::assertSentTo(
            $this->user,
            TaskDueNotification::class,
            function ($notification) use ($tasks) {
                return in_array($notification->task->id, $tasks->pluck('id')->toArray());
            }
        );
    }
}
