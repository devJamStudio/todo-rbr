<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_calendar_id',
        'google_calendar_token',
        'google_calendar_refresh_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_calendar_token',
        'google_calendar_refresh_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'google_token_expires_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::created(function ($user) {
            Log::info('User created, sending welcome notification', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            try {
                $user->notify(new WelcomeNotification());
                Log::info('Welcome notification sent successfully');
            } catch (\Exception $e) {
                Log::error('Failed to send welcome notification', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id
                ]);
                // Don't throw the exception, just log it
            }
        });
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function sendPasswordResetNotification($token)
    {
        Log::info('Sending password reset notification', [
            'user_id' => $this->id,
            'email' => $this->email
        ]);

        try {
            $this->notify(new ResetPasswordNotification($token));
            Log::info('Password reset notification sent successfully');
        } catch (\Exception $e) {
            Log::error('Failed to send password reset notification', [
                'error' => $e->getMessage(),
                'user_id' => $this->id
            ]);
            throw $e;
        }
    }
}
