<?php

namespace App\Models;

use App\Services\GoogleCalendarService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'due_date',
        'priority',
        'status',
        'user_id',
        'share_token',
        'share_token_expires_at',
        'google_calendar_event_id',
        'last_reviewed_at',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'share_token_expires_at' => 'datetime'
    ];

    /**
     * Powiązanie z użytkownikiem
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($task) {
            $task->share_token = Str::random(32);
            $task->share_token_expires_at = now()->addDays(7);
        });

        static::created(function ($task) {
            $user = Auth::user();
            $task->load('user');
            TaskHistory::create([
                'task_id' => $task->id,
                'field' => 'created',
                'old_value' => null,
                'new_value' => 'Task created',
                'user_id' => $user ? $user->id : $task->user_id,
                'user_name' => $user ? $user->name : $task->user->name
            ]);
        });

        static::updating(function ($task) {
            $original = $task->getOriginal();
            $changes = $task->getDirty();
            $user = Auth::user();
            $task->load('user');

            foreach ($changes as $field => $newValue) {
                if (!in_array($field, ['updated_at', 'share_token', 'share_token_expires_at'])) {
                    TaskHistory::create([
                        'task_id' => $task->id,
                        'field' => $field,
                        'old_value' => $original[$field] ?? null,
                        'new_value' => $newValue,
                        'user_id' => $user ? $user->id : $task->user_id,
                        'user_name' => $user ? $user->name : $task->user->name
                    ]);
                }
            }
        });
    }

    public function histories(): HasMany
    {
        return $this->hasMany(TaskHistory::class)->orderBy('created_at', 'desc');
    }

    public function getHistoryForField(string $field)
    {
        return $this->histories()->where('field', $field)->orderBy('created_at', 'desc')->get();
    }

    public function getLatestHistoryForField(string $field)
    {
        return $this->histories()->where('field', $field)->latest()->first();
    }

    /**
     * Czy zadanie jest nadal dostępne publicznie?
     */
    public function isPubliclyAvailable(): bool
    {
        return $this->share_token && $this->share_token_expires_at && $this->share_token_expires_at->isFuture();
    }

    public function generateShareToken(): string
    {
        $this->share_token = Str::random(32);
        $this->share_token_expires_at = now()->addDays(7);
        $this->save();
        return $this->share_token;
    }

    public function isShareTokenValid(): bool
    {
        return $this->share_token && $this->share_token_expires_at && $this->share_token_expires_at->isFuture();
    }

    public function getShareLink(): string
    {
        if (!$this->share_token || !$this->isShareTokenValid()) {
            $this->generateShareToken();
        }
        return route('tasks.shared', ['token' => $this->share_token]);
    }

    public function generateShareLink(): string
    {
        $this->generateShareToken();
        return $this->getShareLink();
    }

    public function makePubliclyAvailable(): void
    {
        $this->share_token = Str::random(32);
        $this->share_token_expires_at = now()->addDays(7);
        $this->save();
    }

    public function makePrivate(): void
    {
        $this->share_token = null;
        $this->share_token_expires_at = null;
        $this->save();
    }

    public function sendDueNotification(): void
    {
        if ($this->status === 'completed' || $this->due_date->isPast()) {
            return;
        }

        $this->user->notify(new \App\Notifications\TaskDueNotification($this));
    }

    public function sendDueReminder(): void
    {
        if ($this->status === 'completed' || $this->due_date->isPast()) {
            return;
        }

        $this->user->notify(new \App\Notifications\TaskDueReminder($this));
    }

    public function sendRevisitReminder(): void
    {
        if ($this->status === 'completed' || !$this->last_reviewed_at || $this->last_reviewed_at->diffInDays(now()) < 7) {
            return;
        }

        $this->user->notify(new \App\Notifications\TaskRevisitReminder($this));
    }

    public function syncToGoogleCalendar(): bool
    {
        if (!$this->user->google_calendar_id) {
            return false;
        }

        try {
            $service = app(GoogleCalendarService::class);
            $event = $service->addEvent($this);
            $this->update(['google_calendar_event_id' => $event->getId()]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function removeFromGoogleCalendar(): bool
    {
        if (!$this->google_calendar_event_id) {
            return false;
        }

        try {
            $service = app(GoogleCalendarService::class);
            $service->removeEvent($this);
            $this->update(['google_calendar_event_id' => null]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function updateGoogleCalendarEvent(): bool
    {
        if (!$this->google_calendar_event_id) {
            return $this->syncToGoogleCalendar();
        }

        try {
            $service = app(GoogleCalendarService::class);
            $service->removeEvent($this);
            $event = $service->addEvent($this);
            $this->update(['google_calendar_event_id' => $event->getId()]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
