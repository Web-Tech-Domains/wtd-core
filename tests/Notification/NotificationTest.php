<?php

declare(strict_types=1);

namespace Tests\Notification;

use PHPUnit\Framework\TestCase;
use WTD\Application\Application;
use WTD\Config\Repository;
use WTD\Container\Container;
use WTD\Notification\Channels\DatabaseChannel;
use WTD\Notification\Messages\ChatMessage;
use WTD\Notification\Messages\DatabaseMessage;
use WTD\Notification\Messages\FirebaseMessage;
use WTD\Notification\Messages\MailMessage;
use WTD\Notification\Messages\TextMessage;
use WTD\Notification\Messages\WebhookMessage;
use WTD\Notification\Notifiable;
use WTD\Notification\Notification;
use WTD\Notification\NotificationManager;
use WTD\Notification\NotificationServiceProvider;
use WTD\Notification\NotificationTransport;

final class NotificationTest extends TestCase
{
    public function testManagerSendsNotificationsThroughAllChannels(): void
    {
        $transport = new NotificationTransport();
        $database = new DatabaseChannel();
        $manager = $this->manager($transport, $database);
        $notifiable = new TestNotifiable();

        $manager->send($notifiable, new AccountAlert());

        self::assertSame([
            'mail',
            'sms',
            'whatsapp',
            'telegram',
            'slack',
            'firebase',
            'webhook',
        ], array_map(static fn (array $sent): string => $sent['channel'], $transport->sent()));
        self::assertSame('user@example.test', $transport->sent()[0]['route']);
        self::assertInstanceOf(MailMessage::class, $transport->sent()[0]['message']);
        self::assertSame([[
            'notifiable' => 1,
            'data' => ['type' => 'account-alert'],
        ]], $database->notifications());
    }

    public function testNotificationServiceProviderRegistersManagerAndChannels(): void
    {
        $basePath = dirname(__DIR__);
        self::assertNotSame('', $basePath);
        /** @var non-empty-string $basePath */

        $app = new Application($basePath, new Container(), new Repository());
        $app->register(NotificationServiceProvider::class);

        self::assertInstanceOf(NotificationManager::class, $app->container()->get(NotificationManager::class));
        self::assertInstanceOf(NotificationTransport::class, $app->container()->get(NotificationTransport::class));
        self::assertInstanceOf(DatabaseChannel::class, $app->container()->get(DatabaseChannel::class));
    }

    private function manager(NotificationTransport $transport, DatabaseChannel $database): NotificationManager
    {
        $manager = new NotificationManager();
        $manager->extend('mail', new \WTD\Notification\Channels\MailChannel($transport));
        $manager->extend('sms', new \WTD\Notification\Channels\TextChannel('sms', 'toSms', $transport));
        $manager->extend('whatsapp', new \WTD\Notification\Channels\TextChannel('whatsapp', 'toWhatsApp', $transport));
        $manager->extend('telegram', new \WTD\Notification\Channels\ChatChannel('telegram', 'toTelegram', $transport));
        $manager->extend('slack', new \WTD\Notification\Channels\ChatChannel('slack', 'toSlack', $transport));
        $manager->extend('firebase', new \WTD\Notification\Channels\FirebaseChannel($transport));
        $manager->extend('database', $database);
        $manager->extend('webhook', new \WTD\Notification\Channels\WebhookChannel($transport));

        return $manager;
    }
}

final class TestNotifiable implements Notifiable
{
    public function routeNotificationFor(string $channel): mixed
    {
        return match ($channel) {
            'mail' => 'user@example.test',
            'sms', 'whatsapp' => '+15555550100',
            'telegram' => '@user',
            'slack' => '#alerts',
            'firebase' => 'device-token',
            'database' => 1,
            'webhook' => 'https://example.test/webhook',
            default => null,
        };
    }
}

final class AccountAlert implements Notification
{
    public function via(Notifiable $notifiable): array
    {
        return ['mail', 'sms', 'whatsapp', 'telegram', 'slack', 'firebase', 'database', 'webhook'];
    }

    public function toMail(Notifiable $notifiable): MailMessage
    {
        return new MailMessage('Account Alert', 'Check your account.');
    }

    public function toSms(Notifiable $notifiable): TextMessage
    {
        return new TextMessage('SMS alert');
    }

    public function toWhatsApp(Notifiable $notifiable): TextMessage
    {
        return new TextMessage('WhatsApp alert');
    }

    public function toTelegram(Notifiable $notifiable): ChatMessage
    {
        return new ChatMessage('Telegram alert');
    }

    public function toSlack(Notifiable $notifiable): ChatMessage
    {
        return new ChatMessage('Slack alert');
    }

    public function toFirebase(Notifiable $notifiable): FirebaseMessage
    {
        return new FirebaseMessage('Alert', 'Firebase alert', ['id' => 1]);
    }

    public function toDatabase(Notifiable $notifiable): DatabaseMessage
    {
        return new DatabaseMessage(['type' => 'account-alert']);
    }

    public function toWebhook(Notifiable $notifiable): WebhookMessage
    {
        return new WebhookMessage(['type' => 'account-alert']);
    }
}
