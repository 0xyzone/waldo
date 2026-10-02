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

class MessageReacted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $messageId;

    public int $conversationId;

    public int $userId;

    public string $reaction;

    /**
     * @var array<int>
     */
    protected array $recipientIds = [];

    public function __construct(Message $message, int $userId, string $reaction)
    {
        $this->messageId = $message->id;
        $this->conversationId = $message->conversation_id;
        $this->userId = $userId;
        $this->reaction = $reaction;

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
        return 'message.reacted';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->messageId,
            'conversation_id' => $this->conversationId,
            'user_id' => $this->userId,
            'reaction' => $this->reaction,
        ];
    }
}
