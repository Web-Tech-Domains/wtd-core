<?php

declare(strict_types=1);

namespace WTD\Notification;

use WTD\Notification\Channels\ChatChannel;
use WTD\Notification\Channels\DatabaseChannel;
use WTD\Notification\Channels\FirebaseChannel;
use WTD\Notification\Channels\MailChannel;
use WTD\Notification\Channels\TextChannel;
use WTD\Notification\Channels\WebhookChannel;
use WTD\Support\ServiceProvider;

final class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container()->singleton(NotificationTransport::class, fn (): NotificationTransport => new NotificationTransport());
        $this->container()->singleton(DatabaseChannel::class, fn (): DatabaseChannel => new DatabaseChannel());
        $this->container()->singleton(NotificationManager::class, function (): NotificationManager {
            $transport = $this->container()->get(NotificationTransport::class);
            $manager = new NotificationManager();
            $manager->extend('mail', new MailChannel($transport));
            $manager->extend('sms', new TextChannel('sms', 'toSms', $transport));
            $manager->extend('whatsapp', new TextChannel('whatsapp', 'toWhatsApp', $transport));
            $manager->extend('telegram', new ChatChannel('telegram', 'toTelegram', $transport));
            $manager->extend('slack', new ChatChannel('slack', 'toSlack', $transport));
            $manager->extend('firebase', new FirebaseChannel($transport));
            $manager->extend('database', $this->container()->get(DatabaseChannel::class));
            $manager->extend('webhook', new WebhookChannel($transport));

            return $manager;
        });
    }
}
