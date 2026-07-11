<?php

declare(strict_types=1);

namespace WTD\WebSocket;

/**
 * Represents a logical WebSocket client connection.
 */
final class WebSocketConnection
{
    public function __construct(public readonly string $id)
    {
    }
}
