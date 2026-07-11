<?php

declare(strict_types=1);

namespace WTD\Notification\Channels;

use RuntimeException;
use WTD\Notification\Channel;
use WTD\Notification\Messages\MailMessage;
use WTD\Notification\Notifiable;
use WTD\Notification\Notification;
use WTD\Notification\NotificationTransport;

final class MailChannel implements Channel
{
    public function __construct(private readonly NotificationTransport $transport)
    {
    }

    public function send(Notifiable $notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toMail')) {
            return;
        }

        $message = $notification->toMail($notifiable);

        if (!$message instanceof MailMessage) {
            throw new RuntimeException('Mail notification must return a MailMessage.');
        }

        $this->transport->send('mail', $notifiable->routeNotificationFor('mail'), $message);
    }
}
