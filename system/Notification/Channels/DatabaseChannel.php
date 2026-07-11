<?php

declare(strict_types=1);

namespace WTD\Notification\Channels;

use RuntimeException;
use WTD\Notification\Channel;
use WTD\Notification\Messages\DatabaseMessage;
use WTD\Notification\Notifiable;
use WTD\Notification\Notification;

final class DatabaseChannel implements Channel
{
    /**
     * @var list<array{notifiable: mixed, data: array<string, mixed>}>
     */
    private array $notifications = [];

    public function send(Notifiable $notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toDatabase')) {
            return;
        }

        $message = $notification->toDatabase($notifiable);

        if (!$message instanceof DatabaseMessage) {
            throw new RuntimeException('Database notification must return a DatabaseMessage.');
        }

        $this->notifications[] = [
            'notifiable' => $notifiable->routeNotificationFor('database'),
            'data' => $message->data,
        ];
    }

    /**
     * @return list<array{notifiable: mixed, data: array<string, mixed>}>
     */
    public function notifications(): array
    {
        return $this->notifications;
    }
}
