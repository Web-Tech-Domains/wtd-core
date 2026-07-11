<?php

declare(strict_types=1);

namespace WTD\Notification\Channels;

use RuntimeException;
use WTD\Notification\Channel;
use WTD\Notification\Messages\FirebaseMessage;
use WTD\Notification\Notifiable;
use WTD\Notification\Notification;
use WTD\Notification\NotificationTransport;

final class FirebaseChannel implements Channel
{
    public function __construct(private readonly NotificationTransport $transport)
    {
    }

    public function send(Notifiable $notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toFirebase')) {
            return;
        }

        $message = $notification->toFirebase($notifiable);

        if (!$message instanceof FirebaseMessage) {
            throw new RuntimeException('Firebase notification must return a FirebaseMessage.');
        }

        $this->transport->send('firebase', $notifiable->routeNotificationFor('firebase'), $message);
    }
}
