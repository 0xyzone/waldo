<?php

namespace App\Events;

use App\Models\ConversationParticipant;
use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessagePinned implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $id;

    public int $conversationId;

    public bool $isPinned;

    public ?string $pinnedUntil;

    public ?int $pinnedBy;

    /**
     * @var array<int>
     */
    protected array $recipientIds = [];

    public function __construct(Message $message)
    {
        $this->id = $message->id;
        $this->conversationId = $message->conversation_id;
        $this->isPinned = (bool) $message->is_pinned;
        $this->pinnedUntil = $message->pinned_until?->toIso8601String();
        $this->pinnedBy = $message->pinned_by;

        $this->recipientIds = ConversationParticipant::where('conversation_id', $message->conversation_id)
            ->where('user_id', '!=', auth()->id())
            ->pluck('user_id')
            ->all();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('chat.conversation.'.$this->conversationId),
        ];

        foreach ($this->recipientIds as $recipientId) {
            $channels[] = new PrivateChannel('chat.user.'.$recipientId);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'message.pinned';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversationId,
            'is_pinned' => $this->isPinned,
            'pinned_until' => $this->pinnedUntil,
            'pinned_by' => $this->pinnedBy,
        ];
    }
}
