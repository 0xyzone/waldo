<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class WebPushGenericNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>|null  $data
     */
    public function __construct(
        public string $title,
        public string $body,
        public ?string $actionUrl = null,
        public ?string $icon = null,
        public ?string $tag = null,
        public ?array $data = null
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return [WebPushChannel::class];
    }

    /**
     * Build the WebPush message.
     */
    public function toWebPush(mixed $notifiable, mixed $notification): WebPushMessage
    {
        $url = $this->actionUrl ?: url('/kamkaj');

        $message = (new WebPushMessage)
            ->title($this->title)
            ->body($this->body)
            ->icon($this->icon ?: asset('pwa-icons/icon-192x192.png'))
            ->badge(asset('pwa-icons/favicon-32x32.png'))
            ->renotify()
            ->vibrate([100, 50, 100])
            ->data([
                'url' => $url,
                'extra' => $this->data,
            ]);

        if ($this->tag) {
            $message->tag($this->tag);
        }

        if ($this->actionUrl) {
            $message->action('Open', 'open_action');
        }

        return $message;
    }
}
