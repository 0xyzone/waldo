<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\WebPushGenericNotification;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Str;

class SendWebPushOnDatabaseNotification
{
    /**
     * Handle the event.
     */
    public function handle(NotificationSent $event): void
    {
        // Only trigger for database channel notifications
        if ($event->channel !== 'database') {
            return;
        }

        $user = $event->notifiable;
        if (! ($user instanceof User)) {
            return;
        }

        // Only send if the user has active browser push subscriptions
        if (! $user->pushSubscriptions()->exists()) {
            return;
        }

        $data = [];
        if (isset($event->response) && is_array($event->response->data ?? null)) {
            $data = $event->response->data;
        } elseif (method_exists($event->notification, 'toDatabase')) {
            $data = (array) $event->notification->toDatabase($user);
        } elseif (method_exists($event->notification, 'toArray')) {
            $data = (array) $event->notification->toArray($user);
        }

        $title = strip_tags((string) ($data['title'] ?? 'New Notification'));
        $body = strip_tags((string) ($data['body'] ?? ''));

        if (blank($title) && blank($body)) {
            return;
        }

        // Extract action URL if available from Filament actions
        $actionUrl = null;
        if (! empty($data['actions']) && is_array($data['actions'])) {
            foreach ($data['actions'] as $action) {
                if (! empty($action['url'])) {
                    $actionUrl = $action['url'];
                    break;
                }
            }
        }

        if (! $actionUrl) {
            $actionUrl = url('/kamkaj');
        }

        $tag = 'db-notif-'.($event->response->id ?? Str::random(8));

        try {
            $user->notify(new WebPushGenericNotification(
                title: $title ?: 'Kamkaj Notification',
                body: $body,
                actionUrl: $actionUrl,
                icon: asset('icons/icon-192x192.png'),
                tag: $tag,
                data: $data
            ));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
