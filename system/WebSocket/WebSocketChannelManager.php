<?php

declare(strict_types=1);

namespace WTD\WebSocket;

/**
 * Tracks logical channel subscriptions for WebSocket connections.
 */
final class WebSocketChannelManager
{
    /**
     * @var array<string, array<string, WebSocketConnection>>
     */
    private array $channels = [];

    public function subscribe(string $channel, WebSocketConnection $connection): void
    {
        $this->channels[$channel][$connection->id] = $connection;
    }

    public function unsubscribe(string $channel, WebSocketConnection $connection): void
    {
        unset($this->channels[$channel][$connection->id]);
    }

    /**
     * @return list<WebSocketConnection>
     */
    public function subscribers(string $channel): array
    {
        return array_values($this->channels[$channel] ?? []);
    }
}
