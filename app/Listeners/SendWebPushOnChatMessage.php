<?php

namespace App\Listeners;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\User;
use App\Notifications\WebPushGenericNotification;
use Illuminate\Support\Str;

class SendWebPushOnChatMessage
{
    /**
     * Handle the event.
     */
    public function handle(MessageSent $event): void
    {
        $recipientIds = $event->recipientIds;

        // Mentioned users already receive a Filament database notification with special body
        if (! empty($event->mentionedUserIds)) {
            $recipientIds = array_diff($recipientIds, $event->mentionedUserIds);
        }

        if (empty($recipientIds)) {
            return;
        }

        $conversation = Conversation::find($event->conversationId);
        $isGroup = $conversation && $conversation->isGroup();

        $title = $event->senderName;
        if ($isGroup && $conversation->title) {
            $title = "{$event->senderName} in {$conversation->title}";
        }

        $snippet = match ($event->type) {
            'image' => '📷 Photo'.(filled($event->body) && $event->body !== $event->attachmentName ? ': '.$event->body : ''),
            'audio' => '🎤 Voice message',
            'file' => '📎 File: '.($event->attachmentName ?? $event->body),
            default => Str::limit($event->body, 120),
        };

        $actionUrl = url('/kamkaj/chat?c='.$event->conversationId);
        $icon = $event->senderAvatar ?: asset('pwa-icons/icon-192x192.png');
        $tag = 'chat-conversation-'.$event->conversationId;

        $recipients = User::whereIn('id', $recipientIds)->get();

        foreach ($recipients as $recipient) {
            if (! $recipient->pushSubscriptions()->exists()) {
                continue;
            }

            try {
                $recipient->notify(new WebPushGenericNotification(
                    title: $title,
                    body: $snippet,
                    actionUrl: $actionUrl,
                    icon: $icon,
                    tag: $tag,
                    data: [
                        'conversation_id' => $event->conversationId,
                        'message_id' => $event->id,
                        'type' => 'chat',
                    ]
                ));
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
