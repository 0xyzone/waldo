<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'role',
        'last_read_at',
        'is_pinned',
        'pinned_until',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    protected function casts(): array
    {
        return [
            'last_read_at' => 'datetime',
            'is_pinned' => 'boolean',
            'pinned_until' => 'datetime',
        ];
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

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
