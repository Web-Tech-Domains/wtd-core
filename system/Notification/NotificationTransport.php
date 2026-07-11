<?php

declare(strict_types=1);

namespace WTD\Notification;

final class NotificationTransport
{
    /**
     * @var list<array{channel: string, route: mixed, message: object}>
     */
    private array $sent = [];

    public function send(string $channel, mixed $route, object $message): void
    {
        $this->sent[] = [
            'channel' => $channel,
            'route' => $route,
            'message' => $message,
        ];
    }

    /**
     * @return list<array{channel: string, route: mixed, message: object}>
     */
    public function sent(): array
    {
        return $this->sent;
    }
}
