<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'reply_to_id',
        'sender_id',
        'body',
        'is_forwarded',
        'delivered_at',
        'type',
        'attachment_path',
        'attachment_name',
        'file_type',
        'file_size',
        'is_deleted',
        'deleted_at',
        'is_pinned',
        'pinned_at',
        'pinned_until',
        'pinned_by',
    ];

    protected function casts(): array
    {
        return [
            'is_forwarded' => 'boolean',
            'delivered_at' => 'datetime',
            'is_deleted' => 'boolean',
            'deleted_at' => 'datetime',
            'is_pinned' => 'boolean',
            'pinned_at' => 'datetime',
            'pinned_until' => 'datetime',
        ];
    }

    /**
     * Get the delivery status of this message for the viewer.
     * Returns: 'sent' | 'delivered' | 'seen'
     */
    public function getDeliveryStatus(int $viewerUserId): string
    {
        if ($this->sender_id !== $viewerUserId || $this->is_deleted) {
            return 'sent';
        }

        $conversation = $this->conversation;
        if (! $conversation) {
            return 'sent';
        }

        $viewer = auth()->user() && auth()->id() === $viewerUserId ? auth()->user() : User::find($viewerUserId);
        $viewerSharesReceipts = (bool) ($viewer?->read_receipts_enabled ?? true);

        // Direct 1-on-1 chat
        if ($conversation->type === 'direct') {
            $otherParticipant = $conversation->participants->firstWhere('user_id', '!=', $viewerUserId)
                ?? $conversation->participants()->where('user_id', '!=', $viewerUserId)->first();

            if (! $otherParticipant) {
                return 'sent';
            }

            // Check if seen (read)
            if ($otherParticipant->last_read_at && $otherParticipant->last_read_at >= $this->created_at) {
                // If viewer has disabled read receipts, viewer cannot see blue ticks (WhatsApp rule)
                if (! $viewerSharesReceipts) {
                    return 'delivered';
                }

                $recipientUser = $otherParticipant->user ?? User::find($otherParticipant->user_id);
                $recipientSharesReceipts = (bool) ($recipientUser?->read_receipts_enabled ?? true);

                // If recipient disabled read receipts, sender cannot see blue ticks either
                if (! $recipientSharesReceipts) {
                    return 'delivered';
                }

                return 'seen';
            }

            // Check if delivered
            if ($this->delivered_at !== null || ($otherParticipant->last_delivered_at && $otherParticipant->last_delivered_at >= $this->created_at)) {
                return 'delivered';
            }

            return 'sent';
        }

        // Group chat
        $otherParticipants = $conversation->participants->where('user_id', '!=', $viewerUserId);
        if ($otherParticipants->isEmpty()) {
            $otherParticipants = $conversation->participants()->where('user_id', '!=', $viewerUserId)->get();
        }

        if ($otherParticipants->isEmpty()) {
            return 'sent';
        }

        $allRead = $otherParticipants->every(fn ($p) => $p->last_read_at && $p->last_read_at >= $this->created_at);
        if ($allRead) {
            if (! $viewerSharesReceipts) {
                return 'delivered';
            }

            return 'seen';
        }

        $anyDelivered = $this->delivered_at !== null || $otherParticipants->contains(fn ($p) => $p->last_delivered_at && $p->last_delivered_at >= $this->created_at);
        if ($anyDelivered) {
            return 'delivered';
        }

        return 'sent';
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Message::class, 'reply_to_id');
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function pinnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pinned_by');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(MessageReaction::class);
    }

    public function getReactionsSummary(int $userId): array
    {
        if ($this->relationLoaded('reactions')) {
            $reactions = $this->reactions;
        } else {
            $reactions = $this->reactions()->with('user')->get();
        }

        $grouped = [];

        foreach ($reactions as $r) {
            $emoji = $r->reaction;
            if (! isset($grouped[$emoji])) {
                $grouped[$emoji] = [
                    'reaction' => $emoji,
                    'count' => 0,
                    'reacted_by_me' => false,
                    'users' => [],
                ];
            }

            $grouped[$emoji]['count']++;
            if ($r->user_id === $userId) {
                $grouped[$emoji]['reacted_by_me'] = true;
            }
            if ($r->relationLoaded('user') && $r->user) {
                $grouped[$emoji]['users'][] = $r->user->name;
            } elseif ($r->user) {
                $grouped[$emoji]['users'][] = $r->user->name;
            }
        }

        return array_values($grouped);
    }

    public function canBeDeletedBy(int $userId): bool
    {
        if ($this->is_deleted) {
            return false;
        }

        // Group Admins / Owners can delete any message/attachment in the group
        $conversation = $this->conversation ?? Conversation::find($this->conversation_id);
        if ($conversation && $conversation->isGroup() && $conversation->isAdmin($userId)) {
            return true;
        }

        if ($this->sender_id !== $userId) {
            return false;
        }

        // Only valid till 15 minutes from the time the chat was sent
        return $this->created_at !== null && abs($this->created_at->diffInMinutes(now())) <= 15;
    }

    public function isCurrentlyPinned(): bool
    {
        if (! $this->is_pinned) {
            return false;
        }

        if ($this->pinned_until !== null && $this->pinned_until->isPast()) {
            return false;
        }

        return true;
    }

    public function getPinnedTimeRemaining(): ?string
    {
        if (! $this->is_pinned) {
            return null;
        }

        if ($this->pinned_until === null) {
            return 'Never ending';
        }

        if ($this->pinned_until->isPast()) {
            return 'Expired';
        }

        return $this->pinned_until->diffForHumans(now(), [
            'parts' => 2,
            'syntax' => CarbonInterface::DIFF_ABSOLUTE,
            'short' => true,
        ]);
    }

    public function isImage(): bool
    {
        return $this->type === 'image' || str_starts_with($this->file_type ?? '', 'image/');
    }

    public function isAudio(): bool
    {
        return $this->type === 'audio' || str_starts_with($this->file_type ?? '', 'audio/');
    }

    public function isFile(): bool
    {
        return $this->type === 'file';
    }

    public function getAttachmentUrl(): ?string
    {
        return $this->attachment_path ? asset('storage/'.$this->attachment_path) : null;
    }

    public function getFormattedFileSize(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes <= 0) {
            return '';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $i), 1).' '.$units[$i];
    }

    /**
     * @return array<int>
     */
    public function parseMentionedUserIds(): array
    {
        if (blank($this->body) || ! $this->conversation_id) {
            return [];
        }

        $body = $this->body;
        $conversation = $this->conversation ?? Conversation::find($this->conversation_id);
        if (! $conversation) {
            return [];
        }

        $conversation->loadMissing('users');
        $users = $conversation->users->where('id', '!=', $this->sender_id);

        // Check if @all is present
        if (preg_match('/(?<=^|\s)@all\b/i', $body)) {
            return $users->pluck('id')->all();
        }

        $mentionedIds = [];

        foreach ($users as $user) {
            // Match @username
            if ($user->username && preg_match('/(?<=^|\s)@'.preg_quote($user->username, '/').'\b/i', $body)) {
                $mentionedIds[] = $user->id;

                continue;
            }

            // Match @FirstName
            $firstName = explode(' ', trim($user->name))[0] ?? '';
            if (strlen($firstName) >= 2 && preg_match('/(?<=^|\s)@'.preg_quote($firstName, '/').'\b/i', $body)) {
                $mentionedIds[] = $user->id;

                continue;
            }

            // Match @FirstnameLastname without spaces
            $nameSlug = str_replace(' ', '', $user->name);
            if (preg_match('/(?<=^|\s)@'.preg_quote($nameSlug, '/').'\b/i', $body)) {
                $mentionedIds[] = $user->id;

                continue;
            }
        }

        return array_values(array_unique($mentionedIds));
    }

    public function mentionsUser(User|int $user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;

        return in_array($userId, $this->parseMentionedUserIds(), true);
    }

    public function getFormattedBodyHtml(bool $isMe = false): string
    {
        if ($this->is_deleted) {
            return 'This message was deleted';
        }

        $body = e($this->body);

        if ($isMe) {
            // When sent by me (amber-600 background bubble)
            $allBadge = '<span class="inline-flex items-center gap-1 rounded bg-white/25 px-1.5 py-0.5 font-black text-amber-100 ring-1 ring-white/30 shadow-sm">📢 @all</span>';
            $body = preg_replace('/(?<=^|\s)@all\b/i', $allBadge, $body);

            $body = preg_replace_callback('/(?<=^|\s)@([a-zA-Z0-9_\-]+)\b/', function ($matches) {
                $tag = $matches[1];
                if (strtolower($tag) === 'all') {
                    return $matches[0];
                }

                return '<span class="inline-flex items-center rounded bg-white/20 px-1.5 py-0.5 font-bold text-white ring-1 ring-white/25">@'.$tag.'</span>';
            }, $body);
        } else {
            // When received (white or dark slate background bubble)
            $allBadge = '<span class="inline-flex items-center gap-1 rounded bg-amber-500/20 px-1.5 py-0.5 font-extrabold text-amber-900 ring-1 ring-amber-500/30 dark:bg-amber-400/20 dark:text-amber-200 dark:ring-amber-400/30">📢 @all</span>';
            $body = preg_replace('/(?<=^|\s)@all\b/i', $allBadge, $body);

            $body = preg_replace_callback('/(?<=^|\s)@([a-zA-Z0-9_\-]+)\b/', function ($matches) {
                $tag = $matches[1];
                if (strtolower($tag) === 'all') {
                    return $matches[0];
                }

                return '<span class="inline-flex items-center rounded bg-amber-500/15 px-1.5 py-0.5 font-bold text-amber-800 ring-1 ring-amber-500/20 dark:bg-amber-400/20 dark:text-amber-300 dark:ring-amber-400/20">@'.$tag.'</span>';
            }, $body);
        }

        return nl2br($body);
    }

    public function getReplySnippet(int $limit = 60): string
    {
        if ($this->is_deleted) {
            return 'This message was deleted';
        }

        if ($this->isAudio()) {
            return '🎤 Voice message';
        }

        if ($this->isImage()) {
            return '📷 Photo'.($this->body ? ': '.Str::limit($this->body, $limit) : '');
        }

        if ($this->isFile()) {
            return '📎 '.($this->attachment_name ?: 'File');
        }

        return Str::limit($this->body ?? '', $limit);
    }
}
