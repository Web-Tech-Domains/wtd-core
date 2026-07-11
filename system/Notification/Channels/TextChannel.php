<?php

declare(strict_types=1);

namespace WTD\Notification\Channels;

use RuntimeException;
use WTD\Notification\Channel;
use WTD\Notification\Messages\TextMessage;
use WTD\Notification\Notifiable;
use WTD\Notification\Notification;
use WTD\Notification\NotificationTransport;

final class TextChannel implements Channel
{
    public function __construct(
        private readonly string $channel,
        private readonly string $method,
        private readonly NotificationTransport $transport,
    ) {
    }

    public function send(Notifiable $notifiable, Notification $notification): void
    {
        if (!method_exists($notification, $this->method)) {
            return;
        }

        $message = $notification->{$this->method}($notifiable);

        if (!$message instanceof TextMessage) {
            throw new RuntimeException(sprintf('[%s] notification must return a TextMessage.', $this->channel));
        }

        $this->transport->send($this->channel, $notifiable->routeNotificationFor($this->channel), $message);
    }
}
