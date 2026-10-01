<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot(['last_read_at'])
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Get the other user in a direct conversation.
     */
    public function getRecipientUser(int $currentUserId): ?User
    {
        return $this->users->firstWhere('id', '!=', $currentUserId);
    }

    /**
     * Mark conversation as read for a given user.
     */
    public function markAsReadFor(int $userId): void
    {
        $this->participants()
            ->where('user_id', $userId)
            ->update(['last_read_at' => now()]);
    }

    /**
     * Count unread messages for a given user.
     */
    public function unreadCountFor(int $userId): int
    {
        $participant = $this->participants()->where('user_id', $userId)->first();
        $lastReadAt = $participant?->last_read_at;

        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->when($lastReadAt, fn ($query) => $query->where('created_at', '>', $lastReadAt))
            ->count();
    }

    /**
     * Find or create a direct conversation between two users.
     */
    public static function findOrCreateDirect(int $userAId, int $userBId): self
    {
        // Find existing direct conversation sharing both participants
        $conversation = self::where('type', 'direct')
            ->whereHas('participants', fn ($q) => $q->where('user_id', $userAId))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $userBId))
            ->first();

        if ($conversation) {
            return $conversation;
        }

        $conversation = self::create([
            'type' => 'direct',
            'last_message_at' => now(),
        ]);

        $conversation->participants()->createMany([
            ['user_id' => $userAId, 'last_read_at' => now()],
            ['user_id' => $userBId, 'last_read_at' => null],
        ]);

        return $conversation;
    }
}
