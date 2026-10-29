<?php

namespace App\Models;

use App\Traits\HasActionLogs;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\HasApiTokens;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'username', 'email', 'phone', 'avatar_url', 'password', 'must_change_password', 'is_active', 'read_receipts_enabled', 'online_status_enabled', 'last_active_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasAvatar, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasActionLogs, HasApiTokens, HasFactory, HasPushSubscriptions, HasRoles, Notifiable;

    /**
     * Flag to allow reactivation only when validated with Super Admin OTP.
     */
    public static bool $allowReactivationWithoutOtp = false;

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::updating(function (User $user) {
            if ($user->isDirty('is_active') && (bool) $user->is_active && ! (bool) $user->getOriginal('is_active')) {
                if (! static::$allowReactivationWithoutOtp) {
                    throw new ValidationException(
                        Validator::make([], []),
                        ['is_active' => ['Reactivating this account requires Super Admin OTP verification.']]
                    );
                }
            }
        });
    }

    /**
     * Determine if the user can access the Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) ($this->is_active ?? true);
    }

    /**
     * Get the user's Filament avatar URL.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ? asset('storage/'.$this->avatar_url) : null;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'is_active' => 'boolean',
            'read_receipts_enabled' => 'boolean',
            'online_status_enabled' => 'boolean',
            'last_active_at' => 'datetime',
        ];
    }

    /**
     * Check if this user and another user can mutually view online & last active status.
     * Follows WhatsApp / Messenger privacy model: if either disables online status, it is hidden for both.
     */
    public function canViewUserOnlineStatus(?User $otherUser): bool
    {
        if (! $otherUser) {
            return false;
        }

        $viewerEnabled = (bool) ($this->online_status_enabled ?? true);
        $otherEnabled = (bool) ($otherUser->online_status_enabled ?? true);

        return $viewerEnabled && $otherEnabled;
    }

    /**
     * Human readable last active timestamp.
     */
    public function getLastActiveFormatted(): ?string
    {
        if (! $this->last_active_at) {
            return null;
        }

        $now = now();
        $diffSeconds = $this->last_active_at->diffInSeconds($now);

        if ($diffSeconds < 60) {
            return 'Just now';
        }

        if ($this->last_active_at->isToday()) {
            return 'Today at '.$this->last_active_at->format('g:i A');
        }

        if ($this->last_active_at->isYesterday()) {
            return 'Yesterday at '.$this->last_active_at->format('g:i A');
        }

        if ($this->last_active_at->year === $now->year) {
            return $this->last_active_at->format('M j \a\t g:i A');
        }

        return $this->last_active_at->format('M j, Y \a\t g:i A');
    }

    /**
     * Get all of the mapUsers for the User
     */
    public function mapUsers(): HasMany
    {
        return $this->hasMany(MapUser::class);
    }

    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot(['last_read_at'])
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function unreadMessagesCount(): int
    {
        return (int) Message::query()
            ->join('conversation_participants as cp', function ($join) {
                $join->on('cp.conversation_id', '=', 'messages.conversation_id')
                    ->where('cp.user_id', '=', $this->id);
            })
            ->where('messages.sender_id', '!=', $this->id)
            ->where(function ($q) {
                $q->whereNull('cp.last_read_at')
                    ->orWhereColumn('messages.created_at', '>', 'cp.last_read_at');
            })
            ->count();
    }
}
