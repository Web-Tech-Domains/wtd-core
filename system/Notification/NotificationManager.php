<?php

declare(strict_types=1);

namespace WTD\Notification;

use InvalidArgumentException;

final class NotificationManager
{
    /**
     * @var array<string, Channel>
     */
    private array $channels = [];

    public function extend(string $name, Channel $channel): void
    {
        $this->channels[$name] = $channel;
    }

    public function channel(string $name): Channel
    {
        return $this->channels[$name] ?? throw new InvalidArgumentException(sprintf('Notification channel [%s] is not configured.', $name));
    }

    public function send(Notifiable $notifiable, Notification $notification): void
    {
        foreach ($notification->via($notifiable) as $channel) {
            $this->channel($channel)->send($notifiable, $notification);
        }
    }
}
