<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    public function test_welcome_notification_is_sent_on_registration()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
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

    public function test_registration_creates_user()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            '_token' => csrf_token()
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('success', 'Registration successful! Welcome to ' . config('app.name'));

        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);
    }

    public function test_registration_validation()
    {
        $response = $this->post(route('register'), [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'different',
            '_token' => csrf_token()
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertDatabaseMissing('users', [
            'email' => 'invalid-email'
        ]);
    }
}
