<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'body',
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
            'is_deleted' => 'boolean',
            'deleted_at' => 'datetime',
            'is_pinned' => 'boolean',
            'pinned_at' => 'datetime',
            'pinned_until' => 'datetime',
        ];
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

    public function canBeDeletedBy(int $userId): bool
    {
        if ($this->is_deleted) {
            return false;
        }

        if ($this->sender_id !== $userId) {
            return false;
        }

        // Only valid till 15 minutes from the time the chat was sent
        return $this->created_at !== null && $this->created_at->diffInMinutes(now()) <= 15;
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
}
