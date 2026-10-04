<?php

namespace App\Events;

use App\Models\ConversationParticipant;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $conversationId;

    public int $userId;

    public string $status; // 'delivered' or 'seen'

    public ?int $messageId = null;

    /**
     * @var array<int>
     */
    public array $participantUserIds = [];

    public function __construct(int $conversationId, int $userId, string $status, ?int $messageId = null)
    {
        $this->conversationId = $conversationId;
        $this->userId = $userId;
        $this->status = $status;
        $this->messageId = $messageId;

        $this->participantUserIds = ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', '!=', $userId)
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

        foreach ($this->participantUserIds as $participantId) {
            $channels[] = new PrivateChannel('chat.user.'.$participantId);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'message.status';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversationId,
            'user_id' => $this->userId,
            'status' => $this->status,
            'message_id' => $this->messageId,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
