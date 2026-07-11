<?php

declare(strict_types=1);

namespace WTD\Notification\Channels;

use RuntimeException;
use WTD\Notification\Channel;
use WTD\Notification\Messages\WebhookMessage;
use WTD\Notification\Notifiable;
use WTD\Notification\Notification;
use WTD\Notification\NotificationTransport;

final class WebhookChannel implements Channel
{
    public function __construct(private readonly NotificationTransport $transport)
    {
    }

    public function send(Notifiable $notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toWebhook')) {
            return;
        }

        $message = $notification->toWebhook($notifiable);

        if (!$message instanceof WebhookMessage) {
            throw new RuntimeException('Webhook notification must return a WebhookMessage.');
        }

        $this->transport->send('webhook', $notifiable->routeNotificationFor('webhook'), $message);
    }
}
