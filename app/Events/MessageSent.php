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

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $id;

    public int $conversationId;

    public int $senderId;

    public string $senderName;

    public ?string $senderUsername;

    public string $body;

    public ?string $attachmentPath;

    public ?string $attachmentName;

    public string $createdAt;

    /**
     * @var array<int>
     */
    protected array $recipientIds = [];

    public function __construct(Message $message)
    {
        $message->loadMissing('sender');

        $this->id = $message->id;
        $this->conversationId = $message->conversation_id;
        $this->senderId = $message->sender_id;
        $this->senderName = $message->sender?->name ?? 'User';
        $this->senderUsername = $message->sender?->username;
        $this->body = $message->body;
        $this->attachmentPath = $message->attachment_path;
        $this->attachmentName = $message->attachment_name;
        $this->createdAt = $message->created_at?->diffForHumans() ?? 'just now';

        $this->recipientIds = ConversationParticipant::where('conversation_id', $message->conversation_id)
            ->where('user_id', '!=', $message->sender_id)
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
        return 'message.sent';
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
            'sender_id' => $this->senderId,
            'sender_name' => $this->senderName,
            'sender_username' => $this->senderUsername,
            'body' => $this->body,
            'attachment_path' => $this->attachmentPath,
            'attachment_name' => $this->attachmentName,
            'created_at' => $this->createdAt,
        ];
    }
}
