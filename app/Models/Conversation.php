<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'description',
        'created_by',
        'avatar_url',
        'only_admins_can_message',
        'only_admins_can_edit_info',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'only_admins_can_message' => 'boolean',
            'only_admins_can_edit_info' => 'boolean',
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
            ->withPivot(['last_read_at', 'role'])
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

    public function pinnedMessages(): HasMany
    {
        return $this->hasMany(Message::class)
            ->where('is_pinned', true)
            ->where(function ($q) {
                $q->whereNull('pinned_until')
                    ->orWhere('pinned_until', '>', now());
            })
            ->orderByDesc('pinned_at');
    }

    public function isPinnedFor(int $userId): bool
    {
        $participant = $this->participants->firstWhere('user_id', $userId);

        return $participant?->isCurrentlyPinned() ?? false;
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isGroup(): bool
    {
        return $this->type === 'group';
    }

    public function getDisplayName(int $currentUserId): string
    {
        if ($this->isGroup()) {
            return $this->title ?: 'Group Chat';
        }

        return $this->getRecipientUser($currentUserId)?->name ?? 'User';
    }

    public function getDisplayAvatar(int $currentUserId): ?string
    {
        if ($this->isGroup()) {
            return $this->avatar_url ? asset('storage/'.$this->avatar_url) : null;
        }

        return $this->getRecipientUser($currentUserId)?->getFilamentAvatarUrl();
    }

    /**
     * Create a group conversation.
     *
     * @param  array<int>  $participantUserIds
     */
    public static function createGroup(int $creatorId, string $title, array $participantUserIds, ?string $avatarUrl = null): self
    {
        $conversation = self::create([
            'type' => 'group',
            'title' => $title,
            'created_by' => $creatorId,
            'avatar_url' => $avatarUrl,
            'last_message_at' => now(),
        ]);

        $allUserIds = array_unique(array_merge([$creatorId], $participantUserIds));

        foreach ($allUserIds as $uId) {
            $conversation->participants()->create([
                'user_id' => $uId,
                'role' => ($uId === $creatorId) ? 'admin' : 'member',
                'last_read_at' => ($uId === $creatorId) ? now() : null,
            ]);
        }

        return $conversation;
    }

    public function isOwner(?int $userId = null): bool
    {
        $userId = $userId ?? auth()->id();

        return $this->isGroup() && $this->created_by === $userId;
    }

    public function isAdmin(?int $userId = null): bool
    {
        $userId = $userId ?? auth()->id();

        if ($this->isOwner($userId)) {
            return true;
        }

        if ($this->relationLoaded('participants')) {
            $participant = $this->participants->firstWhere('user_id', $userId);
            if ($participant) {
                return $participant->isAdmin();
            }
        }

        return $this->participants()->where('user_id', $userId)->where('role', 'admin')->exists();
    }

    public function getUserRole(?int $userId = null): string
    {
        $userId = $userId ?? auth()->id();

        if ($this->isOwner($userId)) {
            return 'owner';
        }

        $participant = $this->participants->firstWhere('user_id', $userId);

        return $participant?->role ?? 'member';
    }

    public function canUserEditInfo(?int $userId = null): bool
    {
        if (! $this->isGroup()) {
            return false;
        }

        if (! $this->only_admins_can_edit_info) {
            return true;
        }

        return $this->isAdmin($userId);
    }

    public function canUserSendMessage(?int $userId = null): bool
    {
        if (! $this->isGroup()) {
            return true;
        }

        if (! $this->only_admins_can_message) {
            return true;
        }

        return $this->isAdmin($userId);
    }

    public function addMember(int $userId, string $role = 'member'): ConversationParticipant
    {
        return $this->participants()->firstOrCreate(
            ['user_id' => $userId],
            [
                'role' => $role,
                'last_read_at' => now(),
            ]
        );
    }

    public function removeMember(int $userId): bool
    {
        if ($this->created_by === $userId) {
            return false; // Cannot remove owner
        }

        return (bool) $this->participants()->where('user_id', $userId)->delete();
    }

    public function setAdmin(int $userId, bool $isAdmin = true): bool
    {
        if ($this->created_by === $userId) {
            return true; // Owner is always admin
        }

        $role = $isAdmin ? 'admin' : 'member';

        return (bool) $this->participants()->where('user_id', $userId)->update(['role' => $role]);
    }

    public function transferOwnership(int $newOwnerId): bool
    {
        if ($newOwnerId === $this->created_by) {
            return true;
        }

        $oldOwnerId = $this->created_by;

        // Ensure new owner is in conversation and marked admin
        $this->addMember($newOwnerId, 'admin');
        $this->participants()->where('user_id', $newOwnerId)->update(['role' => 'admin']);

        // Update created_by
        $this->update(['created_by' => $newOwnerId]);

        // Keep previous owner as admin
        if ($oldOwnerId) {
            $this->participants()->where('user_id', $oldOwnerId)->update(['role' => 'admin']);
        }

        return true;
    }
}
