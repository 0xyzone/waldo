<?php

namespace App\Models;

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
use Laravel\Sanctum\HasApiTokens;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'username', 'email', 'phone', 'avatar_url', 'password', 'must_change_password', 'read_receipts_enabled'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasAvatar, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasPushSubscriptions, HasRoles, Notifiable;

    /**
     * Determine if the user can access the Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
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
            'read_receipts_enabled' => 'boolean',
        ];
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
        $participants = ConversationParticipant::where('user_id', $this->id)->get();

        if ($participants->isEmpty()) {
            return 0;
        }

        $unread = 0;
        foreach ($participants as $participant) {
            $unread += Message::where('conversation_id', $participant->conversation_id)
                ->where('sender_id', '!=', $this->id)
                ->when($participant->last_read_at, fn ($q) => $q->where('created_at', '>', $participant->last_read_at))
                ->count();
        }

        return $unread;
    }
}
