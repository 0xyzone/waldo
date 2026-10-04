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

    public string $type;

    public ?string $attachmentPath;

    public ?string $attachmentName;

    public ?string $fileType;

    public ?int $fileSize;

    public ?string $senderAvatar;

    public string $createdAt;

    public ?int $replyToId = null;

    public ?array $replyTo = null;

    public bool $isForwarded = false;

    /**
     * @var array<int>
     */
    public array $mentionedUserIds = [];

    /**
     * @var array<int>
     */
    public array $recipientIds = [];

    public function __construct(Message $message, array $mentionedUserIds = [])
    {
        $message->loadMissing(['sender', 'replyTo.sender']);

        $this->id = $message->id;
        $this->mentionedUserIds = $mentionedUserIds;
        $this->conversationId = $message->conversation_id;
        $this->senderId = $message->sender_id;
        $this->senderName = $message->sender?->name ?? 'User';
        $this->senderUsername = $message->sender?->username;
        $this->senderAvatar = $message->sender?->getFilamentAvatarUrl();
        $this->body = $message->body;
        $this->type = $message->type ?? 'text';
        $this->attachmentPath = $message->attachment_path;
        $this->attachmentName = $message->attachment_name;
        $this->fileType = $message->file_type;
        $this->fileSize = $message->file_size;
        $this->createdAt = $message->created_at?->diffForHumans() ?? 'just now';
        $this->replyToId = $message->reply_to_id;
        $this->isForwarded = (bool) $message->is_forwarded;

        if ($message->replyTo) {
            $this->replyTo = [
                'id' => $message->replyTo->id,
                'sender_name' => $message->replyTo->sender?->name ?? 'User',
                'body' => $message->replyTo->getReplySnippet(60),
                'type' => $message->replyTo->type,
            ];
        }

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
            'sender_avatar' => $this->senderAvatar,
            'body' => $this->body,
            'type' => $this->type,
            'attachment_path' => $this->attachmentPath,
            'attachment_url' => $this->attachmentPath ? asset('storage/'.$this->attachmentPath) : null,
            'attachment_name' => $this->attachmentName,
            'file_type' => $this->fileType,
            'file_size' => $this->fileSize,
            'created_at' => $this->createdAt,
            'mentioned_user_ids' => $this->mentionedUserIds,
            'reply_to_id' => $this->replyToId,
            'reply_to' => $this->replyTo,
            'is_forwarded' => $this->isForwarded,
        ];
    }
}
